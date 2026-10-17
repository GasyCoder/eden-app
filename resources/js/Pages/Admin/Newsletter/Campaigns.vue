<script setup>
import { Link, router } from '@inertiajs/vue3';
import { RotateCcw, Send, Users, X } from '@lucide/vue';
import { onBeforeUnmount, onMounted } from 'vue';
import AdminPageHeader from '@/Components/Admin/Ui/AdminPageHeader.vue';
import Badge from '@/Components/ui/Badge.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import Pagination from '@/Components/ui/Pagination.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { formatDate } from '@/lib/i18n';
import { buttonVariants } from '@/lib/variants';

const props = defineProps({
    campaigns: { type: Object, required: true },
    subscribers: { type: Number, default: 0 },
    queueViaScheduler: { type: Boolean, default: true },
    mailer: { type: String, default: '' },
    pendingJobs: { type: Number, default: null },
});

const statusLabels = {
    scheduled: ['Programmé', 'warning'],
    queued: ['En file d’attente', 'info'],
    sending: ['Envoi en cours', 'info'],
    sent: ['Envoyé', 'success'],
    failed: ['Échec', 'destructive'],
    cancelled: ['Annulé', 'secondary'],
};
const when = (iso) => (iso ? formatDate(iso, 'fr', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '');
const percent = (campaign) => (campaign.recipients_count ? Math.round(((campaign.sent_count + campaign.failed_count) / campaign.recipients_count) * 100) : 0);
const post = (campaign, action) => router.post(`/admin/newsletter/campaigns/${campaign.id}/${action}`, {}, { preserveScroll: true });

// Rafraîchit la liste tant qu'un envoi est en cours
let timer = null;
onMounted(() => {
    timer = window.setInterval(() => {
        if (props.campaigns.data.some((campaign) => ['queued', 'sending'].includes(campaign.status))) {
            router.reload({ only: ['campaigns', 'pendingJobs'] });
        }
    }, 8000);
});
onBeforeUnmount(() => window.clearInterval(timer));
</script>

<template>
    <AdminLayout title="Envois de la newsletter">
        <AdminPageHeader title="Envois" description="Chaque contenu envoyé aux abonnés : programmé jusqu’à sa publication, puis distribué par la file d’attente." back-href="/admin/newsletter" back-label="Abonnés">
            <template #actions>
                <Link href="/admin/newsletter" :class="buttonVariants({ variant: 'outline' })"><Users aria-hidden="true" />{{ subscribers }} abonné{{ subscribers > 1 ? 's' : '' }}</Link>
            </template>
        </AdminPageHeader>

        <div v-if="mailer === 'log' || mailer === 'array'" class="mb-4 rounded-lg border border-gold-300 bg-gold-50 p-4 text-sm text-gold-900 dark:border-gold-800 dark:bg-gold-950 dark:text-gold-200" role="status">
            Les emails ne partent pas réellement : le serveur d’envoi est réglé sur « {{ mailer }} » (développement). Configurez MAIL_MAILER=smtp et les accès SMTP dans le fichier .env en production.
        </div>
        <p class="mb-4 text-sm text-muted-foreground">
            {{ queueViaScheduler ? 'La file d’attente est traitée chaque minute par la tâche planifiée (cron « php artisan schedule:run »).' : 'La file d’attente est traitée par un worker permanent (php artisan queue:work).' }}
            <template v-if="pendingJobs !== null"> {{ pendingJobs }} tâche{{ pendingJobs > 1 ? 's' : '' }} en attente.</template>
        </p>

        <template v-if="campaigns.data.length">
            <ul class="grid gap-3">
                <li v-for="campaign in campaigns.data" :key="campaign.id" class="grid gap-3 rounded-xl border bg-card p-4 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-center">
                    <div class="grid min-w-0 gap-1.5">
                        <div class="flex flex-wrap items-center gap-2">
                            <Badge :variant="statusLabels[campaign.status]?.[1]" dot>{{ statusLabels[campaign.status]?.[0] }}</Badge>
                            <span class="text-xs text-muted-foreground">{{ campaign.kind }}</span>
                        </div>
                        <component :is="campaign.edit_url ? Link : 'span'" :href="campaign.edit_url" class="truncate font-medium hover:underline hover:underline-offset-4">{{ campaign.subject }}</component>
                        <p class="text-xs text-muted-foreground">
                            <template v-if="campaign.status === 'scheduled'">{{ campaign.scheduled_at ? `Départ prévu le ${when(campaign.scheduled_at)}` : 'Partira à la publication du contenu' }}</template>
                            <template v-else-if="campaign.finished_at">Terminé le {{ when(campaign.finished_at) }}</template>
                            <template v-else-if="campaign.started_at">Lancé le {{ when(campaign.started_at) }}</template>
                            <template v-if="campaign.author"> · par {{ campaign.author }}</template>
                        </p>
                        <div v-if="campaign.recipients_count" class="flex items-center gap-3 text-xs">
                            <span class="h-1.5 w-40 overflow-hidden rounded-full bg-secondary"><span class="block h-full rounded-full bg-eden-600" :style="{ width: `${percent(campaign)}%` }"></span></span>
                            <span class="tabular-nums">{{ campaign.sent_count }} / {{ campaign.recipients_count }} envoyés<template v-if="campaign.failed_count">, <span class="text-destructive">{{ campaign.failed_count }} en échec</span></template></span>
                        </div>
                        <p v-if="campaign.last_error" class="truncate text-xs text-destructive" :title="campaign.last_error">{{ campaign.last_error }}</p>
                    </div>
                    <div class="flex gap-2">
                        <button v-if="campaign.status === 'scheduled'" type="button" :class="buttonVariants({ variant: 'outline', size: 'sm' })" @click="post(campaign, 'cancel')"><X aria-hidden="true" />Annuler</button>
                        <button v-if="campaign.failed_count && ['sent', 'failed'].includes(campaign.status)" type="button" :class="buttonVariants({ variant: 'outline', size: 'sm' })" @click="post(campaign, 'retry')"><RotateCcw aria-hidden="true" />Relancer les échecs</button>
                    </div>
                </li>
            </ul>
            <Pagination :meta="campaigns.meta" :links="campaigns.links" preserve-scroll :labels="{ previous: 'Précédent', next: 'Suivant', info: `${campaigns.meta.from} à ${campaigns.meta.to} sur ${campaigns.meta.total}` }" />
        </template>
        <EmptyState v-else :icon="Send" title="Aucun envoi" description="Cochez « Envoyer aux abonnés » en publiant une actualité, un événement ou un numéro de revue." />
    </AdminLayout>
</template>
