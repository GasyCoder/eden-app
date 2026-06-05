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
                        <div class="c-icon"><EditableText :value="blocks?.address_icon" fallback="📍" content-key="contact.address_icon" :can-edit="canEdit" /></div>
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
                        <div class="c-icon"><EditableText :value="blocks?.phone_icon" fallback="📞" content-key="contact.phone_icon" :can-edit="canEdit" /></div>
                        <div>
                            <h4><EditableText :value="blocks?.phone_label" fallback="Téléphone" content-key="contact.phone_label" :can-edit="canEdit" /></h4>
                            <p><EditableText :value="blocks?.phone" fallback="+261 20 62 XXX XX" content-key="contact.phone" :can-edit="canEdit" /></p>
                        </div>
                    </div>
                    <div class="c-item">
                        <div class="c-icon"><EditableText :value="blocks?.email_icon" fallback="✉️" content-key="contact.email_icon" :can-edit="canEdit" /></div>
                        <div>
                            <h4><EditableText :value="blocks?.email_label" fallback="Email" content-key="contact.email_label" :can-edit="canEdit" /></h4>
                            <p><a :href="`mailto:${blocks?.email ?? 'contact@eden.univ-mahajanga.mg'}`"><EditableText :value="blocks?.email" fallback="contact@eden.univ-mahajanga.mg" content-key="contact.email" :can-edit="canEdit" /></a></p>
                        </div>
                    </div>
                    <div class="c-item">
                        <div class="c-icon"><EditableText :value="blocks?.website_icon" fallback="🌐" content-key="contact.website_icon" :can-edit="canEdit" /></div>
                        <div>
                            <h4><EditableText :value="blocks?.website_label" fallback="Site Web de l'Université" content-key="contact.website_label" :can-edit="canEdit" /></h4>
                            <p><EditableText :value="blocks?.website" fallback="www.univ-mahajanga.mg" content-key="contact.website" :can-edit="canEdit" /></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="map-ph img-ph reveal" :class="{ 'has-image': blocks?.map_image }">
                <img v-if="blocks?.map_image" :src="blocks.map_image" alt="Carte de localisation">
                <template v-else>
                    <div class="ic"><EditableText :value="blocks?.map_icon" fallback="🗺️" content-key="contact.map_icon" :can-edit="canEdit" /></div>
                    <span><EditableText :value="blocks?.map_label" fallback="Carte de localisation" content-key="contact.map_label" :can-edit="canEdit" /></span>
                    <span style="font-size:0.72rem;margin-top:0.25rem;opacity:.6;"><EditableText :value="blocks?.map_caption" fallback="Université de Mahajanga — Madagascar" content-key="contact.map_caption" :can-edit="canEdit" /></span>
                </template>
                <EditableImage content-key="contact.map_image" :can-edit="canEdit" />
            </div>
        </div>
    </section>
</template>
