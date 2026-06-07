<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateContentBlockRequest;
use App\Models\ContentBlock;
use App\Services\ContentRevisionRecorder;
use App\Services\ContentSanitizer;
use App\Support\ContentLocale;
use Illuminate\Http\RedirectResponse;

class ContentBlockController extends Controller
{
    public function update(
        UpdateContentBlockRequest $request,
        string $key,
        ContentSanitizer $sanitizer,
        ContentRevisionRecorder $revisions,
    ): RedirectResponse {
        abort_unless($request->user()->canEditBlock($key), 403, 'Ce contenu est réservé au superadmin.');

        $locale = ContentLocale::fromRequest($request);
        $source = ContentBlock::where('key', $key)
            ->locale(ContentLocale::DEFAULT)
            ->firstOrFail();
        $block = ContentBlock::firstOrNew([
            'key' => $key,
            'locale' => $locale,
        ]);

        if (! $block->exists) {
            $block->fill([
                'type' => $source->type,
                'group' => $source->group,
                'position' => $source->position,
                'is_active' => $source->is_active,
            ]);
        }

        $type = $request->validated('type') ?? $block->type;
        $wasExisting = $block->exists;
        $before = $wasExisting ? $revisions->snapshotBlock($block) : null;

        $block->fill([
            'type' => $type,
            'value' => $sanitizer->clean($request->validated('value'), $type),
            'meta' => $request->has('meta') ? $sanitizer->cleanMixed($request->validated('meta')) : $block->meta,
        ]);
        $block->save();

        $revisions->recordBlock($block, $wasExisting ? 'updated' : 'created', $before, $revisions->snapshotBlock($block));

        return back(303)->with('success', 'Contenu enregistré.');
    }
}
