<script setup>
import { Link } from '@inertiajs/vue3';
import { formatDate, useT } from '@/lib/i18n';
import { cn } from '@/lib/utils';
import { badgeVariants } from '@/lib/variants';

defineProps({
    post: { type: Object, required: true },
    featured: { type: Boolean, default: false },
    featuredBadge: { type: Boolean, default: false },
});

const { t, path, locale } = useT();
</script>

<template>
    <article :class="cn('group relative flex flex-col gap-4', featured && 'gap-6 border-b pb-12 md:grid md:grid-cols-[minmax(0,1.25fr)_minmax(0,1fr)] md:items-center md:gap-10')">
        <div :class="cn('relative overflow-hidden rounded-xl border bg-secondary', featured ? 'aspect-video' : 'aspect-[16/10]')">
            <picture v-if="post.cover_image">
                <source v-for="source in post.cover_sources?.sources ?? []" :key="source.type" :type="source.type" :srcset="source.srcset" :sizes="post.cover_sources?.sizes">
                <img :src="post.cover_image" alt="" class="size-full object-cover" loading="lazy" decoding="async">
            </picture>
            <div v-else class="flex size-full items-center justify-center bg-eden-50 p-6 dark:bg-eden-950">
                <span class="text-center text-sm font-semibold text-eden-700 dark:text-eden-300">{{ post.category?.name ?? 'EDEN' }}</span>
            </div>
        </div>
        <div class="grid gap-2.5">
            <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-muted-foreground">
                <span v-if="featured || featuredBadge" :class="badgeVariants({ variant: 'warning' })">{{ t('featured') }}</span>
                <span v-if="post.category" class="font-semibold text-eden-700 dark:text-eden-300">{{ post.category.name }}</span>
                <span v-if="post.category" aria-hidden="true">·</span>
                <time :datetime="post.published_at">{{ formatDate(post.published_at, locale) }}</time>
            </div>
            <h3 :class="cn('font-semibold tracking-tight text-balance text-foreground', featured ? 'text-2xl leading-tight sm:text-3xl' : 'text-lg leading-snug')">
                <Link :href="path('news', post.slug)" class="outline-none after:absolute after:inset-0 after:rounded-xl group-hover:underline group-hover:decoration-eden-600/40 group-hover:underline-offset-4 focus-visible:after:ring-[3px] focus-visible:after:ring-ring/50">{{ post.title }}</Link>
            </h3>
            <p v-if="post.excerpt" :class="cn('text-muted-foreground', featured ? 'line-clamp-4 text-base leading-relaxed' : 'line-clamp-3 text-sm leading-relaxed')">{{ post.excerpt }}</p>
            <p v-if="featured" class="text-xs text-muted-foreground">
                <span v-if="post.author">{{ t('by') }} {{ post.author }} · </span>{{ t('minRead', post.reading_minutes) }}
            </p>
        </div>
    </article>
</template>
