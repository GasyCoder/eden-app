<?php

use App\Models\ContentBlock;
use App\Models\ContentItem;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

function settingsPayload(array $overrides = []): array
{
    return array_merge([
        'site_name' => 'EDEN',
        'site_caption' => 'École Doctorale Écosystèmes Naturels',
        'logo' => '/storage/content/logo.png',
        'favicon' => '/storage/content/favicon.png',
        'seo_title' => 'EDEN | Université de Mahajanga',
        'seo_description' => 'Formation doctorale et recherche.',
        'seo_keywords' => 'EDEN, doctorat',
        'og_image' => '',
        'robots' => 'index',
        'google_verification' => '',
        'analytics_id' => '',
        'contact_email' => 'contact@eden.mg',
        'contact_phone' => '+261 20 62 000 00',
        'contact_address' => 'BP 652, Mahajanga',
        'contact_website' => 'www.univ-mahajanga.mg',
        'socials' => [['title' => 'Facebook', 'link_url' => 'https://facebook.com/eden']],
        'colors' => ['hero' => '#FFFFFF'],
    ], $overrides);
}

test('only the superadmin can open site settings', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true, 'role' => 'editor']))->get('/admin/settings')->assertForbidden();

    $this->actingAs(User::factory()->create(['is_admin' => true, 'role' => 'superadmin']))->get('/admin/settings')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Admin/Settings/Edit', false)->has('settings')->has('networks'));
});

test('site settings update identity, seo, contact and social links in both languages', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true, 'role' => 'superadmin']))
        ->put('/admin/settings', settingsPayload(['robots' => 'noindex', 'analytics_id' => 'G-ABC1234XYZ']))
        ->assertRedirect();

    expect(ContentBlock::query()->where('key', 'seo.title')->value('value'))->toBe('EDEN | Université de Mahajanga')
        ->and(ContentBlock::query()->where('key', 'contact.email')->value('value'))->toBe('contact@eden.mg')
        ->and(ContentItem::query()->where('collection_key', 'footer_socials')->where('locale', 'en')->pluck('link_url')->all())->toBe(['https://facebook.com/eden']);

    $html = $this->get('/')->getContent();
    expect($html)->toContain('content="noindex, nofollow"')->toContain('name="eden-analytics" content="G-ABC1234XYZ"');
});

test('site settings validate analytics id and social urls', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true, 'role' => 'superadmin']))
        ->put('/admin/settings', settingsPayload(['analytics_id' => 'UA-123', 'socials' => [['title' => 'X', 'link_url' => 'pas-une-url']]]))
        ->assertSessionHasErrors(['analytics_id', 'socials.0.link_url']);
});
