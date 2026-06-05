<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateContentBlockRequest;
use App\Models\ContentBlock;
use App\Services\ContentRevisionRecorder;
use App\Services\ContentSanitizer;
use Illuminate\Http\RedirectResponse;

class ContentBlockController extends Controller
{
    public function update(
        UpdateContentBlockRequest $request,
        string $key,
        ContentSanitizer $sanitizer,
        ContentRevisionRecorder $revisions,
    ): RedirectResponse {
        $block = ContentBlock::where('key', $key)->firstOrFail();
        $type = $request->validated('type') ?? $block->type;
        $before = $revisions->snapshotBlock($block);

        $block->update([
            'type' => $type,
            'value' => $sanitizer->clean($request->validated('value'), $type),
            'meta' => $request->has('meta') ? $sanitizer->cleanMixed($request->validated('meta')) : $block->meta,
        ]);

        $revisions->recordBlock($block, 'updated', $before, $revisions->snapshotBlock($block));

        return back(303)->with('success', 'Contenu enregistré.');
    }
}
