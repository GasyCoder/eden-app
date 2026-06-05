<script setup>
defineProps({
    blocks: {
        type: Object,
        default: () => ({}),
    },
});
</script>

<template>
    <section id="contact">
        <div class="container">
            <div class="contact-info reveal">
                <span class="eyebrow">{{ blocks?.eyebrow ?? 'Contact' }}</span>
                <h2>{{ blocks?.title ?? 'Nous Contacter' }}</h2>
                <p>{{ blocks?.description ?? 'Pour toute question relative aux admissions, à la recherche ou aux partenariats, notre équipe est à votre disposition.' }}</p>
                <div class="c-items">
                    <div class="c-item">
                        <div class="c-icon">📍</div>
                        <div>
                            <h4>{{ blocks?.address_label ?? 'Adresse' }}</h4>
                            <p>
                                <template v-for="(line, index) in (blocks?.address ?? 'École Doctorale EDEN — Université de Mahajanga\nBP 652, Mahajanga 401, Madagascar').split('\n')" :key="line">
                                    <br v-if="index > 0">{{ line }}
                                </template>
                            </p>
                        </div>
                    </div>
                    <div class="c-item">
                        <div class="c-icon">📞</div>
                        <div>
                            <h4>{{ blocks?.phone_label ?? 'Téléphone' }}</h4>
                            <p>{{ blocks?.phone ?? '+261 20 62 XXX XX' }}</p>
                        </div>
                    </div>
                    <div class="c-item">
                        <div class="c-icon">✉️</div>
                        <div>
                            <h4>{{ blocks?.email_label ?? 'Email' }}</h4>
                            <p><a :href="`mailto:${blocks?.email ?? 'contact@eden.univ-mahajanga.mg'}`">{{ blocks?.email ?? 'contact@eden.univ-mahajanga.mg' }}</a></p>
                        </div>
                    </div>
                    <div class="c-item">
                        <div class="c-icon">🌐</div>
                        <div>
                            <h4>{{ blocks?.website_label ?? "Site Web de l'Université" }}</h4>
                            <p>{{ blocks?.website ?? 'www.univ-mahajanga.mg' }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="map-ph img-ph reveal">
                <div class="ic">{{ blocks?.map_icon ?? '🗺️' }}</div>
                <span>{{ blocks?.map_label ?? 'Carte de localisation' }}</span>
                <span style="font-size:0.72rem;margin-top:0.25rem;opacity:.6;">{{ blocks?.map_caption ?? 'Université de Mahajanga — Madagascar' }}</span>
            </div>
        </div>
    </section>
</template>
