<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Event;
use App\Models\Journal;
use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Database\Seeder;

/**
 * Contenus de démonstration pour les modules du portail (actualités, événements,
 * revues, annonces). Non exécuté par défaut :
 *   php artisan db:seed --class=PortalDemoSeeder
 * Toutes les entrées portent le préfixe de slug « demo- » pour être retirées facilement.
 */
class PortalDemoSeeder extends Seeder
{
    public function run(): void
    {
        $categories = collect([
            ['demo-vie-doctorale', 'Vie doctorale', 'Doctoral life'],
            ['demo-soutenances', 'Soutenances', 'Thesis defenses'],
            ['demo-recherche', 'Recherche', 'Research'],
        ])->map(fn ($c, $i) => PostCategory::updateOrCreate(['slug' => $c[0]], ['name' => ['fr' => $c[1], 'en' => $c[2]], 'position' => $i]));

        $body = '<p>L’École Doctorale Écosystèmes Naturels accompagne chaque année de nouveaux doctorants dans des travaux de terrain exigeants, au plus près des forêts sèches, des mangroves et des récifs du nord-ouest de Madagascar.</p><h2>Une démarche collective</h2><p>Les équipes d’accueil réunissent encadrants, partenaires et étudiants autour de protocoles partagés. Les données collectées alimentent des publications, mais aussi les plans de gestion des aires protégées de la région Boeny.</p><ul><li>Inventaires floristiques et faunistiques</li><li>Suivi de la régénération des mangroves</li><li>Ateliers de restitution auprès des communautés</li></ul><blockquote>La recherche n’a de sens que si elle revient vers les territoires qui l’ont rendue possible.</blockquote><p>Les résultats seront présentés lors de la prochaine journée scientifique de l’école doctorale.</p>';

        $posts = [
            ['demo-journee-scientifique-2026', 'Journée scientifique 2026 : les doctorants présentent leurs travaux de terrain', 'Science day 2026: doctoral candidates present their fieldwork', 0, true, 2],
            ['demo-soutenance-mangroves', 'Soutenance de thèse : dynamique des mangroves de la baie de Bombetoka', 'Thesis defense: mangrove dynamics in Bombetoka Bay', 1, false, 6],
            ['demo-appel-candidatures', 'Appel à candidatures pour la rentrée doctorale', 'Call for applications for the next doctoral intake', 0, false, 11],
            ['demo-partenariat-ird', 'Un nouveau partenariat de recherche sur la biodiversité marine côtière avec des institutions de l’océan Indien', 'A new partnership on coastal marine biodiversity', 2, false, 20],
            ['demo-atelier-redaction', 'Atelier de rédaction scientifique', 'Scientific writing workshop', 0, false, 33],
        ];

        foreach ($posts as [$slug, $fr, $en, $cat, $featured, $days]) {
            Post::updateOrCreate(['slug' => $slug], [
                'title' => ['fr' => $fr, 'en' => $en],
                'excerpt' => ['fr' => 'Retour sur une étape importante de la vie de l’École Doctorale, avec les témoignages des doctorants et de leurs encadrants.'],
                'body' => ['fr' => $body],
                'post_category_id' => $categories[$cat]->id,
                'author_name' => 'Service communication EDEN',
                'status' => 'published',
                'published_at' => now()->subDays($days),
                'is_featured' => $featured,
            ]);
        }

        Post::updateOrCreate(['slug' => 'demo-brouillon'], [
            'title' => ['fr' => 'Brouillon : compte rendu du conseil'], 'status' => 'draft', 'published_at' => null,
        ]);

        $events = [
            ['demo-seminaire-ecologie', 'Séminaire « Écologie des forêts sèches »', 9, 'in_person', 'Amphithéâtre A, Université de Mahajanga', 'published'],
            ['demo-webinaire-redaction', 'Webinaire : publier dans une revue internationale', 16, 'online', null, 'published'],
            ['demo-soutenance-rakoto', 'Soutenance publique de thèse en écologie marine côtière et gestion durable des ressources halieutiques', 24, 'hybrid', 'Salle du conseil, Campus d’Ambondrona', 'published'],
            ['demo-atelier-annule', 'Atelier cartographie SIG', 30, 'in_person', 'Laboratoire SIG', 'cancelled'],
            ['demo-colloque-2025', 'Colloque annuel 2025', -60, 'in_person', 'Mahajanga', 'published'],
        ];

        foreach ($events as [$slug, $title, $days, $format, $location, $status]) {
            Event::updateOrCreate(['slug' => $slug], [
                'title' => ['fr' => $title],
                'summary' => ['fr' => 'Une rencontre ouverte aux doctorants, chercheurs et partenaires pour échanger sur les avancées récentes.'],
                'body' => ['fr' => $body],
                'starts_at' => now()->addDays($days)->setTime(6, 0),
                'ends_at' => now()->addDays($days)->setTime(9, 0),
                'format' => $format,
                'location' => $location,
                'organizer' => 'École Doctorale EDEN',
                'registration_url' => 'https://example.org/inscription',
                'status' => $status,
            ]);
        }

        $journals = [
            ['demo-revue-ecosystemes', 'Revue malgache des écosystèmes naturels', 'Écologie', '2958-1234', true],
            ['demo-cahiers-biodiversite', 'Cahiers de la biodiversité de l’océan Indien occidental', 'Biodiversité', '2958-5678', true],
            ['demo-bulletin-halieutique', 'Bulletin halieutique', 'Océanographie', null, false],
        ];

        foreach ($journals as $i => [$slug, $title, $domain, $issn, $featured]) {
            Journal::updateOrCreate(['slug' => $slug], [
                'title' => ['fr' => $title],
                'description' => ['fr' => $body],
                'domain' => $domain,
                'issn' => $issn,
                'frequency' => ['fr' => 'Semestrielle', 'en' => 'Biannual'],
                'language' => 'Français, anglais',
                'editor' => 'Comité éditorial EDEN',
                'external_url' => 'https://example.org/revue',
                'status' => $i === 2 ? 'archived' : 'active',
                'is_featured' => $featured,
                'position' => $i,
            ]);
        }

        Announcement::updateOrCreate(['cta_url' => '/evenements#demo'], [
            'title' => ['fr' => 'Inscriptions ouvertes au séminaire d’écologie', 'en' => 'Registration open for the ecology seminar'],
            'message' => ['fr' => 'Places limitées, inscription avant le 15 novembre.'],
            'cta_label' => ['fr' => 'Voir l’événement'],
            'type' => 'event',
            'status' => 'published',
            'priority' => 1,
        ]);
    }
}
