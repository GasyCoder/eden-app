<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { CircleCheck, MailX } from '@lucide/vue';
import { useT } from '@/lib/i18n';
import { cn } from '@/lib/utils';
import { buttonVariants } from '@/lib/variants';

const props = defineProps({
    email: { type: String, required: true },
    token: { type: String, required: true },
    unsubscribed: { type: Boolean, default: false },
});

const { t, path } = useT();
const form = useForm({});
const confirm = () => form.post(`/newsletter/desabonnement/${props.token}`);
</script>

<template>
    <Head :title="t('unsubscribeTitle')">
        <meta name="robots" content="noindex">
    </Head>

    <main id="main" class="grid min-h-dvh place-items-center bg-muted px-4 py-10">
        <section class="grid w-full max-w-sm justify-items-center gap-4 rounded-xl bg-card p-8 text-center shadow-sm">
            <template v-if="unsubscribed">
                <span class="grid size-12 place-items-center rounded-full bg-eden-50 text-eden-600 dark:bg-eden-950" aria-hidden="true"><CircleCheck class="size-6" /></span>
                <h1 class="text-2xl font-semibold tracking-tight">{{ t('unsubscribed') }}</h1>
                <p class="text-sm leading-relaxed text-muted-foreground">{{ t('unsubscribedText', email) }}</p>
            </template>
            <template v-else>
                <span class="grid size-12 place-items-center rounded-full bg-laterite-50 text-laterite-600 dark:bg-laterite-950" aria-hidden="true"><MailX class="size-6" /></span>
                <h1 class="text-2xl font-semibold tracking-tight">{{ t('unsubscribeTitle') }}</h1>
                <p class="text-sm leading-relaxed text-muted-foreground">{{ t('unsubscribeText', email) }}</p>
                <button type="button" :class="cn(buttonVariants(), 'mt-2 w-full')" :disabled="form.processing" @click="confirm">{{ t('unsubscribeConfirm') }}</button>
            </template>
            <Link :href="path('home')" :class="cn(buttonVariants({ variant: 'outline' }), 'w-full')">{{ t('home') }}</Link>
        </section>
    </main>
</template>
