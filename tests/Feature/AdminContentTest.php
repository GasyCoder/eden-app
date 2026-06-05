<?php

use App\Models\ContentBlock;
use App\Models\User;

test('guest cannot update content blocks', function () {
    ContentBlock::create([
        'key' => 'hero.title',
        'value' => 'Old title',
    ]);

    $this->patch('/admin/content-blocks/hero.title', [
        'value' => 'New title',
    ])->assertRedirect('/login');
});

test('admin can update content blocks', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    ContentBlock::create([
        'key' => 'hero.title',
        'value' => 'Old title',
    ]);

    $this->actingAs($admin)
        ->patch('/admin/content-blocks/hero.title', [
            'value' => 'New title',
            'type' => 'text',
        ])
        ->assertRedirect();

    expect(ContentBlock::where('key', 'hero.title')->value('value'))->toBe('New title');
});
