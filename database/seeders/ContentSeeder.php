<?php

namespace Database\Seeders;

use App\Models\ContentBlock;
use App\Models\ContentItem;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedBlocks();
        $this->seedItems();
    }

    private function seedBlocks(): void
    {
        $blocks = [
            ['key' => 'site.name', 'group' => 'site', 'value' => 'EDEN-UMG'],
            ['key' => 'site.caption', 'group' => 'site', 'value' => 'Université de Mahajanga'],

            ['key' => 'hero.eyebrow', 'group' => 'hero', 'value' => 'École Doctorale — Université de Mahajanga'],
            ['key' => 'hero.title', 'group' => 'hero', 'value' => 'Former les Chercheurs de Demain au Cœur des'],
            ['key' => 'hero.title_highlight', 'group' => 'hero', 'value' => 'Écosystèmes Naturels'],
            ['key' => 'hero.subtitle', 'group' => 'hero', 'type' => 'textarea', 'value' => "L'EDEN prépare des docteurs de haut niveau engagés dans la recherche scientifique, la conservation de la biodiversité unique de Madagascar et le développement durable de la région."],
            ['key' => 'hero.button_primary_label', 'group' => 'hero', 'value' => "Découvrir l'EDEN"],
            ['key' => 'hero.button_primary_url', 'group' => 'hero', 'type' => 'link', 'value' => '#about'],
            ['key' => 'hero.button_secondary_label', 'group' => 'hero', 'value' => 'Candidater'],
            ['key' => 'hero.button_secondary_url', 'group' => 'hero', 'type' => 'link', 'value' => '#formation'],
            ['key' => 'hero.image_icon', 'group' => 'hero', 'value' => '🌿'],
            ['key' => 'hero.image_label', 'group' => 'hero', 'value' => 'Photo de terrain ou de recherche'],
            ['key' => 'hero.image_caption', 'group' => 'hero', 'value' => 'Région Boeny — Madagascar'],
            ['key' => 'hero.image_meta', 'group' => 'hero', 'value' => 'Terrain de recherche, Université de Mahajanga'],
            ['key' => 'hero.image_tag', 'group' => 'hero', 'value' => 'Biodiversité · Recherche'],

            ['key' => 'about.eyebrow', 'group' => 'about', 'value' => 'À propos'],
            ['key' => 'about.title', 'group' => 'about', 'value' => "L'École Doctorale"],
            ['key' => 'about.title_second_line', 'group' => 'about', 'value' => 'Écosystèmes Naturels'],
            ['key' => 'about.description_1', 'group' => 'about', 'type' => 'textarea', 'value' => "Fondée en 2018, l'EDEN est une composante scientifique de l'Université de Mahajanga dédiée à la formation avancée en sciences de l'environnement et à la recherche sur les écosystèmes de Madagascar."],
            ['key' => 'about.description_2', 'group' => 'about', 'type' => 'textarea', 'value' => "Implantée dans la région Boeny, au cœur d'une biodiversité exceptionnelle, EDEN bénéficie d'un cadre privilégié pour l'étude des écosystèmes terrestres, aquatiques et marins du nord-ouest malgache."],
            ['key' => 'about.button_label', 'group' => 'about', 'value' => 'Accéder à la formation'],
            ['key' => 'about.button_url', 'group' => 'about', 'type' => 'link', 'value' => '#formation'],

            ['key' => 'stats.doctorants', 'group' => 'stats', 'type' => 'number', 'value' => '52'],
            ['key' => 'stats.doctorants_suffix', 'group' => 'stats', 'value' => '+'],
            ['key' => 'stats.doctorants_label', 'group' => 'stats', 'value' => 'Doctorants actifs'],
            ['key' => 'stats.theses', 'group' => 'stats', 'type' => 'number', 'value' => '31'],
            ['key' => 'stats.theses_label', 'group' => 'stats', 'value' => 'Thèses soutenues'],
            ['key' => 'stats.partnerships', 'group' => 'stats', 'type' => 'number', 'value' => '14'],
            ['key' => 'stats.partnerships_label', 'group' => 'stats', 'value' => 'Partenariats actifs'],
            ['key' => 'stats.publications', 'group' => 'stats', 'type' => 'number', 'value' => '78'],
            ['key' => 'stats.publications_label', 'group' => 'stats', 'value' => 'Publications scientifiques'],
            ['key' => 'stats.eyebrow', 'group' => 'stats', 'value' => 'EDEN en chiffres'],
            ['key' => 'stats.title', 'group' => 'stats', 'value' => "L'École Doctorale en Données"],

            ['key' => 'mission.eyebrow', 'group' => 'mission', 'value' => 'Nos fondements'],
            ['key' => 'mission.title', 'group' => 'mission', 'value' => 'Mission, Vision & Objectifs'],

            ['key' => 'direction.quote', 'group' => 'direction', 'type' => 'textarea', 'value' => "« L'École Doctorale EDEN incarne notre engagement envers l'excellence scientifique et la préservation du patrimoine naturel de Madagascar. Nos docteurs sont les gardiens de demain de notre biodiversité unique — une responsabilité que nous assumons avec fierté et conviction. »"],
            ['key' => 'direction.name', 'group' => 'direction', 'value' => 'Prof. [Nom du Directeur]'],
            ['key' => 'direction.title', 'group' => 'direction', 'value' => "Directeur de l'École Doctorale EDEN-UMG"],
            ['key' => 'direction.photo_icon', 'group' => 'direction', 'value' => '👤'],
            ['key' => 'direction.photo_label', 'group' => 'direction', 'value' => 'Portrait du Directeur'],
            ['key' => 'direction.photo_caption', 'group' => 'direction', 'value' => 'Photo officielle'],

            ['key' => 'organisation.eyebrow', 'group' => 'organisation', 'value' => 'Organisation'],
            ['key' => 'organisation.title', 'group' => 'organisation', 'value' => "Équipes d'Accueil Doctoral"],
            ['key' => 'organisation.description', 'group' => 'organisation', 'type' => 'textarea', 'value' => "L'EDEN est structurée autour de deux Équipes d'Accueil Doctoral (EAD) regroupant chercheurs et doctorants autour de thématiques scientifiques complémentaires."],

            ['key' => 'formation.eyebrow', 'group' => 'formation', 'value' => 'Formation'],
            ['key' => 'formation.title', 'group' => 'formation', 'value' => 'Parcours Doctoral'],
            ['key' => 'formation.description', 'group' => 'formation', 'type' => 'textarea', 'value' => "L'EDEN offre une formation de 3 ans alliant excellence scientifique, encadrement personnalisé et développement des compétences du chercheur."],
            ['key' => 'formation.conditions_title', 'group' => 'formation', 'value' => "Conditions d'Admission"],
            ['key' => 'formation.path_title', 'group' => 'formation', 'value' => 'Parcours en 3 Ans'],
            ['key' => 'formation.units_title', 'group' => 'formation', 'value' => 'Unités de Formation'],

            ['key' => 'recherche.eyebrow', 'group' => 'recherche', 'value' => 'Recherche'],
            ['key' => 'recherche.title', 'group' => 'recherche', 'value' => 'Thématiques de Recherche'],
            ['key' => 'recherche.description', 'group' => 'recherche', 'type' => 'textarea', 'value' => "Les activités de l'EDEN couvrent un large spectre, des forêts tropicales aux récifs coralliens, en passant par les zones humides et les agroécosystèmes malgaches."],
            ['key' => 'recherche.tab_ead_1', 'group' => 'recherche', 'value' => 'EAD 1 — Écosystèmes Terrestres'],
            ['key' => 'recherche.tab_ead_2', 'group' => 'recherche', 'value' => 'EAD 2 — Aquatique & Marin'],

            ['key' => 'partenaires.eyebrow', 'group' => 'partenaires', 'value' => 'Partenariats'],
            ['key' => 'partenaires.title', 'group' => 'partenaires', 'value' => 'Nos Partenaires'],
            ['key' => 'partenaires.description', 'group' => 'partenaires', 'type' => 'textarea', 'value' => "L'EDEN développe des collaborations avec des organismes nationaux et internationaux de premier plan en matière de recherche environnementale et de conservation."],
            ['key' => 'partenaires.national_label', 'group' => 'partenaires', 'value' => 'Partenaires nationaux'],
            ['key' => 'partenaires.international_label', 'group' => 'partenaires', 'value' => 'Partenaires internationaux'],

            ['key' => 'projets.eyebrow', 'group' => 'projets', 'value' => 'Projets'],
            ['key' => 'projets.title', 'group' => 'projets', 'value' => 'Projets de Recherche en Cours'],
            ['key' => 'projets.description', 'group' => 'projets', 'type' => 'textarea', 'value' => "L'EDEN pilote et co-pilote plusieurs projets d'envergure financés par des institutions nationales et internationales."],

            ['key' => 'contact.eyebrow', 'group' => 'contact', 'value' => 'Contact'],
            ['key' => 'contact.title', 'group' => 'contact', 'value' => 'Nous Contacter'],
            ['key' => 'contact.description', 'group' => 'contact', 'type' => 'textarea', 'value' => 'Pour toute question relative aux admissions, à la recherche ou aux partenariats, notre équipe est à votre disposition.'],
            ['key' => 'contact.address_label', 'group' => 'contact', 'value' => 'Adresse'],
            ['key' => 'contact.address', 'group' => 'contact', 'type' => 'textarea', 'value' => "École Doctorale EDEN — Université de Mahajanga\nBP 652, Mahajanga 401, Madagascar"],
            ['key' => 'contact.phone_label', 'group' => 'contact', 'value' => 'Téléphone'],
            ['key' => 'contact.phone', 'group' => 'contact', 'value' => '+261 20 62 XXX XX'],
            ['key' => 'contact.email_label', 'group' => 'contact', 'value' => 'Email'],
            ['key' => 'contact.email', 'group' => 'contact', 'value' => 'contact@eden.univ-mahajanga.mg'],
            ['key' => 'contact.website_label', 'group' => 'contact', 'value' => "Site Web de l'Université"],
            ['key' => 'contact.website', 'group' => 'contact', 'value' => 'www.univ-mahajanga.mg'],
            ['key' => 'contact.map_icon', 'group' => 'contact', 'value' => '🗺️'],
            ['key' => 'contact.map_label', 'group' => 'contact', 'value' => 'Carte de localisation'],
            ['key' => 'contact.map_caption', 'group' => 'contact', 'value' => 'Université de Mahajanga — Madagascar'],

            ['key' => 'footer.description', 'group' => 'footer', 'type' => 'textarea', 'value' => 'École Doctorale Écosystèmes Naturels — Former les chercheurs de demain pour la préservation des écosystèmes malgaches et le développement durable.'],
            ['key' => 'footer.copyright', 'group' => 'footer', 'value' => '© 2025 EDEN-UMG — École Doctorale Écosystèmes Naturels, Université de Mahajanga. Tous droits réservés.'],
            ['key' => 'footer.credit', 'group' => 'footer', 'value' => "Conçu pour l'excellence académique"],
        ];

        foreach ($blocks as $position => $block) {
            ContentBlock::updateOrCreate(
                ['key' => $block['key']],
                [
                    'type' => $block['type'] ?? 'text',
                    'value' => $block['value'],
                    'meta' => $block['meta'] ?? null,
                    'group' => $block['group'] ?? null,
                    'position' => $position,
                    'is_active' => true,
                ],
            );
        }
    }

    private function seedItems(): void
    {
        $collections = [
            'mission_cards' => [
                ['subtitle' => 'Mission', 'title' => 'Former pour la Science', 'description' => 'Former des chercheurs de haut niveau capables de contribuer au développement durable et à la conservation des écosystèmes naturels malgaches, dans une démarche rigoureuse et éthique.'],
                ['subtitle' => 'Vision', 'title' => "Pôle d'Excellence Régional", 'description' => "Devenir un centre d'excellence régional et international en sciences des écosystèmes, reconnu pour la qualité de ses doctorants et l'impact durable de ses recherches sur l'environnement malgache."],
                ['subtitle' => 'Objectifs', 'title' => 'Science & Conservation', 'description' => 'Promouvoir la recherche scientifique, renforcer les partenariats nationaux et internationaux, et contribuer activement aux politiques de conservation de la biodiversité à Madagascar.'],
            ],
            'ead_teams' => [
                [
                    'title' => 'Biodiversité et Écosystèmes Terrestres',
                    'subtitle' => 'Responsable : Dr. [Nom du Responsable EAD 1]',
                    'meta' => [
                        'number' => 'EAD 1',
                        'themes' => [
                            'Diversité floristique et fonctionnelle des forêts malgaches',
                            'Écologie des espèces endémiques et faune sauvage',
                            'Conservation des ressources génétiques végétales',
                            'Agroécologie et systèmes agricoles durables',
                            'Restauration des écosystèmes forestiers dégradés',
                        ],
                    ],
                ],
                [
                    'title' => 'Écosystèmes Aquatiques et Marins',
                    'subtitle' => 'Responsable : Dr. [Nom du Responsable EAD 2]',
                    'meta' => [
                        'number' => 'EAD 2',
                        'themes' => [
                            'Biodiversité et santé des récifs coralliens',
                            'Dynamique et conservation des mangroves',
                            'Ressources halieutiques et pêche durable',
                            'Qualité des eaux et écosystèmes lacustres',
                            'Ichtyologie et biologie marine côtière',
                        ],
                    ],
                ],
            ],
            'formation_conditions' => [
                ['title' => "Être titulaire d'un Master 2 ou diplôme équivalent"],
                ['title' => "Disposer d'un projet de recherche rédigé et validé"],
                ['title' => 'Avoir un directeur de thèse habilité (HDR)'],
                ['title' => "Réussir l'entretien devant le Conseil de l'École Doctorale"],
                ['title' => "Obtenir l'inscription administrative à l'Université de Mahajanga"],
            ],
            'formation_steps' => [
                ['subtitle' => 'Année 1', 'title' => 'Initiation à la Recherche', 'description' => 'Séminaires, revue bibliographique, protocole expérimental', 'meta' => ['number' => '1']],
                ['subtitle' => 'Année 2', 'title' => 'Recherche Active', 'description' => 'Terrains, analyses, publications, conférences', 'meta' => ['number' => '2']],
                ['subtitle' => 'Année 3', 'title' => 'Rédaction & Soutenance', 'description' => 'Rédaction de thèse, pré-soutenance, soutenance publique', 'meta' => ['number' => '3']],
            ],
            'formation_units' => [
                ['title' => 'Méthodologie de la recherche scientifique'],
                ['title' => 'Biostatistiques et analyse de données'],
                ['title' => 'Rédaction et publication scientifique'],
                ['title' => 'Éthique et déontologie de la recherche'],
                ['title' => 'Valorisation et transfert de connaissances'],
            ],
            'research_themes_ead_1' => [
                ['title' => 'Conservation de la Biodiversité Végétale', 'description' => 'Inventaires floristiques, cartographie et évaluation du statut de conservation des plantes endémiques malgaches de la région Boeny.'],
                ['title' => 'Écologie des Lémuriens et Faune Endémique', 'description' => "Comportement, écologie alimentaire et conservation des lémuriens et autres espèces endémiques de la zone d'influence de l'UMG."],
                ['title' => 'Restauration des Écosystèmes Dégradés', 'description' => 'Techniques de restauration écologique, suivi de la régénération naturelle et programmes de reboisement communautaire.'],
                ['title' => 'Agroforesterie et Systèmes Durables', 'description' => 'Développement de modèles agroforestiers adaptés aux conditions écologiques et socio-économiques du nord-ouest de Madagascar.'],
            ],
            'research_themes_ead_2' => [
                ['title' => 'Suivi des Récifs Coralliens', 'description' => "Monitoring de l'état de santé des récifs du Canal de Mozambique : couverture corallienne, biodiversité associée, blanchissement thermique."],
                ['title' => 'Mangroves et Zones Humides', 'description' => 'Cartographie, dynamique temporelle et services écosystémiques des mangroves de la côte nord-ouest de Madagascar.'],
                ['title' => 'Ressources Halieutiques Durables', 'description' => 'Évaluation des stocks, pratiques de pêche artisanale et gestion intégrée des ressources marines côtières.'],
                ['title' => 'Limnologie et Eaux Douces', 'description' => "Qualité physicochimique et biologique des eaux, biodiversité aquatique des lacs et cours d'eau de la région Boeny."],
            ],
            'partners_national' => [
                ['title' => "Université d'Antananarivo"],
                ['title' => 'Université de Toliara'],
                ['title' => 'CNRE Madagascar'],
                ['title' => 'IRD Madagascar'],
                ['title' => 'WWF Madagascar'],
                ['title' => 'Madagascar National Parks'],
                ['title' => 'WCS Madagascar'],
            ],
            'partners_international' => [
                ['title' => 'Université de La Réunion'],
                ['title' => 'MNHN Paris'],
                ['title' => 'Université de Bordeaux'],
                ['title' => 'CIRAD'],
                ['title' => 'Agence Française de Développement'],
                ['title' => 'GEF — Fonds Mondial Environnement'],
                ['title' => 'IUCN'],
                ['title' => 'Union Européenne'],
            ],
            'projects' => [
                [
                    'title' => 'BIODIVERS-BOENY',
                    'subtitle' => 'En cours',
                    'description' => "Conservation et valorisation des espèces endémiques de la Région Boeny : inventaires, suivis fauniques et réseau d'aires protégées communautaires.",
                    'meta' => ['icon' => '🌿', 'image_label' => 'Terrain — forêt endémique Boeny', 'ead' => 'EAD 1', 'funding' => 'Financement : AFD', 'period' => '2023 – 2026', 'badge_class' => 'badge-active'],
                ],
                [
                    'title' => 'CORAL-MOZAMBIQUE',
                    'subtitle' => 'En cours',
                    'description' => "Monitoring continu et restauration des récifs coralliens du Canal de Mozambique face au blanchissement thermique et à l'acidification des océans.",
                    'meta' => ['icon' => '🐠', 'image_label' => 'Terrain — récif corallien', 'ead' => 'EAD 2', 'funding' => 'Financement : Union Européenne', 'period' => '2022 – 2025', 'badge_class' => 'badge-active'],
                ],
                [
                    'title' => 'CLIMATE-COAST',
                    'subtitle' => 'Démarrage',
                    'description' => 'Évaluation et stratégies d\'adaptation des écosystèmes côtiers et terrestres de la région Boeny face aux impacts du changement climatique.',
                    'meta' => ['icon' => '🌊', 'image_label' => 'Terrain — mangroves et zone côtière', 'ead' => 'EAD 1 & 2', 'funding' => 'Financement : GEF', 'period' => '2025 – 2028', 'badge_class' => 'badge-new'],
                ],
            ],
            'footer_navigation' => [
                ['title' => 'À propos', 'link_url' => '#about'],
                ['title' => 'Mission & Vision', 'link_url' => '#mission'],
                ['title' => 'Organisation', 'link_url' => '#organisation'],
                ['title' => 'Formation', 'link_url' => '#formation'],
            ],
            'footer_research' => [
                ['title' => 'Thématiques EAD 1', 'link_url' => '#recherche'],
                ['title' => 'Thématiques EAD 2', 'link_url' => '#recherche'],
                ['title' => 'Projets en cours', 'link_url' => '#projets'],
                ['title' => 'Partenariats', 'link_url' => '#partenaires'],
            ],
            'footer_information' => [
                ['title' => 'Nous contacter', 'link_url' => '#contact'],
                ['title' => 'Actualités', 'link_url' => '#'],
                ['title' => 'Bibliothèque numérique', 'link_url' => '#'],
                ['title' => 'Espace doctorant', 'link_url' => '#'],
            ],
        ];

        foreach ($collections as $collectionKey => $items) {
            foreach ($items as $position => $item) {
                ContentItem::updateOrCreate(
                    [
                        'collection_key' => $collectionKey,
                        'position' => $position,
                    ],
                    [
                        'title' => $item['title'] ?? null,
                        'subtitle' => $item['subtitle'] ?? null,
                        'description' => $item['description'] ?? null,
                        'image' => $item['image'] ?? null,
                        'link_url' => $item['link_url'] ?? null,
                        'link_label' => $item['link_label'] ?? null,
                        'meta' => $item['meta'] ?? null,
                        'is_active' => true,
                    ],
                );
            }
        }
    }
}
