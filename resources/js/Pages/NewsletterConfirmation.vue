<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    email: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <Head title="Inscription newsletter" />

    <main class="newsletter-confirm-page">
        <section class="newsletter-confirm-card card">
            <span class="eyebrow">Newsletter</span>
            <h1>Inscription confirmée</h1>
            <p>
                Votre adresse <strong>{{ email }}</strong> est enregistrée pour recevoir les informations de l'École Doctorale EDEN.
            </p>
            <div class="newsletter-confirm-actions">
                <Link href="/" class="btn btn-primary">Retour au site</Link>
            </div>
        </section>
    </main>
</template>
