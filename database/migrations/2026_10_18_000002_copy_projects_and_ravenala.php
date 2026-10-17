<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Reprend les projets de la collection éditable « projects » (FR + EN) et la page
 * RAVENALA (jusqu'ici écrite en dur) dans la table projects. Les contenus d'origine
 * ne sont pas supprimés.
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $items = DB::table('content_items')->where('collection_key', 'projects')->orderBy('position')->orderBy('id')->get();
        $groups = $items->groupBy(function (object $item): string {
            $slug = Str::slug($item->title) ?: 'projet-'.$item->id;

            return str_contains($slug, 'ravenala') ? 'ravenala' : $slug;
        });
        $position = 0;

        foreach ($groups as $slug => $translations) {
            $item = $translations->firstWhere('locale', 'fr') ?? $translations->first();
            $locale = $item->locale ?? 'fr';
            $meta = json_decode($item->meta ?? '[]', true) ?: [];
            $en = $locale === 'fr' ? $translations->firstWhere('locale', 'en') : null;
            preg_match_all('/\d{4}/', (string) ($meta['period'] ?? ''), $years);

            if (DB::table('projects')->where('slug', $slug)->exists()) {
                continue;
            }

            DB::table('projects')->insert([
                'slug' => $slug,
                'title' => json_encode(array_filter(array_filter([$locale => $item->title]) + array_filter(['en' => $en?->title]))),
                'acronym' => null,
                'summary' => json_encode(array_filter(array_filter([$locale => $item->description]) + array_filter(['en' => $en?->description]))) ?: null,
                'cover_image' => $item->image ?: null,
                'progress' => ($meta['badge_class'] ?? '') === 'badge-new' ? 'upcoming' : 'ongoing',
                'team' => $meta['ead'] ?? null,
                'funder' => trim((string) preg_replace('/^(financement|funding)\s*:?\s*/i', '', (string) ($meta['funding'] ?? ''))) ?: null,
                'leader' => trim((string) preg_replace('/^(chef de projet|project leader)\s*:?\s*/i', '', (string) ($meta['project_leader'] ?? ''))) ?: null,
                'start_year' => $years[0][0] ?? null,
                'end_year' => $years[0][1] ?? null,
                'external_url' => $item->link_url && $item->link_url !== '#' && ! str_contains($item->link_url, '/projets/ravenala') ? $item->link_url : null,
                'details' => null,
                'status' => $item->is_active ? 'published' : 'draft',
                'is_featured' => false,
                'position' => $position++,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

        }

        $ravenalaDefaults = [
            'slug' => 'ravenala',
            'title' => json_encode(['fr' => 'Projet RAVENALA', 'en' => 'RAVENALA project']),
            'acronym' => 'RAVENALA',
            'subtitle' => json_encode(['fr' => 'Réseau Académique pour la Valorisation des Enseignants, des Nouvelles Approches et de l’Apprentissage actif']),
            'summary' => json_encode([
                'fr' => 'Le projet renforce les capacités des établissements d’enseignement supérieur en matière de pédagogie universitaire à Madagascar et aux Comores, au service d’un apprentissage actif, inclusif et durable.',
                'en' => 'The project strengthens higher education institutions’ capacity in university teaching in Madagascar and the Comoros, for active, inclusive and sustainable learning.',
            ]),
            'body' => json_encode(['fr' => '<p>RAVENALA rassemble des universités partenaires autour d’un objectif commun : améliorer durablement les pratiques pédagogiques dans l’enseignement supérieur, en favorisant l’innovation, l’inclusion et l’usage raisonné du numérique.</p>'
                .'<p>Pour l’Université de Mahajanga et l’École Doctorale EDEN, le projet représente une opportunité de renforcer les compétences des enseignants, d’accompagner la transformation des pratiques et de contribuer à la diffusion de ressources pédagogiques adaptées aux réalités locales.</p>'
                .'<h3>Rôle de l’UMG et de l’EDEN</h3>'
                .'<p>L’Université de Mahajanga participe à la formation, à la pédagogie universitaire, à la transformation numérique pédagogique et à la dissémination des résultats. L’EDEN apporte son ancrage académique et scientifique pour valoriser les ressources produites, accompagner les enseignants et renforcer les pratiques d’apprentissage actif au sein de la communauté universitaire.</p>']),
            'cover_image' => '/images-projet/1771294651732.jpeg',
            'logo' => '/images-projet/ID-R-C.png',
            'progress' => 'ongoing',
            'team' => null,
            'funder' => 'Union européenne (Erasmus+)',
            'leader' => 'Pr RANARIJAONA Hery Lisy Tiana',
            'start_year' => null,
            'end_year' => null,
            'external_url' => null,
            'details' => json_encode([
                'objectives' => [
                    'Renforcer les compétences pédagogiques des enseignants universitaires.',
                    'Soutenir la transformation positive des pratiques pédagogiques.',
                    'Favoriser l’émergence de référents pédagogiques qualifiés.',
                    'Promouvoir l’éducation inclusive et l’égalité femmes-hommes.',
                    'Optimiser l’usage des pratiques pédagogiques et numériques.',
                    'Diffuser les résultats du projet et sensibiliser la communauté universitaire.',
                ],
                'beneficiaries' => [
                    'Enseignants et personnels de l’Université d’Antsiranana',
                    'Enseignants et personnels de l’Université de Mahajanga',
                    'Enseignants et personnels de l’Université des Comores',
                ],
                'partners' => [
                    ['name' => 'Université d’Angers', 'country' => 'France', 'role' => 'Chef de file du projet', 'logo' => '/images-projet/ua-nb.png'],
                    ['name' => 'Université Catholique de Louvain', 'country' => 'Belgique', 'role' => 'Partenaire académique', 'logo' => '/images-projet/ucl.jpeg'],
                    ['name' => 'Université d’Antsiranana', 'country' => 'Madagascar', 'role' => 'Université bénéficiaire', 'logo' => '/images-projet/logo-una-NB.png'],
                    ['name' => 'Université de Mahajanga', 'country' => 'Madagascar', 'role' => 'Université bénéficiaire', 'logo' => '/images-projet/umgs.jpeg'],
                    ['name' => 'Université des Comores', 'country' => 'Comores', 'role' => 'Université bénéficiaire', 'logo' => '/images-projet/unive-comores-NB.jpg'],
                ],
                'work_packages' => [
                    ['code' => 'WP1', 'title' => 'Gestion, coordination et suivi du projet', 'description' => 'Coordination opérationnelle, suivi méthodologique et reporting régulier pour garantir le bon déroulement du projet.', 'lead' => 'Pilote : UA, co-pilote : UNA'],
                    ['code' => 'WP2', 'title' => 'Partage et co-construction des ressources pédagogiques', 'description' => 'Mise en commun de ressources, guides et outils pour renforcer les pratiques pédagogiques universitaires.', 'lead' => 'Pilote : UA, co-pilote : UCLouvain'],
                    ['code' => 'WP3', 'title' => 'Formation de futurs référents pédagogiques', 'description' => 'Formation et accompagnement d’enseignants capables de soutenir leurs pairs dans les établissements partenaires.', 'lead' => 'Pilote : UCLouvain, co-pilote : UMG'],
                    ['code' => 'WP4', 'title' => 'Sensibilisations et formations pour les enseignants', 'description' => 'Formations en présentiel, accompagnements à distance et sensibilisations à l’égalité dans l’enseignement supérieur.', 'lead' => 'Pilote : UNA, co-pilote : UDC'],
                    ['code' => 'WP5', 'title' => 'Communication et pérennisation', 'description' => 'Diffusion des résultats, valorisation des acquis et pérennisation des pratiques après le projet.', 'lead' => 'Pilote : UDC, co-pilote : UMG'],
                ],
                'facts' => [
                    ['label' => 'Partenaires', 'value' => '5'],
                    ['label' => 'Universités bénéficiaires', 'value' => '3'],
                    ['label' => 'Lots de travail', 'value' => '5'],
                ],
                'funding_notice' => 'Financé par l’Union européenne. Les points de vue et avis exprimés n’engagent toutefois que leur(s) auteur(s) et ne reflètent pas nécessairement ceux de l’Union européenne ou de l’Agence exécutive européenne pour l’éducation et la culture (EACEA). Ni l’Union européenne ni l’EACEA ne sauraient en être tenues pour responsables.',
                'funding_logo' => '/images-projet/EN_Co-fundedbytheEU_RGB_BLACK.jpg',
                'gallery_image' => '/images-projet/images.jpeg',
            ]),
            'status' => 'published',
            'is_featured' => true,
            'position' => $position,
            'created_at' => $now,
            'updated_at' => $now,
        ];
        $existing = DB::table('projects')->where('slug', 'ravenala')->first();

        if ($existing) {
            // Enrichit la fiche existante sans remplacer les textes, images ou statuts édités.
            $missing = array_filter($ravenalaDefaults, fn ($value, $key) => $existing->{$key} === null, ARRAY_FILTER_USE_BOTH);
            $titles = json_decode($ravenalaDefaults['title'], true);
            $titles = array_replace($titles, json_decode($existing->title, true) ?: []);
            DB::table('projects')->where('id', $existing->id)->update([...$missing, 'title' => json_encode($titles)]);
        } else {
            DB::table('projects')->insert($ravenalaDefaults);
        }
    }

    public function down(): void
    {
        DB::table('projects')->truncate();
    }
};
