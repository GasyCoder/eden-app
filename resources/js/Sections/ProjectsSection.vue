<script setup>
import { router } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import AdminEditButton from '@/Components/Editable/AdminEditButton.vue';
import EditableCollection from '@/Components/Editable/EditableCollection.vue';
import EditableText from '@/Components/Editable/EditableText.vue';
import EditableTextarea from '@/Components/Editable/EditableTextarea.vue';
import { adminDelete, adminPatch, withContentLocale } from '@/Composables/useAdminRequest';

const props = defineProps({
    blocks: {
        type: Object,
        default: () => ({}),
    },
    items: {
        type: Array,
        default: () => [],
    },
    locale: {
        type: String,
        default: 'fr',
    },
    canEdit: {
        type: Boolean,
        default: false,
    },
});

const fallbackProjects = [
    {
        title: 'BIODIVERS-BOENY',
        subtitle: 'En cours',
        description: "Conservation et valorisation des espèces endémiques de la Région Boeny : inventaires, suivis fauniques et réseau d'aires protégées communautaires.",
        meta: { icon: '🌿', image_label: 'Terrain — forêt endémique Boeny', ead: 'EAD 1', funding: 'Financement : AFD', project_leader: 'Chef de projet : À préciser', period: '2023 – 2026', badge_class: 'badge-active' },
    },
    {
        title: 'CORAL-MOZAMBIQUE',
        subtitle: 'En cours',
        description: "Monitoring continu et restauration des récifs coralliens du Canal de Mozambique face au blanchissement thermique et à l'acidification des océans.",
        meta: { icon: '🐠', image_label: 'Terrain — récif corallien', ead: 'EAD 2', funding: 'Financement : Union Européenne', project_leader: 'Chef de projet : À préciser', period: '2022 – 2025', badge_class: 'badge-active' },
    },
    {
        title: 'CLIMATE-COAST',
        subtitle: 'Démarrage',
        description: "Évaluation et stratégies d'adaptation des écosystèmes côtiers et terrestres de la région Boeny face aux impacts du changement climatique.",
        meta: { icon: '🌊', image_label: 'Terrain — mangroves et zone côtière', ead: 'EAD 1 & 2', funding: 'Financement : GEF', project_leader: 'Chef de projet : À préciser', period: '2025 – 2028', badge_class: 'badge-new' },
    },
];

const projects = computed(() => (props.items?.length ? props.items : fallbackProjects));
const projectLabels = computed(() => {
    const isEnglish = props.locale === 'en';

    return {
        funding: isEnglish ? 'Funding' : 'Financement',
        leader: isEnglish ? 'Project leader' : 'Chef de projet',
        period: isEnglish ? 'Period' : 'Période',
        empty: isEnglish ? 'To be specified' : 'À préciser',
    };
});
const projectViewLabels = computed(() => {
    const isEnglish = props.locale === 'en';

    return {
        display: isEnglish ? 'Display' : 'Affichage',
        showing: isEnglish ? 'projects shown' : 'projets affichés',
        grid: isEnglish ? 'Grid' : 'Grille',
        list: isEnglish ? 'List' : 'Liste',
    };
});
const editingId = ref(null);
const creating = ref(false);
const projectView = ref('grid');
const projectLayoutClass = computed(() => (projectView.value === 'list' ? 'projects-list' : 'projects-grid'));
const form = ref(blankProject());
const uploadError = ref('');
const uploadingProjectImage = ref(false);

onMounted(() => {
    try {
        const savedView = window.localStorage.getItem('eden-project-view');

        if (['grid', 'list'].includes(savedView)) {
            projectView.value = savedView;
        }
    } catch {
        projectView.value = 'grid';
    }
});

function blankProject() {
    return {
        title: '',
        subtitle: 'En cours',
        description: '',
        icon: '🌿',
        image_label: 'Terrain — recherche',
        ead: 'EAD 1',
        funding: 'Financement : ',
        project_leader: 'Chef de projet : ',
        period: '',
        image: '',
        badge_class: 'badge-active',
        link_url: '',
        link_label: '',
    };
}

function hasLink(project) {
    return project.link_url && project.link_url !== '#';
}

function setProjectView(view) {
    projectView.value = view;

    try {
        window.localStorage.setItem('eden-project-view', view);
    } catch {
        // Local storage can be unavailable in strict browser modes.
    }
}

function stripDetailLabels(value, labels, fallback = projectLabels.value.empty) {
    let text = String(value ?? '').trim();

    labels.forEach((label) => {
        const escapedLabel = label.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        text = text.replace(new RegExp(`^${escapedLabel}\\s*:?\\s*`, 'i'), '').trim();
    });

    return text || fallback;
}

function projectFunding(project) {
    return stripDetailLabels(project.meta?.funding, ['Financement', 'Funding']);
}

function projectLeader(project) {
    return stripDetailLabels(project.meta?.project_leader, ['Chef de projet', 'Project leader']);
}

function projectPeriod(project) {
    return String(project.meta?.period ?? '').trim() || projectLabels.value.empty;
}

function projectDescription(project) {
    return project.description ?? "Conservation et valorisation des espèces endémiques de la Région Boeny : inventaires, suivis fauniques et réseau d'aires protégées communautaires.";
}

function editProject(project) {
    uploadError.value = '';
    uploadingProjectImage.value = false;
    form.value = {
        title: project.title ?? '',
        subtitle: project.subtitle ?? '',
        description: project.description ?? '',
        icon: project.meta?.icon ?? '🌿',
        image_label: project.meta?.image_label ?? '',
        ead: project.meta?.ead ?? '',
        funding: project.meta?.funding ?? '',
        project_leader: project.meta?.project_leader ?? '',
        period: project.meta?.period ?? '',
        image: project.image ?? '',
        badge_class: project.meta?.badge_class ?? 'badge-active',
        link_url: project.link_url ?? '',
        link_label: project.link_label ?? '',
    };
    creating.value = false;
    editingId.value = project.id;
}

function startCreate() {
    uploadError.value = '';
    uploadingProjectImage.value = false;
    form.value = blankProject();
    editingId.value = null;
    creating.value = true;
}

function cancelEdit() {
    uploadError.value = '';
    uploadingProjectImage.value = false;
    editingId.value = null;
    creating.value = false;
    form.value = blankProject();
}

function payload() {
    return {
        title: form.value.title,
        subtitle: form.value.subtitle,
        description: form.value.description,
        image: form.value.image,
        link_url: form.value.link_url,
        link_label: form.value.link_label,
        meta: {
            icon: form.value.icon,
            image_label: form.value.image_label,
            ead: form.value.ead,
            funding: form.value.funding,
            project_leader: form.value.project_leader,
            period: form.value.period,
            badge_class: form.value.badge_class,
        },
    };
}

function projectDisplayImage(project) {
    return editingId.value === project.id ? form.value.image : project.image;
}

function projectDisplaySources(project) {
    return editingId.value === project.id ? [] : project.image_sources?.sources ?? [];
}

function projectDisplayImageLabel(project) {
    return editingId.value === project.id
        ? form.value.image_label
        : project.meta?.image_label ?? 'Terrain — forêt endémique Boeny';
}

function saveProject(project) {
    adminPatch(`/admin/content-items/${project.id}`, payload(), {
        preserveScroll: true,
        onSuccess: cancelEdit,
    });
}

function createProject() {
    router.post('/admin/content-items/projects', withContentLocale(payload()), {
        preserveScroll: true,
        onSuccess: cancelEdit,
    });
}

function deleteProject(project) {
    if (!window.confirm('Supprimer ce projet ?')) {
        return;
    }

    adminDelete(`/admin/content-items/${project.id}`, {}, {
        preserveScroll: true,
    });
}

function reorderProject(index, direction) {
    const editableProjects = projects.value.filter((project) => project.id);
    const targetIndex = index + direction;

    if (targetIndex < 0 || targetIndex >= editableProjects.length) {
        return;
    }

    const reordered = [...editableProjects];
    const [project] = reordered.splice(index, 1);
    reordered.splice(targetIndex, 0, project);

    adminPatch('/admin/content-items/projects/reorder', {
        items: reordered.map((item, position) => ({ id: item.id, position })),
    }, {
        preserveScroll: true,
    });
}

async function uploadProjectImage(event) {
    const file = event.target.files?.[0];

    if (!file) {
        return;
    }

    uploadError.value = '';
    uploadingProjectImage.value = true;

    try {
        const data = new FormData();
        data.append('image', file);

        const response = await fetch('/admin/uploads/images', {
            method: 'POST',
            body: data,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                Accept: 'application/json',
            },
        });

        const payload = await response.json();

        if (!response.ok || !payload.path) {
            uploadError.value = payload.message ?? 'Image refusée.';
            return;
        }

        form.value.image = payload.path;
    } catch {
        uploadError.value = 'Téléversement impossible.';
    } finally {
        uploadingProjectImage.value = false;
        event.target.value = '';
    }
}
</script>

<template>
    <section id="projets">
        <div class="container">
            <div class="section-header reveal">
                <span class="eyebrow">
                    <EditableText :value="blocks?.eyebrow" fallback="Projets" content-key="projets.eyebrow" :can-edit="canEdit" />
                </span>
                <h2>
                    <EditableText :value="blocks?.title" fallback="Projets de Recherche en Cours" content-key="projets.title" :can-edit="canEdit" />
                </h2>
                <p>
                    <EditableTextarea
                        :value="blocks?.description"
                        fallback="L'EDEN pilote et co-pilote plusieurs projets d'envergure financés par des institutions nationales et internationales."
                        content-key="projets.description"
                        :can-edit="canEdit"
                    />
                </p>
            </div>
            <div class="projects-display-bar reveal">
                <div class="projects-display-count">
                    <strong>{{ projects.length }}</strong>
                    <span>{{ projectViewLabels.showing }}</span>
                </div>
                <div class="projects-view-toggle" role="group" :aria-label="projectViewLabels.display">
                    <button
                        type="button"
                        :class="{ active: projectView === 'grid' }"
                        :aria-pressed="projectView === 'grid'"
                        :title="projectViewLabels.grid"
                        @click="setProjectView('grid')"
                    >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="4" width="7" height="7" rx="1.4" /><rect x="13" y="4" width="7" height="7" rx="1.4" /><rect x="4" y="13" width="7" height="7" rx="1.4" /><rect x="13" y="13" width="7" height="7" rx="1.4" /></svg>
                        <span>{{ projectViewLabels.grid }}</span>
                    </button>
                    <button
                        type="button"
                        :class="{ active: projectView === 'list' }"
                        :aria-pressed="projectView === 'list'"
                        :title="projectViewLabels.list"
                        @click="setProjectView('list')"
                    >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 6h12" /><path d="M8 12h12" /><path d="M8 18h12" /><rect x="4" y="5" width="1.8" height="1.8" rx=".4" /><rect x="4" y="11" width="1.8" height="1.8" rx=".4" /><rect x="4" y="17" width="1.8" height="1.8" rx=".4" /></svg>
                        <span>{{ projectViewLabels.list }}</span>
                    </button>
                </div>
            </div>
            <div v-if="canEdit" class="eden-collection-toolbar">
                <button type="button" @click="startCreate">Ajouter un projet</button>
            </div>
            <EditableCollection :can-edit="canEdit">
                <div class="projects-layout" :class="projectLayoutClass">
                <div v-if="creating" class="proj-card proj-card-editing reveal visible">
                    <div class="proj-img img-ph" :class="{ 'has-image': form.image, 'is-uploading': uploadingProjectImage }">
                        <img v-if="form.image" :src="form.image" alt="" width="380" height="190" loading="lazy" decoding="async">
                        <template v-else>
                            <div class="ic">{{ form.icon }}</div>
                            <span>{{ form.image_label }}</span>
                        </template>
                        <span v-if="uploadingProjectImage" class="eden-upload-skeleton" role="status" aria-live="polite">
                            <span class="eden-upload-content">
                                <span class="eden-upload-spinner"></span>
                                <span>Téléversement...</span>
                            </span>
                        </span>
                    </div>
                    <div class="proj-body">
                        <div class="eden-project-edit">
                            <input v-model="form.title" class="eden-collection-input" type="text" placeholder="Titre du projet">
                            <input v-model="form.subtitle" class="eden-collection-input" type="text" placeholder="Statut">
                            <input v-model="form.ead" class="eden-collection-input" type="text" placeholder="EAD">
                            <textarea v-model="form.description" class="eden-collection-textarea" placeholder="Description"></textarea>
                            <input v-model="form.icon" class="eden-collection-input" type="text" placeholder="Icône">
                            <input v-model="form.image_label" class="eden-collection-input" type="text" placeholder="Libellé image">
                            <input v-model="form.image" class="eden-collection-input" type="text" placeholder="URL image">
                            <input type="file" accept="image/*" :disabled="uploadingProjectImage" @change="uploadProjectImage">
                            <span v-if="uploadError" class="eden-edit-error">{{ uploadError }}</span>
                            <input v-model="form.funding" class="eden-collection-input" type="text" placeholder="Financement (ex. The MBZ Species Conservation Fund)">
                            <input v-model="form.project_leader" class="eden-collection-input" type="text" placeholder="Chef de projet (ex. RAKOTO)">
                            <input v-model="form.period" class="eden-collection-input" type="text" placeholder="Période">
                            <select v-model="form.badge_class" class="eden-collection-input">
                                <option value="badge-active">En cours</option>
                                <option value="badge-new">Démarrage</option>
                            </select>
                            <input v-model="form.link_url" class="eden-collection-input" type="text" placeholder="Lien externe du projet (https://…)">
                            <input v-model="form.link_label" class="eden-collection-input" type="text" placeholder="Texte du bouton (ex. En savoir plus)">
                            <div class="eden-collection-actions">
                                <button type="button" :disabled="uploadingProjectImage" @click="createProject">Enregistrer</button>
                                <button type="button" @click="cancelEdit">Annuler</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    v-for="(project, index) in projects"
                    :key="project.id ?? project.title"
                    class="proj-card reveal visible"
                    :class="{ 'proj-card-editing': editingId === project.id }"
                >
                    <div class="proj-img img-ph" :class="{ 'has-image': projectDisplayImage(project), 'is-uploading': editingId === project.id && uploadingProjectImage }">
                        <picture v-if="projectDisplayImage(project)">
                            <source
                                v-for="source in projectDisplaySources(project)"
                                :key="source.type"
                                :type="source.type"
                                :srcset="source.srcset"
                                :sizes="project.image_sources?.sizes"
                            >
                            <img
                                :src="projectDisplayImage(project)"
                                :alt="project.title ?? 'Projet EDEN-UMG'"
                                width="380"
                                height="190"
                                loading="lazy"
                                decoding="async"
                            >
                        </picture>
                        <template v-else>
                            <div class="ic">{{ project.meta?.icon ?? '🌿' }}</div>
                            <span>{{ projectDisplayImageLabel(project) }}</span>
                        </template>
                        <span v-if="editingId === project.id && uploadingProjectImage" class="eden-upload-skeleton" role="status" aria-live="polite">
                            <span class="eden-upload-content">
                                <span class="eden-upload-spinner"></span>
                                <span>Téléversement...</span>
                            </span>
                        </span>
                    </div>
                    <div class="proj-body">
                        <template v-if="editingId === project.id">
                            <div class="eden-project-edit">
                                <input v-model="form.title" class="eden-collection-input" type="text">
                                <input v-model="form.subtitle" class="eden-collection-input" type="text">
                                <input v-model="form.ead" class="eden-collection-input" type="text">
                                <textarea v-model="form.description" class="eden-collection-textarea"></textarea>
                                <input v-model="form.icon" class="eden-collection-input" type="text">
                                <input v-model="form.image_label" class="eden-collection-input" type="text">
                                <input v-model="form.image" class="eden-collection-input" type="text" placeholder="URL image">
                                <input type="file" accept="image/*" :disabled="uploadingProjectImage" @change="uploadProjectImage">
                                <span v-if="uploadError" class="eden-edit-error">{{ uploadError }}</span>
                                <input v-model="form.funding" class="eden-collection-input" type="text" placeholder="Financement (ex. The MBZ Species Conservation Fund)">
                                <input v-model="form.project_leader" class="eden-collection-input" type="text" placeholder="Chef de projet (ex. RAKOTO)">
                                <input v-model="form.period" class="eden-collection-input" type="text">
                                <select v-model="form.badge_class" class="eden-collection-input">
                                    <option value="badge-active">En cours</option>
                                    <option value="badge-new">Démarrage</option>
                                </select>
                                <input v-model="form.link_url" class="eden-collection-input" type="text" placeholder="Lien externe du projet (https://…)">
                                <input v-model="form.link_label" class="eden-collection-input" type="text" placeholder="Texte du bouton (ex. En savoir plus)">
                                <div class="eden-collection-actions">
                                    <button type="button" :disabled="uploadingProjectImage" @click="saveProject(project)">Enregistrer</button>
                                    <button type="button" @click="cancelEdit">Annuler</button>
                                    <button type="button" @click="deleteProject(project)">Supprimer</button>
                                </div>
                            </div>
                        </template>
                        <template v-else>
                            <div class="proj-meta">
                                <span class="badge" :class="project.meta?.badge_class ?? 'badge-active'">{{ project.subtitle ?? 'En cours' }}</span>
                                <span class="proj-ead">{{ project.meta?.ead ?? 'EAD 1' }}</span>
                                <AdminEditButton v-if="canEdit && project.id" @click="editProject(project)" />
                            </div>
                            <h4>{{ project.title ?? 'BIODIVERS-BOENY' }}</h4>
                            <p
                                class="proj-description"
                                :title="projectDescription(project)"
                            >
                                {{ projectDescription(project) }}
                            </p>
                            <div class="proj-details">
                                <span>
                                    <strong>{{ projectLabels.funding }}</strong>
                                    <em>{{ projectFunding(project) }}</em>
                                </span>
                                <span>
                                    <strong>{{ projectLabels.leader }}</strong>
                                    <em>{{ projectLeader(project) }}</em>
                                </span>
                                <span>
                                    <strong>{{ projectLabels.period }}</strong>
                                    <em>{{ projectPeriod(project) }}</em>
                                </span>
                            </div>
                            <a
                                v-if="hasLink(project)"
                                class="btn btn-secondary proj-cta"
                                :href="project.link_url"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                {{ project.link_label || 'En savoir plus' }}
                                <svg class="proj-cta-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17 17 7" /><path d="M9 7h8v8" /></svg>
                            </a>
                            <div v-if="canEdit && project.id" class="eden-collection-actions">
                                <button type="button" @click="reorderProject(index, -1)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><title>Monter</title><path d="m6 15 6-6 6 6"/></svg></button>
                                <button type="button" @click="reorderProject(index, 1)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><title>Descendre</title><path d="m6 9 6 6 6-6"/></svg></button>
                            </div>
                        </template>
                    </div>
                </div>
                </div>
            </EditableCollection>
            <div class="projects-ravenala-link">
                <a
                    href="/projets/ravenala"
                    class="btn btn-primary"
                >
                    Voir le projet RAVENALA
                </a>
            </div>
        </div>
    </section>
</template>
