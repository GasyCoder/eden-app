<script setup>
import { Head } from '@inertiajs/vue3';
import PageHeader from '@/Components/Portal/PageHeader.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useT } from '@/lib/i18n';
import ContactCta from '@/Sections/ContactCta.vue';
import DoctoralPathSection from '@/Sections/DoctoralPathSection.vue';

defineProps({
    blocks: { type: Object, default: () => ({}) },
    collections: { type: Object, default: () => ({}) },
});

const { t } = useT();
</script>

<template>
    <Head :title="t('training')" />

    <PublicLayout v-slot="{ editMode }">
        <PageHeader :title="t('training')" :lead="t('trainingLead')" :crumbs="[{ label: t('training') }]" />
        <DoctoralPathSection
            :blocks="blocks.formation"
            :conditions="collections.formation_conditions"
            :steps="collections.formation_steps"
            :units="collections.formation_units"
            :can-edit="editMode"
        />
        <ContactCta />
    </PublicLayout>
</template>
