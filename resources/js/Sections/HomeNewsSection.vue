<script setup>
import { Link } from '@inertiajs/vue3';
import { ArrowRight, Newspaper } from '@lucide/vue';
import PostCard from '@/Components/Portal/PostCard.vue';
import SectionHeading from '@/Components/Portal/SectionHeading.vue';
import EmptyState from '@/Components/ui/EmptyState.vue';
import { useT } from '@/lib/i18n';
import { buttonVariants, containerClass } from '@/lib/variants';

defineProps({
    posts: { type: Array, default: () => [] },
    canEdit: { type: Boolean, default: false },
});

const { t, path } = useT();
</script>

<template>
    <section id="actualites" class="bg-background py-16 lg:py-20">
        <div :class="containerClass">
            <div class="flex flex-wrap items-end justify-between gap-6">
                <SectionHeading>{{ t('latestNews') }}</SectionHeading>
                <Link v-if="posts.length" :href="path('news')" :class="buttonVariants({ variant: 'outline' })">{{ t('seeAll') }}<ArrowRight aria-hidden="true" /></Link>
            </div>
            <div v-if="posts.length" class="mt-10 grid gap-x-6 gap-y-10 md:grid-cols-2 lg:grid-cols-3">
                <PostCard v-for="(post, index) in posts" :key="post.id" :post="post" :featured-badge="index === 0 && post.is_featured" />
            </div>
            <p v-else-if="!canEdit" class="mt-8 max-w-xl text-muted-foreground">{{ t('noNewsYet') }}</p>
            <EmptyState v-else class="mt-10" :icon="Newspaper" title="Aucune actualité publiée" description="Cette section apparaît sur l’accueil dès qu’une actualité est publiée (visible uniquement par les administrateurs).">
                <Link href="/admin/posts/create" :class="buttonVariants({ variant: 'outline', size: 'sm' })">Rédiger une actualité</Link>
            </EmptyState>
        </div>
    </section>
</template>
