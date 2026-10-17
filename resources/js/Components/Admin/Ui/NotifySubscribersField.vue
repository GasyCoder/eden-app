<script setup>
import { Link } from '@inertiajs/vue3';
import { CircleCheck, Clock, Mail, OctagonAlert, Send } from '@lucide/vue';
import { computed } from 'vue';
import Switch from '@/Components/ui/Switch.vue';
import { formatDate } from '@/lib/i18n';

// Case « Envoyer aux abonnés » : l'email part à la publication du contenu (file d'attente).
const props = defineProps({
    newsletter: { type: Object, default: () => ({ subscribers: 0, campaign: null }) },
    published: { type: Boolean, default: false },
    noun: { type: String, default: 'ce contenu' },
});

const model = defineModel({ type: Boolean, default: false });
const campaign = computed(() => props.newsletter?.campaign ?? null);
const blocking = computed(() => ['scheduled', 'queued', 'sending', 'sent'].includes(campaign.value?.status));
const when = (iso) => formatDate(iso, 'fr', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
</script>

<template>
    <section class="grid gap-3 rounded-xl border bg-card p-5 text-card-foreground">
        <h2 class="flex items-center gap-2 text-base font-semibold"><Mail class="size-4 text-eden-600" aria-hidden="true" />Newsletter</h2>

        <div v-if="campaign?.status === 'sent'" class="flex items-start gap-2.5 rounded-lg bg-eden-50 p-3 text-sm text-eden-800 dark:bg-eden-950 dark:text-eden-200">
            <CircleCheck class="mt-0.5 size-4 shrink-0" aria-hidden="true" />
            <span>Envoyé le {{ when(campaign.finished_at) }} à {{ campaign.sent_count }} abonné{{ campaign.sent_count > 1 ? 's' : '' }}<template v-if="campaign.failed_count">, {{ campaign.failed_count }} échec{{ campaign.failed_count > 1 ? 's' : '' }}</template>.</span>
        </div>
        <div v-else-if="campaign?.status === 'scheduled'" class="flex items-start gap-2.5 rounded-lg bg-gold-50 p-3 text-sm text-gold-900 dark:bg-gold-950 dark:text-gold-200">
            <Clock class="mt-0.5 size-4 shrink-0" aria-hidden="true" />
            <span v-if="campaign.scheduled_at">Envoi programmé le {{ when(campaign.scheduled_at) }}, à la mise en ligne.</span>
            <span v-else>Envoi programmé : il partira dès que {{ noun }} sera publié.</span>
        </div>
        <div v-else-if="['queued', 'sending'].includes(campaign?.status)" class="flex items-start gap-2.5 rounded-lg bg-secondary p-3 text-sm">
            <Send class="mt-0.5 size-4 shrink-0 text-eden-600" aria-hidden="true" />
            <span>Envoi en cours vers {{ campaign.recipients_count }} abonné{{ campaign.recipients_count > 1 ? 's' : '' }}.</span>
        </div>
        <div v-else-if="campaign?.status === 'failed'" class="flex items-start gap-2.5 rounded-lg bg-laterite-50 p-3 text-sm text-laterite-800 dark:bg-laterite-950 dark:text-laterite-200">
            <OctagonAlert class="mt-0.5 size-4 shrink-0" aria-hidden="true" />
            <span>Le dernier envoi a échoué. <Link href="/admin/newsletter/campaigns" class="font-medium underline underline-offset-4">Voir les envois</Link></span>
        </div>

        <template v-if="!blocking">
            <Switch
                v-model="model"
                label="Envoyer aux abonnés"
                :disabled="!newsletter.subscribers"
                :description="newsletter.subscribers
                    ? `${newsletter.subscribers} abonné${newsletter.subscribers > 1 ? 's' : ''} actif${newsletter.subscribers > 1 ? 's' : ''}. ${published ? 'L’email part à l’enregistrement (ou à la date de publication si elle est future).' : 'Le contenu n’est pas publié : l’email attendra sa publication.'}`
                    : 'Aucun abonné actif pour le moment.'"
            />
        </template>
        <p v-else class="text-xs text-muted-foreground">Un contenu n’est envoyé qu’une fois aux abonnés.</p>
    </section>
</template>
