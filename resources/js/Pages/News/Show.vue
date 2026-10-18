<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { Archive, Eye } from '@lucide/vue';
import PostCard from '@/Components/Portal/PostCard.vue';
import ShareButtons from '@/Components/Portal/ShareButtons.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { formatDate, useT } from '@/lib/i18n';
import { cn } from '@/lib/utils';
import { badgeVariants, buttonVariants, containerClass } from '@/lib/variants';

defineProps({
    post: { type: Object, required: true },
    recentPosts: { type: Array, default: () => [] },
});

const { t, path, locale } = useT();
</script>

<template>
    <Head :title="post.seo_title">
        <meta name="description" :content="post.meta_description">
    </Head>

    <PublicLayout :editable="false">
        <article class="pb-20">
            <header class="mx-auto max-w-3xl px-4 pt-12 pb-10 sm:px-6 lg:pt-16">
                <div v-if="post.status === 'archived'" class="mb-6 grid justify-items-start gap-2 rounded-lg border bg-muted/50 p-4" role="status">
                    <span :class="badgeVariants({ variant: 'secondary' })"><Archive class="size-3" aria-hidden="true" />{{ t('archivedPost') }}</span>
                    <p class="text-sm text-muted-foreground">{{ t('archivedPostNotice') }}</p>
                </div>
                <div v-if="!post.is_public" class="mb-6 flex items-start gap-3 rounded-lg border border-gold-300 bg-gold-50 p-4 text-sm text-gold-900 dark:border-gold-800 dark:bg-gold-950 dark:text-gold-200" role="status">
                    <Eye class="mt-0.5 size-4 shrink-0" aria-hidden="true" /><span>{{ t('draftPreview') }}</span>
                </div>
                <nav :aria-label="t('home')">
                    <ol class="flex flex-wrap items-center gap-1.5 text-sm text-muted-foreground">
                        <li><Link :href="path('home')" class="hover:text-foreground">{{ t('home') }}</Link></li>
                        <li aria-hidden="true">/</li>
                        <li><Link :href="path('news')" class="hover:text-foreground">{{ t('news') }}</Link></li>
                        <template v-if="post.category">
                            <li aria-hidden="true">/</li>
                            <li><Link :href="`${path('news')}?categorie=${post.category.slug}`" class="font-medium text-eden-700 hover:underline dark:text-eden-300">{{ post.category.name }}</Link></li>
                        </template>
                    </ol>
                </nav>
                <h1 class="mt-5 text-3xl leading-tight font-bold tracking-tight text-balance sm:text-4xl lg:text-5xl">{{ post.title }}</h1>
                <p v-if="post.excerpt" class="mt-5 text-xl leading-relaxed text-pretty text-muted-foreground">{{ post.excerpt }}</p>
                <div class="mt-8 flex flex-wrap items-center justify-between gap-4 border-t pt-5 text-sm text-muted-foreground">
                    <p>
                        <span v-if="post.author">{{ t('by') }} <strong class="font-semibold text-foreground">{{ post.author }}</strong> · </span>
                        <time :datetime="post.published_at">{{ formatDate(post.published_at, locale) }}</time>
                        · {{ t('minRead', post.reading_minutes) }}
                    </p>
                    <ShareButtons :title="post.title" />
                </div>
            </header>

            <figure v-if="post.cover_image" :class="cn(containerClass, 'mb-12 max-w-5xl')">
                <picture class="block aspect-[2/1] overflow-hidden rounded-xl border bg-secondary">
                    <source v-for="source in post.cover_sources?.sources ?? []" :key="source.type" :type="source.type" :srcset="source.srcset" sizes="(max-width: 1024px) 100vw, 1024px">
                    <img :src="post.cover_image" :alt="post.title" class="size-full object-cover" fetchpriority="high" decoding="async">
                </picture>
            </figure>

            <!-- Contenu nettoyé côté serveur (App\Services\RichTextSanitizer) -->
            <div class="mx-auto max-w-3xl px-4 sm:px-6">
                <div v-if="post.body" class="prose prose-lg prose-eden max-w-none" v-html="post.body"></div>
                <footer class="mt-12 border-t pt-6"><ShareButtons :title="post.title" /></footer>
            </div>
        </article>

        <section v-if="recentPosts.length" class="bg-muted py-16 lg:py-20">
            <div :class="containerClass">
                <div class="mb-10 flex items-center justify-between gap-4">
                    <h2 class="text-2xl font-semibold tracking-tight">{{ t('recentNews') }}</h2>
                    <Link :href="path('news')" :class="buttonVariants({ variant: 'outline' })">{{ t('seeAll') }}</Link>
                </div>
                <div class="grid gap-x-6 gap-y-12 md:grid-cols-2 lg:grid-cols-3">
                    <PostCard v-for="item in recentPosts" :key="item.id" :post="item" />
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
