<script setup>
import { ArrowRight } from '@lucide/vue';
import { useT } from '@/lib/i18n';
import { buttonVariants, containerClass } from '@/lib/variants';

const { t, path } = useT();
</script>

<template>
    <section class="bg-background py-16">
        <div :class="containerClass">
            <div class="flex flex-col items-start justify-between gap-6 rounded-xl border bg-card p-6 sm:p-8 lg:flex-row lg:items-center">
                <div>
                    <h2 class="text-xl font-semibold tracking-tight">{{ t('contactCta') }}</h2>
                    <p class="mt-1 text-muted-foreground">{{ t('contactCtaText') }}</p>
                </div>
                <a :href="path('contact')" :class="buttonVariants()">
                    {{ t('writeUs') }}<ArrowRight aria-hidden="true" />
                </a>
            </div>
        </div>
    </section>
</template>
