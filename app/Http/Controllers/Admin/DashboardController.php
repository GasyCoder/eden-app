<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Event;
use App\Models\Journal;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscription;
use App\Models\Post;
use App\Models\Project;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'posts' => ['published' => Post::query()->published()->count(), 'drafts' => Post::query()->where('status', 'draft')->count()],
                'events' => ['upcoming' => Event::query()->visible()->upcoming()->count(), 'drafts' => Event::query()->where('status', 'draft')->count()],
                'journals' => ['active' => Journal::query()->where('status', 'active')->count(), 'total' => Journal::query()->count()],
                'announcements' => ['live' => Announcement::query()->live()->count(), 'total' => Announcement::query()->count()],
                'projects' => ['published' => Project::query()->published()->count(), 'ongoing' => Project::query()->published()->where('progress', 'ongoing')->count()],
                'newsletter' => ['active' => NewsletterSubscription::query()->active()->count(), 'scheduled' => NewsletterCampaign::query()->where('status', 'scheduled')->count()],
            ],
            'recentPosts' => Post::query()->with('category', 'author')->latest('updated_at')->limit(5)->get()
                ->map(fn (Post $post) => $post->toAdminRow())->values()->all(),
            'upcomingEvents' => Event::query()->where('status', '<>', 'draft')->upcoming()->limit(4)->get()
                ->map(fn (Event $event) => $event->toAdminRow())->values()->all(),
            'liveAnnouncements' => Announcement::query()->live()->orderByDesc('priority')->get()
                ->map(fn (Announcement $announcement) => $announcement->toAdminRow())->values()->all(),
        ]);
    }
}
