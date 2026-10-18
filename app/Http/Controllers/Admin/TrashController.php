<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentItem;
use App\Models\ContentRevision;
use App\Models\Journal;
use App\Models\JournalIssue;
use App\Services\TrashRegistry;
use App\Support\Paginated;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class TrashController extends Controller
{
    public function index(Request $request, TrashRegistry $registry): Response
    {
        $types = $registry->types($request->user());
        $type = $request->query('type', '');
        abort_unless(is_string($type) && ($type === '' || isset($types[$type])), 404);
        $search = trim($request->string('q')->toString());
        $union = null;

        foreach ($types as $key => [$class, $label]) {
            if ($type !== '' && $key !== $type) {
                continue;
            }

            $query = $registry->query($request->user(), $key);
            if ($search !== '') {
                $column = match ($key) {
                    'users', 'post-categories' => 'name',
                    'newsletter-subscribers' => 'email',
                    default => 'title',
                };
                $query->where($column, 'like', '%'.$search.'%');
            }
            $part = $query->select('id', 'deleted_at')->selectRaw('? as type', [$key])->toBase();
            $union = $union ? $union->unionAll($part) : $part;
        }

        $items = DB::query()->fromSub($union, 'trash_items')
            ->orderByDesc('deleted_at')->orderBy('type')->orderByDesc('id')->paginate(20);

        return Inertia::render('Admin/Trash/Index', [
            'items' => Paginated::from($items, fn ($row) => $registry->row(
                $registry->query($request->user(), $row->type)->findOrFail($row->id), $row->type,
            )),
            'types' => collect($types)->map(fn ($value, $key) => ['value' => $key, 'label' => $value[1]])->values()->all(),
            'filters' => ['type' => $type, 'q' => $search],
        ]);
    }

    public function restore(Request $request, string $type, int $id, TrashRegistry $registry): RedirectResponse
    {
        $item = $registry->query($request->user(), $type)->findOrFail($id);

        if ($item instanceof JournalIssue && ! Journal::whereKey($item->journal_id)->exists()) {
            return to_route('admin.trash.index', [], 303)->with('error', 'Restaurez d’abord la revue associée à ce numéro.');
        }

        DB::transaction(function () use ($item): void {
            if ($item instanceof JournalIssue) {
                $item->forceFill(['deleted_with_journal' => false]);
            }
            $item->restore();
        });

        return to_route('admin.trash.index', [], 303)->with('success', 'Élément restauré.');
    }

    public function destroy(Request $request, string $type, int $id, TrashRegistry $registry): RedirectResponse
    {
        $item = $registry->query($request->user(), $type)->findOrFail($id);
        DB::transaction(function () use ($item): void {
            if ($item instanceof ContentItem) {
                ContentRevision::query()->where('content_type', 'item')->where('content_id', $item->id)->delete();
            }
            $item->forceDelete();
        });

        return to_route('admin.trash.index', [], 303)->with('success', 'Élément supprimé définitivement.');
    }
}
