<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\ContentItem;
use App\Models\Event;
use App\Models\HeroSlide;
use App\Models\Journal;
use App\Models\JournalIssue;
use App\Models\NewsletterSubscription;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TrashRegistry
{
    public const TYPES = [
        'posts' => [Post::class, 'Actualités'],
        'events' => [Event::class, 'Événements'],
        'projects' => [Project::class, 'Projets'],
        'journals' => [Journal::class, 'Revues'],
        'journal-issues' => [JournalIssue::class, 'Numéros de revues'],
        'announcements' => [Announcement::class, 'Annonces'],
        'hero-slides' => [HeroSlide::class, 'Diapositives du héro'],
        'post-categories' => [PostCategory::class, 'Catégories'],
        'content-items' => [ContentItem::class, 'Éléments des pages'],
        'newsletter-subscribers' => [NewsletterSubscription::class, 'Abonnés newsletter'],
        'users' => [User::class, 'Utilisateurs'],
    ];

    public function types(User $user): array
    {
        return array_filter(self::TYPES, fn ($value, $key) => $key !== 'users' || $user->isSuperAdmin(), ARRAY_FILTER_USE_BOTH);
    }

    public function query(User $user, string $type): Builder
    {
        $types = $this->types($user);
        abort_unless(isset($types[$type]), 404);
        $query = $types[$type][0]::onlyTrashed();

        if ($type === 'content-items' && ! $user->isSuperAdmin()) {
            $query->whereNotIn('collection_key', config('eden.superadmin_only_blocks', []));
        }

        return $query;
    }

    public function row(Model $item, string $type): array
    {
        $title = match (true) {
            $item instanceof User => $item->name.' · '.$item->email,
            $item instanceof NewsletterSubscription => $item->email,
            $item instanceof PostCategory => $item->translate('name', 'fr'),
            $item instanceof JournalIssue => $item->label(),
            $item instanceof ContentItem => $item->title ?: $item->collection_key,
            default => $item->translate('title', 'fr'),
        };
        $blocked = $item instanceof JournalIssue && ! Journal::whereKey($item->journal_id)->exists();

        return [
            'id' => $item->id,
            'type' => $type,
            'type_label' => self::TYPES[$type][1],
            'title' => $title ?: self::TYPES[$type][1].' #'.$item->id,
            'deleted_at' => $item->deleted_at->toIso8601String(),
            'can_restore' => ! $blocked,
            'restore_hint' => $blocked ? 'Restaurez d’abord la revue associée.' : null,
        ];
    }
}
