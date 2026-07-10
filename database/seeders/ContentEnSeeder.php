<?php

namespace Database\Seeders;

use App\Models\ContentBlock;
use App\Models\ContentItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ContentEnSeeder extends Seeder
{
    private const LOCALE = 'en';

    private const TEXTAREA_KEYS = [
        'hero.subtitle', 'about.description_1', 'about.description_2', 'direction.quote',
        'organisation.description', 'formation.description', 'recherche.description',
        'partenaires.description', 'projets.description', 'contact.description',
        'contact.address', 'footer.description', 'popup.message', 'seo.description',
    ];

    public function run(): void
    {
        $this->seedBlocks();
        $this->seedItems();
    }

    private function seedBlocks(): void
    {
        $blocks = [
            'nav.about_label' => 'About',
            'nav.organisation_label' => 'Organisation',
            'nav.formation_label' => 'Training',
            'nav.recherche_label' => 'Research',
            'nav.projets_label' => 'Projects',
            'nav.contact_label' => 'Contact',
            'nav.cta_label' => 'Thesis Bank',

            'hero.eyebrow' => 'Doctoral School — University of Mahajanga',
            'hero.title' => "Training Tomorrow's Researchers at the Heart of",
            'hero.title_highlight' => 'Natural Ecosystems',
            'hero.subtitle' => "EDEN trains high-level doctors committed to scientific research, the conservation of Madagascar's unique biodiversity and the sustainable development of the region.",
            'hero.button_primary_label' => 'Discover EDEN',
            'hero.button_secondary_label' => 'Apply',
            'hero.image_label' => 'Field or research photo',
            'hero.image_caption' => 'Boeny Region — Madagascar',
            'hero.image_meta' => 'Research field, University of Mahajanga',
            'hero.image_tag' => 'Biodiversity · Research',

            'about.eyebrow' => 'About',
            'about.title' => 'The Doctoral School',
            'about.title_second_line' => 'Natural Ecosystems',
            'about.description_1' => "Founded in 2018, EDEN is a scientific component of the University of Mahajanga dedicated to advanced training in environmental sciences and research on Madagascar's ecosystems.",
            'about.description_2' => 'Located in the Boeny region, at the heart of exceptional biodiversity, EDEN enjoys a privileged setting for studying the terrestrial, aquatic and marine ecosystems of north-western Madagascar.',
            'about.button_label' => 'Go to training',

            'stats.doctorants_label' => 'Active doctoral students',
            'stats.theses_label' => 'Theses defended',
            'stats.partnerships_label' => 'Active partnerships',
            'stats.publications_label' => 'Scientific publications',
            'stats.eyebrow' => 'EDEN in figures',
            'stats.title' => 'The Doctoral School in Data',

            'mission.eyebrow' => 'Our foundations',
            'mission.title' => 'Mission, Vision & Objectives',

            'direction.quote' => '« The EDEN Doctoral School embodies our commitment to scientific excellence and the preservation of Madagascar\'s natural heritage. Our doctors are tomorrow\'s guardians of our unique biodiversity — a responsibility we embrace with pride and conviction. »',
            'direction.title' => 'Director of the EDEN-UMG Doctoral School',
            'direction.photo_label' => "Director's portrait",
            'direction.photo_caption' => 'Official photo',

            'organisation.eyebrow' => 'Organisation',
            'organisation.title' => 'Doctoral Research Teams',
            'organisation.description' => 'EDEN is structured around two Doctoral Research Teams (EAD) bringing together researchers and doctoral students around complementary scientific themes.',

            'formation.eyebrow' => 'Training',
            'formation.title' => 'Doctoral Programme',
            'formation.description' => 'EDEN offers a 3-year programme combining scientific excellence, personalised supervision and the development of researcher skills.',
            'formation.conditions_title' => 'Admission Requirements',
            'formation.path_title' => '3-Year Programme',
            'formation.units_title' => 'Training Units',

            'recherche.eyebrow' => 'Research',
            'recherche.title' => 'Research Themes',
            'recherche.description' => "EDEN's activities cover a broad spectrum, from tropical forests to coral reefs, including wetlands and Malagasy agroecosystems.",
            'recherche.tab_ead_1' => 'EAD 1 — Terrestrial Ecosystems',
            'recherche.tab_ead_2' => 'EAD 2 — Aquatic & Marine',

            'partenaires.eyebrow' => 'Partnerships',
            'partenaires.title' => 'Our Partners',
            'partenaires.description' => 'EDEN develops collaborations with leading national and international organisations in environmental research and conservation.',
            'partenaires.national_label' => 'National partners',
            'partenaires.international_label' => 'International partners',

            'projets.eyebrow' => 'Projects',
            'projets.title' => 'Ongoing Research Projects',
            'projets.description' => 'EDEN leads and co-leads several large-scale projects funded by national and international institutions.',

            'contact.eyebrow' => 'Contact',
            'contact.title' => 'Contact Us',
            'contact.description' => 'For any question regarding admissions, research or partnerships, our team is at your disposal.',
            'contact.address_label' => 'Address',
            'contact.address' => "EDEN Doctoral School — University of Mahajanga\nBP 652, Mahajanga 401, Madagascar",
            'contact.phone_label' => 'Phone',
            'contact.email_label' => 'Email',
            'contact.website_label' => 'University Website',
            'contact.map_label' => 'Location map',
            'contact.map_caption' => 'University of Mahajanga — Madagascar',

            'footer.description' => "Doctoral School of Natural Ecosystems — Training tomorrow's researchers for the preservation of Malagasy ecosystems and sustainable development.",
            'footer.navigation_title' => 'Navigation',
            'footer.research_title' => 'Research',
            'footer.information_title' => 'Information',
            'footer.copyright' => '© 2025 EDEN-UMG — Doctoral School of Natural Ecosystems, University of Mahajanga. All rights reserved.',
            'footer.credit' => 'Designed for academic excellence',

            'popup.title' => 'Important announcement',
            'popup.message' => 'New information is available for visitors of the EDEN Doctoral School.',
            'popup.button_label' => 'Learn more',

            'seo.title' => 'EDEN-UMG — Doctoral School of Natural Ecosystems',
            'seo.description' => "Doctoral School of Natural Ecosystems (EDEN) at the University of Mahajanga: doctoral training and research on Madagascar's biodiversity — research teams, doctoral programme, themes, projects and partnerships.",
            'seo.keywords' => 'EDEN, Doctoral School, University of Mahajanga, UMG, Natural Ecosystems, PhD Madagascar, biodiversity research, thesis, Boeny, doctoral training, ecology, conservation',
        ];

        $position = 0;

        foreach ($blocks as $key => $value) {
            ContentBlock::updateOrCreate(
                ['key' => $key, 'locale' => self::LOCALE],
                [
                    'type' => in_array($key, self::TEXTAREA_KEYS, true) ? 'textarea' : 'text',
                    'value' => $value,
                    'group' => Str::before($key, '.'),
                    'position' => $position++,
                    'is_active' => true,
                ],
            );
        }
    }

    private function seedItems(): void
    {
        $collections = [
            'mission_cards' => [
                ['subtitle' => 'Mission', 'title' => 'Training for Science', 'description' => 'Train high-level researchers able to contribute to sustainable development and the conservation of Malagasy natural ecosystems, through a rigorous and ethical approach.'],
                ['subtitle' => 'Vision', 'title' => 'Regional Centre of Excellence', 'description' => 'Become a regional and international centre of excellence in ecosystem sciences, recognised for the quality of its doctoral students and the lasting impact of its research on the Malagasy environment.'],
                ['subtitle' => 'Objectives', 'title' => 'Science & Conservation', 'description' => 'Promote scientific research, strengthen national and international partnerships, and actively contribute to biodiversity conservation policies in Madagascar.'],
            ],
            'ead_teams' => [
                [
                    'title' => 'Biodiversity and Terrestrial Ecosystems',
                    'subtitle' => 'Head: Dr. [EAD 1 Lead Name]',
                    'meta' => [
                        'number' => 'EAD 1',
                        'themes' => [
                            'Floristic and functional diversity of Malagasy forests',
                            'Ecology of endemic species and wildlife',
                            'Conservation of plant genetic resources',
                            'Agroecology and sustainable farming systems',
                            'Restoration of degraded forest ecosystems',
                        ],
                    ],
                ],
                [
                    'title' => 'Aquatic and Marine Ecosystems',
                    'subtitle' => 'Head: Dr. [EAD 2 Lead Name]',
                    'meta' => [
                        'number' => 'EAD 2',
                        'themes' => [
                            'Biodiversity and health of coral reefs',
                            'Dynamics and conservation of mangroves',
                            'Fishery resources and sustainable fishing',
                            'Water quality and lake ecosystems',
                            'Ichthyology and coastal marine biology',
                        ],
                    ],
                ],
            ],
            'formation_conditions' => [
                ['title' => 'Hold a Master 2 degree or equivalent'],
                ['title' => 'Have a written and approved research project'],
                ['title' => 'Have an accredited thesis supervisor (HDR)'],
                ['title' => 'Pass the interview before the Doctoral School Council'],
                ['title' => 'Complete administrative enrolment at the University of Mahajanga'],
            ],
            'formation_steps' => [
                ['subtitle' => 'Year 1', 'title' => 'Introduction to Research', 'description' => 'Seminars, literature review, experimental protocol', 'meta' => ['number' => '1']],
                ['subtitle' => 'Year 2', 'title' => 'Active Research', 'description' => 'Fieldwork, analyses, publications, conferences', 'meta' => ['number' => '2']],
                ['subtitle' => 'Year 3', 'title' => 'Writing & Defence', 'description' => 'Thesis writing, pre-defence, public defence', 'meta' => ['number' => '3']],
            ],
            'formation_units' => [
                ['title' => 'Scientific research methodology'],
                ['title' => 'Biostatistics and data analysis'],
                ['title' => 'Scientific writing and publication'],
                ['title' => 'Research ethics and integrity'],
                ['title' => 'Knowledge valorisation and transfer'],
            ],
            'research_themes_ead_1' => [
                ['title' => 'Plant Biodiversity Conservation', 'description' => 'Floristic inventories, mapping and conservation-status assessment of endemic Malagasy plants in the Boeny region.'],
                ['title' => 'Ecology of Lemurs and Endemic Fauna', 'description' => "Behaviour, feeding ecology and conservation of lemurs and other endemic species within UMG's area of influence."],
                ['title' => 'Restoration of Degraded Ecosystems', 'description' => 'Ecological restoration techniques, monitoring of natural regeneration and community reforestation programmes.'],
                ['title' => 'Agroforestry and Sustainable Systems', 'description' => 'Development of agroforestry models adapted to the ecological and socio-economic conditions of north-western Madagascar.'],
            ],
            'research_themes_ead_2' => [
                ['title' => 'Coral Reef Monitoring', 'description' => 'Monitoring the health of the Mozambique Channel reefs: coral cover, associated biodiversity, thermal bleaching.'],
                ['title' => 'Mangroves and Wetlands', 'description' => "Mapping, temporal dynamics and ecosystem services of the mangroves of north-western Madagascar's coast."],
                ['title' => 'Sustainable Fishery Resources', 'description' => 'Stock assessment, artisanal fishing practices and integrated management of coastal marine resources.'],
                ['title' => 'Limnology and Freshwater', 'description' => 'Physicochemical and biological water quality, aquatic biodiversity of the lakes and rivers of the Boeny region.'],
            ],
            'partners_national' => [
                ['title' => 'University of Antananarivo'],
                ['title' => 'University of Toliara'],
                ['title' => 'CNRE Madagascar'],
                ['title' => 'IRD Madagascar'],
                ['title' => 'WWF Madagascar'],
                ['title' => 'Madagascar National Parks'],
                ['title' => 'WCS Madagascar'],
            ],
            'partners_international' => [
                ['title' => 'University of La Réunion'],
                ['title' => 'MNHN Paris'],
                ['title' => 'University of Bordeaux'],
                ['title' => 'CIRAD'],
                ['title' => 'French Development Agency (AFD)'],
                ['title' => 'GEF — Global Environment Facility'],
                ['title' => 'IUCN'],
                ['title' => 'European Union'],
            ],
            'projects' => [
                [
                    'title' => 'BIODIVERS-BOENY',
                    'subtitle' => 'Ongoing',
                    'description' => 'Conservation and valorisation of endemic species in the Boeny Region: inventories, faunal monitoring and a network of community protected areas.',
                    'meta' => ['icon' => '🌿', 'image_label' => 'Field — Boeny endemic forest', 'ead' => 'EAD 1', 'funding' => 'Funding: AFD', 'project_leader' => 'Project leader: To be specified', 'period' => '2023 – 2026', 'badge_class' => 'badge-active'],
                ],
                [
                    'title' => 'CORAL-MOZAMBIQUE',
                    'subtitle' => 'Ongoing',
                    'description' => 'Continuous monitoring and restoration of the Mozambique Channel coral reefs facing thermal bleaching and ocean acidification.',
                    'meta' => ['icon' => '🐠', 'image_label' => 'Field — coral reef', 'ead' => 'EAD 2', 'funding' => 'Funding: European Union', 'project_leader' => 'Project leader: To be specified', 'period' => '2022 – 2025', 'badge_class' => 'badge-active'],
                ],
                [
                    'title' => 'CLIMATE-COAST',
                    'subtitle' => 'Starting',
                    'description' => 'Assessment and adaptation strategies for the coastal and terrestrial ecosystems of the Boeny region facing the impacts of climate change.',
                    'meta' => ['icon' => '🌊', 'image_label' => 'Field — mangroves and coastal area', 'ead' => 'EAD 1 & 2', 'funding' => 'Funding: GEF', 'project_leader' => 'Project leader: To be specified', 'period' => '2025 – 2028', 'badge_class' => 'badge-new'],
                ],
            ],
            'footer_navigation' => [
                ['title' => 'About', 'link_url' => '#about'],
                ['title' => 'Mission & Vision', 'link_url' => '#mission'],
                ['title' => 'Organisation', 'link_url' => '#organisation'],
                ['title' => 'Training', 'link_url' => '#formation'],
            ],
            'footer_research' => [
                ['title' => 'EAD 1 Themes', 'link_url' => '#recherche'],
                ['title' => 'EAD 2 Themes', 'link_url' => '#recherche'],
                ['title' => 'Ongoing projects', 'link_url' => '#projets'],
                ['title' => 'Partnerships', 'link_url' => '#partenaires'],
            ],
            'footer_information' => [
                ['title' => 'Contact us', 'link_url' => '#contact'],
                ['title' => 'News', 'link_url' => '#'],
                ['title' => 'Digital library', 'link_url' => '#'],
                ['title' => 'Doctoral student area', 'link_url' => '#'],
            ],
            'footer_socials' => [
                ['title' => 'Facebook', 'link_url' => '#'],
            ],
        ];

        foreach ($collections as $collectionKey => $items) {
            foreach ($items as $position => $item) {
                ContentItem::updateOrCreate(
                    [
                        'collection_key' => $collectionKey,
                        'locale' => self::LOCALE,
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
