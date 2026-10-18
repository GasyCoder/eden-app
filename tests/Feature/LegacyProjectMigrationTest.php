<?php

use App\Models\ContentItem;
use App\Models\Project;

test('project migration never associates unrelated translations by their position', function () {
    Project::withTrashed()->forceDelete();
    ContentItem::withTrashed()->where('collection_key', 'projects')->forceDelete();
    ContentItem::create(['collection_key' => 'projects', 'locale' => 'fr', 'title' => 'COFFEA DECARYANA CASE STUDY', 'description' => 'Texte français conservé', 'position' => 0, 'is_active' => true]);
    ContentItem::create(['collection_key' => 'projects', 'locale' => 'en', 'title' => 'BIODIVERS-BOENY', 'description' => 'Independent English project', 'position' => 0, 'is_active' => true]);
    ContentItem::create(['collection_key' => 'projects', 'locale' => 'fr', 'title' => 'BRIDGE-COFFEA', 'description' => 'Version française', 'position' => 1, 'is_active' => true]);
    ContentItem::create(['collection_key' => 'projects', 'locale' => 'en', 'title' => 'BRIDGE-COFFEA', 'description' => 'English version', 'position' => 7, 'is_active' => true]);

    $migration = require database_path('migrations/2026_10_18_000002_copy_projects_and_ravenala.php');
    $migration->up();
    $coffea = Project::where('slug', 'coffea-decaryana-case-study')->sole();
    $english = Project::where('slug', 'biodivers-boeny')->sole();
    $bridge = Project::where('slug', 'bridge-coffea')->sole();
    expect($coffea->title)->toBe(['fr' => 'COFFEA DECARYANA CASE STUDY'])
        ->and($coffea->summary)->toBe(['fr' => 'Texte français conservé'])
        ->and($english->title)->toBe(['en' => 'BIODIVERS-BOENY'])
        ->and($english->summary)->toBe(['en' => 'Independent English project'])
        ->and($bridge->summary)->toBe(['fr' => 'Version française', 'en' => 'English version'])
        ->and(ContentItem::where('collection_key', 'projects')->count())->toBe(4);
    $migration->up();
    expect(Project::count())->toBe(4);
});

test('project migration enriches a single canonical ravenala while preserving edited content', function () {
    Project::withTrashed()->forceDelete();
    ContentItem::withTrashed()->where('collection_key', 'projects')->forceDelete();
    ContentItem::create(['collection_key' => 'projects', 'locale' => 'fr', 'title' => 'Projet RAVENALA', 'description' => 'Résumé de production', 'image' => '/storage/ravenala.jpg', 'link_url' => 'https://eden.mahajanga-univ.mg/projets/ravenala', 'position' => 1, 'is_active' => false]);

    $migration = require database_path('migrations/2026_10_18_000002_copy_projects_and_ravenala.php');
    $migration->up();
    $ravenala = Project::sole();
    expect($ravenala->slug)->toBe('ravenala')
        ->and($ravenala->summary)->toBe(['fr' => 'Résumé de production'])
        ->and($ravenala->cover_image)->toBe('/storage/ravenala.jpg')
        ->and($ravenala->external_url)->toBeNull()
        ->and($ravenala->status)->toBe('draft')
        ->and($ravenala->body['fr'])->toContain('RAVENALA')
        ->and($ravenala->details['partners'])->toHaveCount(5)
        ->and($ravenala->title['en'])->toBe('RAVENALA project');
});
