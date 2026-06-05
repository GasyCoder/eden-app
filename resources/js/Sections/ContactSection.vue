<script setup>
import EditableImage from '@/Components/Editable/EditableImage.vue';
import EditableText from '@/Components/Editable/EditableText.vue';
import EditableTextarea from '@/Components/Editable/EditableTextarea.vue';

defineProps({
    blocks: {
        type: Object,
        default: () => ({}),
    },
    canEdit: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <section id="contact">
        <div class="container">
            <div class="contact-info reveal">
                <span class="eyebrow"><EditableText :value="blocks?.eyebrow" fallback="Contact" content-key="contact.eyebrow" :can-edit="canEdit" /></span>
                <h2><EditableText :value="blocks?.title" fallback="Nous Contacter" content-key="contact.title" :can-edit="canEdit" /></h2>
                <p>
                    <EditableTextarea
                        :value="blocks?.description"
                        fallback="Pour toute question relative aux admissions, à la recherche ou aux partenariats, notre équipe est à votre disposition."
                        content-key="contact.description"
                        :can-edit="canEdit"
                    />
                </p>
                <div class="c-items">
                    <div class="c-item">
                        <div class="c-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                        </div>
                        <div>
                            <h4><EditableText :value="blocks?.address_label" fallback="Adresse" content-key="contact.address_label" :can-edit="canEdit" /></h4>
                            <p>
                                <EditableTextarea
                                    :value="blocks?.address"
                                    fallback="École Doctorale EDEN — Université de Mahajanga&#10;BP 652, Mahajanga 401, Madagascar"
                                    content-key="contact.address"
                                    :can-edit="canEdit"
                                />
                            </p>
                        </div>
                    </div>
                    <div class="c-item">
                        <div class="c-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.6a2 2 0 0 1-.5 2.1L8 9.6a16 16 0 0 0 6 6l1.2-1.2a2 2 0 0 1 2.1-.5c.8.3 1.7.5 2.6.6a2 2 0 0 1 1.7 2Z"/></svg>
                        </div>
                        <div>
                            <h4><EditableText :value="blocks?.phone_label" fallback="Téléphone" content-key="contact.phone_label" :can-edit="canEdit" /></h4>
                            <p><EditableText :value="blocks?.phone" fallback="+261 20 62 XXX XX" content-key="contact.phone" :can-edit="canEdit" /></p>
                        </div>
                    </div>
                    <div class="c-item">
                        <div class="c-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                        </div>
                        <div>
                            <h4><EditableText :value="blocks?.email_label" fallback="Email" content-key="contact.email_label" :can-edit="canEdit" /></h4>
                            <p><a :href="`mailto:${blocks?.email ?? 'contact@eden.univ-mahajanga.mg'}`"><EditableText :value="blocks?.email" fallback="contact@eden.univ-mahajanga.mg" content-key="contact.email" :can-edit="canEdit" /></a></p>
                        </div>
                    </div>
                    <div class="c-item">
                        <div class="c-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M3 12h18"/><path d="M12 3a14 14 0 0 1 0 18 14 14 0 0 1 0-18Z"/></svg>
                        </div>
                        <div>
                            <h4><EditableText :value="blocks?.website_label" fallback="Site Web de l'Université" content-key="contact.website_label" :can-edit="canEdit" /></h4>
                            <p><EditableText :value="blocks?.website" fallback="www.univ-mahajanga.mg" content-key="contact.website" :can-edit="canEdit" /></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="map-ph img-ph reveal" :class="{ 'has-image': blocks?.map_image }">
                <img v-if="blocks?.map_image" :src="blocks.map_image" alt="Carte de localisation" width="680" height="440" loading="lazy" decoding="async">
                <template v-else>
                    <div class="ic" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="m9 4 6 2 5-2v14l-5 2-6-2-5 2V6l5-2Z"/><path d="M9 4v14M15 6v14"/></svg>
                    </div>
                    <span><EditableText :value="blocks?.map_label" fallback="Carte de localisation" content-key="contact.map_label" :can-edit="canEdit" /></span>
                    <span style="font-size:0.72rem;margin-top:0.25rem;opacity:.6;"><EditableText :value="blocks?.map_caption" fallback="Université de Mahajanga — Madagascar" content-key="contact.map_caption" :can-edit="canEdit" /></span>
                </template>
                <EditableImage content-key="contact.map_image" :can-edit="canEdit" />
            </div>
        </div>
    </section>
</template>
