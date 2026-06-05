<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateContentBlockRequest;
use App\Models\ContentBlock;
use App\Services\ContentSanitizer;
use Illuminate\Http\RedirectResponse;

class ContentBlockController extends Controller
{
    public function update(UpdateContentBlockRequest $request, string $key, ContentSanitizer $sanitizer): RedirectResponse
    {
        $block = ContentBlock::where('key', $key)->firstOrFail();
        $type = $request->validated('type') ?? $block->type;

        $block->update([
            'type' => $type,
            'value' => $sanitizer->clean($request->validated('value'), $type),
            'meta' => $request->validated('meta') ?? $block->meta,
        ]);

        return back(303);
    }
}
