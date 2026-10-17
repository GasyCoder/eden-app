<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { CircleCheck, Clock3, MailCheck, MailX } from '@lucide/vue';
import { computed } from 'vue';
import { cn } from '@/lib/utils';
import { buttonVariants } from '@/lib/variants';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    status: { type: String, required: true },
    mailError: { type: Boolean, default: false },
    resent: { type: Boolean, default: false },
});

const form = useForm({});
const states = {
    pending: { title: 'Confirmez votre e-mail', icon: MailCheck, text: 'Un lien de confirmation a été envoyé à cette adresse. Il est valable 24 heures. Vérifiez aussi vos courriers indésirables.' },
    pending_approval: { title: 'E-mail confirmé', icon: Clock3, text: 'Votre adresse est vérifiée. Votre demande attend maintenant la validation d’un administrateur. Vous recevrez les actualités uniquement après cette validation.' },
    subscribed: { title: 'Abonnement validé', icon: CircleCheck, text: 'Votre abonnement a été validé par l’administration. Vous pouvez désormais recevoir les actualités de l’École Doctorale EDEN.' },
    rejected: { title: 'Demande refusée', icon: MailX, text: 'Votre demande n’a pas été retenue. Aucune newsletter ne sera envoyée à cette adresse. Vous pouvez contacter l’EDEN pour plus d’informations.' },
    unsubscribed: { title: 'Vous êtes désinscrit', icon: MailX, text: 'Cette adresse ne recevra plus de newsletter.' },
};
const state = computed(() => states[props.status] ?? states.pending);
const resend = () => form.post('/newsletter/confirmation/renvoyer', { preserveScroll: true });
</script>

<template>
    <Head title="Inscription newsletter"><meta name="robots" content="noindex, nofollow"></Head>

    <main id="main" class="grid min-h-dvh place-items-center bg-muted px-4 py-10">
        <section class="grid w-full max-w-sm justify-items-center gap-4 rounded-xl border bg-card p-8 text-center shadow-sm">
            <span class="grid size-12 place-items-center rounded-full bg-eden-50 text-eden-600 dark:bg-eden-950" aria-hidden="true"><component :is="state.icon" class="size-6" /></span>
            <h1 class="text-2xl font-semibold tracking-tight">{{ state.title }}</h1>
            <strong class="w-full min-w-0 text-sm font-medium break-all">{{ email }}</strong>
            <p class="text-sm leading-relaxed text-muted-foreground">{{ status === 'pending' && mailError ? 'Confirmez votre adresse e-mail, puis attendez la validation d’un administrateur pour recevoir les actualités.' : state.text }}</p>
            <p v-if="mailError" role="alert" class="text-sm text-destructive">L’e-mail n’a pas pu être envoyé. Votre demande est enregistrée mais reste inactive. Réessayez de renvoyer le lien.</p>
            <p v-if="resent" role="status" class="text-sm text-eden-700 dark:text-eden-300">Un nouveau lien vous a été envoyé. Les liens précédents ne sont plus valables.</p>
            <form v-if="status === 'pending'" class="grid w-full gap-2" @submit.prevent="resend">
                <button type="submit" :class="cn(buttonVariants({ variant: 'outline' }), 'h-12 w-full whitespace-normal')" :disabled="form.processing">{{ form.processing ? 'Envoi…' : 'Renvoyer le lien de confirmation' }}</button>
                <p v-if="form.errors.email" class="text-sm text-destructive" role="alert">{{ form.errors.email }}</p>
                <p class="text-xs text-muted-foreground">Patientez une minute entre deux envois.</p>
            </form>
            <Link href="/" :class="cn(buttonVariants(), 'mt-2 w-full')">Retour au site</Link>
        </section>
    </main>
</template>
