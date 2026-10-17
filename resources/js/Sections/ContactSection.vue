<script setup>
import { computed } from 'vue';
import { Mail, Map, MapPin, Phone } from '@lucide/vue';
import EditableText from '@/Components/Editable/EditableText.vue';
import SectionHeading from '@/Components/Portal/SectionHeading.vue';
import { cn } from '@/lib/utils';
import { containerClass } from '@/lib/variants';
import EditableTextarea from '@/Components/Editable/EditableTextarea.vue';
import LinkEdit from '@/Components/Editable/LinkEdit.vue';

const props = defineProps({
    blocks: {
        type: Object,
        default: () => ({}),
    },
    canEdit: {
        type: Boolean,
        default: false,
    },
    imageVariants: {
        type: Object,
        default: () => ({}),
    },
});

const trustedMapUrl = (value) => {
    if (!value) {
        return '';
    }

    try {
        const url = new URL(value);
        const host = url.hostname.toLowerCase();
        const path = url.pathname.toLowerCase();

        if (url.protocol !== 'https:') {
            return '';
        }

        if ((host === 'www.google.com' || host === 'maps.google.com') && path.startsWith('/maps')) {
            if (!url.searchParams.has('output') && !path.startsWith('/maps/embed')) {
                url.searchParams.set('output', 'embed');
            }

            return url.toString();
        }

        if (host === 'www.openstreetmap.org' && path === '/export/embed.html') {
            return url.toString();
        }
    } catch {
        return '';
    }

    return '';
};

const mapEmbedUrl = computed(() => trustedMapUrl(props.blocks?.map_embed_url));
const hasMapMedia = computed(() => Boolean(mapEmbedUrl.value || props.blocks?.map_image));
const phoneHref = computed(() => {
    const digits = String(props.blocks?.phone ?? '').replace(/[^\d+]/g, '');

    // Pas de lien tant que le numéro n'est pas complet (ex. « +261 20 62 XXX XX »)
    return digits.replace(/\D/g, '').length >= 8 && !/x/i.test(props.blocks?.phone ?? '') ? `tel:${digits}` : null;
});
const email = computed(() => props.blocks?.email || 'contact@eden.univ-mahajanga.mg');
</script>

<template>
    <section id="contact" class="bg-[var(--bg-contact,var(--background))] py-16 lg:py-20">
        <div :class="cn(containerClass, 'grid items-center gap-12 lg:grid-cols-2 lg:gap-16')">
            <div>
                <SectionHeading>
                    <EditableText :value="blocks?.title" fallback="Nous Contacter" content-key="contact.title" :can-edit="canEdit" />
                    <template #description>
                        <EditableTextarea
                            :value="blocks?.description"
                            fallback="Pour toute question relative aux admissions, à la recherche ou aux partenariats, notre équipe est à votre disposition."
                            content-key="contact.description"
                            :can-edit="canEdit"
                        />
                    </template>
                </SectionHeading>
                <ul class="mt-8 grid gap-5">
                    <li class="flex items-start gap-3">
                        <span class="grid size-8 shrink-0 place-items-center rounded-md bg-eden-50 text-eden-700 dark:bg-eden-950 dark:text-eden-300"><MapPin class="size-4" aria-hidden="true" /></span>
                        <div class="min-w-0">
                            <h3 class="text-xs text-muted-foreground"><EditableText :value="blocks?.address_label" fallback="Adresse" content-key="contact.address_label" :can-edit="canEdit" /></h3>
                            <p class="mt-0.5 text-sm leading-relaxed font-medium whitespace-pre-line">
                                <EditableTextarea :value="blocks?.address" fallback="École Doctorale EDEN, Université de Mahajanga&#10;BP 652, Mahajanga 401, Madagascar" content-key="contact.address" :can-edit="canEdit" />
                            </p>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="grid size-8 shrink-0 place-items-center rounded-md bg-gold-50 text-gold-800 dark:bg-gold-950 dark:text-gold-300"><Phone class="size-4" aria-hidden="true" /></span>
                        <div class="min-w-0">
                            <h3 class="text-xs text-muted-foreground"><EditableText :value="blocks?.phone_label" fallback="Téléphone" content-key="contact.phone_label" :can-edit="canEdit" /></h3>
                            <a v-if="phoneHref && !canEdit" :href="phoneHref" class="mt-0.5 block text-sm font-medium hover:text-eden-700 dark:hover:text-eden-300">{{ blocks?.phone }}</a>
                            <p v-else class="mt-0.5 text-sm font-medium"><EditableText :value="blocks?.phone" fallback="+261 20 62 XXX XX" content-key="contact.phone" :can-edit="canEdit" /></p>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="grid size-8 shrink-0 place-items-center rounded-md bg-laterite-50 text-laterite-700 dark:bg-laterite-950 dark:text-laterite-300"><Mail class="size-4" aria-hidden="true" /></span>
                        <div class="min-w-0">
                            <h3 class="text-xs text-muted-foreground"><EditableText :value="blocks?.email_label" fallback="Email" content-key="contact.email_label" :can-edit="canEdit" /></h3>
                            <a :href="`mailto:${email}`" class="mt-0.5 block text-sm font-medium break-all text-eden-700 hover:underline hover:underline-offset-4 dark:text-eden-300"><EditableText :value="blocks?.email" fallback="contact@eden.univ-mahajanga.mg" content-key="contact.email" :can-edit="canEdit" /></a>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="relative aspect-[4/3] overflow-hidden rounded-xl bg-secondary lg:aspect-[5/4]">
                <iframe v-if="mapEmbedUrl" :src="mapEmbedUrl" title="Carte de localisation de l’Université de Mahajanga" class="block size-full border-0" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                <picture v-else-if="blocks?.map_image">
                    <source v-for="source in imageVariants?.sources ?? []" :key="source.type" :type="source.type" :srcset="source.srcset" :sizes="imageVariants?.sizes">
                    <img :src="blocks.map_image" alt="Carte de localisation de l’Université de Mahajanga" class="size-full object-cover" :width="imageVariants?.width ?? 680" :height="imageVariants?.height ?? 440" loading="lazy" decoding="async">
                </picture>
                <div v-else class="flex size-full flex-col items-center justify-center gap-2 text-sm text-muted-foreground">
                    <Map class="size-8 opacity-60" aria-hidden="true" />
                    <span><EditableText :value="blocks?.map_label" fallback="Carte de localisation" content-key="contact.map_label" :can-edit="canEdit" /></span>
                    <small><EditableText :value="blocks?.map_caption" fallback="Université de Mahajanga, Madagascar" content-key="contact.map_caption" :can-edit="canEdit" /></small>
                </div>
                <span v-if="canEdit" class="absolute bottom-3 left-3 z-10" title="Lien Google Maps ou OpenStreetMap (intégration)">
                    <LinkEdit :value="blocks?.map_embed_url" fallback="Ajouter l’URL de la carte" content-key="contact.map_embed_url" :can-edit="canEdit" />
                </span>
            </div>
        </div>
    </section>
</template>
