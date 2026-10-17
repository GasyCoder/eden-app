<?php

namespace Database\Seeders;

use App\Models\Journal;
use App\Models\JournalIssue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * MaToY, le journal trimestriel de l'EDEN, et son numéro 01 (juin - août 2026).
 *
 * Les fichiers (PDF, couverture, photos) sont repris s'ils ont été déposés dans
 * storage/app/public/matoy/n01-2026/ ; sinon le numéro est créé sans médias et
 * peut être complété depuis /admin/journals.
 *
 *   php artisan db:seed --class=MatoySeeder
 */
class MatoySeeder extends Seeder
{
    private const DIR = 'matoy/n01-2026';

    public function run(): void
    {
        $journal = Journal::query()->updateOrCreate(['slug' => 'matoy'], [
            'title' => ['fr' => 'MaToY', 'en' => 'MaToY'],
            'description' => [
                'fr' => '<p><strong>MaToY</strong> est le journal trimestriel de l’École Doctorale Écosystèmes Naturels (EDEN). L’appellation, proposée par le Pr RABIBISOA Nirhy et validée par le Conseil de l’EDEN, traduit une volonté de valoriser notre territoire, notre environnement et notre biodiversité : <strong>Ma</strong> renvoie à Mahajanga, <strong>To</strong> à Tontolo (environnement) et <strong>tanY</strong> à tany (terre, habitat). En outre, <em>matoy</em> signifie littéralement « mûr » en français, évoquant la maturité, la connaissance et l’épanouissement.</p>'
                    .'<p>Après plusieurs années d’interruption, MaToY renaît sous une nouvelle édition, avec la volonté de faire vivre un espace de valorisation des activités scientifiques, académiques et institutionnelles de l’EDEN.</p>'
                    .'<h3>Informer, valoriser, partager</h3>'
                    .'<p>À travers ce journal trimestriel, l’EDEN souhaite faire connaître ses activités, valoriser les travaux de ses chercheurs et doctorants et partager les avancées de la recherche avec la communauté universitaire, les partenaires scientifiques et le grand public.</p>',
                'en' => '<p><strong>MaToY</strong> is the quarterly newsletter of the Doctoral School of Natural Ecosystems (EDEN). <strong>Ma</strong> stands for Mahajanga, <strong>To</strong> for Tontolo (environment) and <strong>tanY</strong> for tany (land, habitat); <em>matoy</em> also means “ripe” in Malagasy, evoking maturity and knowledge.</p>'
                    .'<p>After several years of interruption, MaToY returns with a new edition showcasing EDEN’s scientific, academic and institutional activities.</p>',
            ],
            'frequency' => ['fr' => 'Trimestriel', 'en' => 'Quarterly'],
            'domain' => 'Vie de l’École doctorale',
            'editor' => 'Pr Tit. RANARIJAONA Hery Lisy Tiana (direction éditoriale) · Dr Rickarlos BEZANDRY (rédaction et coordination)',
            'language' => 'Français',
            'cover_image' => $this->media('cover.jpg'),
            'status' => 'active',
            'is_featured' => true,
            'position' => 0,
        ]);

        $issue = JournalIssue::query()->firstOrNew(['journal_id' => $journal->id, 'slug' => 'n01-2026']);

        $issue->fill([
            'number' => 1,
            'year' => 2026,
            'period' => ['fr' => 'Juin - Août', 'en' => 'June - August'],
            'title' => ['fr' => 'Nouvelle édition', 'en' => 'New edition'],
            'editorial' => [
                'fr' => '<p>Cette nouvelle édition de <strong>MaToY</strong> met en lumière les temps forts qui ont marqué la vie de l’EDEN au cours de la période écoulée : recherches, publications, projets, soutenances, partenariats et activités scientifiques. Elle offre un aperçu de la diversité des initiatives portées par les doctorants et les chercheurs de l’EDEN, ainsi que de leur contribution au rayonnement scientifique de l’Université de Mahajanga.</p>'
                    .'<h3>L’EDEN depuis 2013</h3>'
                    .'<p>L’EDEN a été habilitée par le MESUPRES le 31 janvier 2013, par l’arrêté n°1951/2013. Sa première directrice, la Pr Tit. RANARIJAONA Hery Lisy Tiana, en assure aujourd’hui encore la direction. L’EDEN est dédiée à la formation doctorale et à la recherche sur les écosystèmes naturels : biodiversité, écologie, conservation, agroécologie, évolution et systématique.</p>'
                    .'<p>Implantée à l’Université de Mahajanga, à l’ouest de Madagascar, l’EDEN développe principalement ses recherches dans l’Ouest et le Nord-Ouest du pays, tout en accueillant des travaux sur différents écosystèmes et groupes biologiques de Madagascar.</p>'
                    .'<p><em>Direction éditoriale : Pr Tit. RANARIJAONA Hery Lisy Tiana, Directrice de l’EDEN. Rédaction et coordination éditoriale : Dr Rickarlos BEZANDRY, Responsable de la communication de l’EDEN.</em></p>',
                'en' => '<p>This new edition of <strong>MaToY</strong> highlights the key moments in EDEN’s life over the past period: research, publications, projects, thesis defenses, partnerships and scientific activities, and the contribution of its doctoral students and researchers to the scientific reputation of the University of Mahajanga.</p>',
            ],
            'cover_image' => $this->media('cover.jpg'),
            'pdf_url' => $this->media('matoy-n01-2026.pdf'),
            'pdf_size' => $this->size('matoy-n01-2026.pdf'),
            'page_count' => 11,
            'sections' => $this->sections(),
            'stats' => [
                ['label' => 'Communication', 'value' => 13],
                ['label' => 'Publication scientifique', 'value' => 2],
                ['label' => 'Formation', 'value' => 4],
                ['label' => 'Soutenance de doctorat', 'value' => 2],
                ['label' => 'Soutenance de HDR', 'value' => 1],
                ['label' => 'Projet en cours', 'value' => 4],
            ],
            'status' => 'published',
            'published_at' => $issue->published_at ?? now()->setDate(2026, 9, 1)->startOfDay(),
        ])->save();
    }

    private function sections(): array
    {
        $sections = [
            ['Actualités', 2, 'workshop.jpg', 'Conservation et restauration : l’EDEN renforce le leadership communautaire',
                'Avec le Duke Lemur Center (DLC) et AlaTsimarefo, dans le cadre du projet Demand for Restoration (D4R) financé par la fondation suisse SoR4D, l’EDEN a organisé un atelier sur les approches participatives de la conservation et de la restauration (10 au 13 août 2026, Université de Mahajanga) : inclusion, consentement libre, préalable et éclairé (FPIC), dialogue avec les VOI, savoirs locaux et planification d’actions de restauration.'],
            ['Formation & doctorat', 2, 'doctoriales.jpg', 'Doctoriales 2026 : l’EDEN à l’honneur',
                '12ᵉ édition des Doctoriales de l’Université d’Antananarivo (15 au 17 juillet 2026). Onze doctorant·es de l’EDEN y ont participé ; RABEMANANJARA Paul Bienvenu et NDRIANJAFY Aurélien sont lauréats du Premier Prix du meilleur projet multidisciplinaire et innovant avec le projet « Système Avi-Piscicole Hybride Intégré Résilient (SAPHIR) ».'],
            ['Formation & doctorat', 3, 'jrist.jpg', 'Journées de Recherche Scientifique des IST (JRIST 2026) à Antsiranana',
                '11ᵉ édition (24 au 26 août 2026), sur le thème « Du savoir à l’impact ». Deux communications affichées de l’EDEN : FLORINE, sur la gestion des ressources naturelles et la dégradation forestière à Antsanitia, et RAZAFINDRATONGA Judicaël Bertôt, sur l’érosion hydrique des sols du bassin versant de Kamoro (USLE).'],
            ['Soutenances', 4, null, 'ANDRIAMALALALA Alain Michaël, thèse de doctorat',
                '8 juillet 2026, salle de conférence de l’EDEN. « Évaluation de l’état éco-hydrologique du complexe Mahavavy-Kinkony en vue de la mise en place d’un programme de base de restauration ».'],
            ['Soutenances', 4, 'soutenance-rabemarosoa.jpg', 'RABEMAROSOA Jean Christophe, thèse de doctorat',
                '17 juillet 2026, salle de conférence de l’EDEN. « Reforestation et restauration des écosystèmes fragiles en utilisant les graines déféquées par Eulemur coronatus de la Montagne des Français, Antsiranana II ».'],
            ['Soutenances', 4, 'hdr-tsiavahananahary.jpg', 'TSIAVAHANANAHARY Tsaralaza Jorlin, HDR',
                '24 août 2026, salle de conférence KAKAL. « Dynamique des espèces et des habitats vulnérables de Madagascar face au changement climatique : une approche holistique vers des stratégies adaptatives et de conservation intégrée ».'],
            ['Recherche & rayonnement', 5, 'mantidactylus.jpg', 'Mantidactylus rabibisoai, une nouvelle grenouille dédiée au Pr RABIBISOA Nirhy',
                'Décrite en 2026 (Vences et al., Spixiana 48(2) : 221-302), cette espèce endémique honore l’enseignant-chercheur de l’Université de Mahajanga et chef de l’équipe d’accueil BAEP de l’EDEN. Le numéro rend aussi hommage à d’autres chercheurs malagasy honorés par la nomenclature : Pr Sylvain Razafimandimbison, feue Dr Elisette Rahelivololona et Pr Hery Lisy Tiana Ranarijaona.'],
            ['Publications scientifiques', 6, 'plant-environment.jpg', 'Zones humides du Nord-Ouest : publication dans Plant-Environment Interactions',
                'Ramiandrisoa, B. A. et al. (2026). Impact of Anthropogenic Activities and Climate Change on the Wetlands in the North-West of Madagascar. Plant-Environment Interactions, 7, e70206.'],
            ['Publications scientifiques', 6, 'aerobiologia.jpg', 'Aérobiologie en Afrique : publication dans Aerobiologia',
                'Gharbi, D. et al. (2026). Aerobiology in Africa: a continent-wide review of pollen, fungal spores, and allergy risk. Aerobiologia, 42, 49.'],
            ['Projets & partenariats', 7, 'ravenala.jpg', 'Projet RAVENALA : de nouveaux équipements pour la pédagogie universitaire',
                'Financé par Erasmus+, le projet a doté l’Université de Mahajanga de nouveaux équipements numériques en juillet 2026. Partenaires : universités de Mahajanga, d’Angers, Catholique de Louvain, d’Antsiranana et des Comores.'],
            ['Projets & partenariats', 7, 'coffea.jpg', 'Conservation de Coffea decaryana retenue par le MBZ Species Conservation Fund',
                'Projet porté par le Dr BEZANDRY Rickarlos, postdoctorant (EDEN), sélectionné pour financement.'],
            ['Projets & partenariats', 8, 'anas-bernieri.jpg', 'Canard de Madagascar (Anas bernieri) : un nouveau projet soutenu par le MBZ',
                'Porté par RIVOSON Jacquis Lucieno, doctorant : écologie spatiale, état de conservation et menaces dans le complexe Mahavavy-Kinkony et l’aire protégée de Bombetoka-Belemboka.'],
            ['Projets & partenariats', 8, 'erymnochelys.jpg', 'Erymnochelys madagascariensis : suivi soutenu par le Turtle Conservation Fund',
                'Porté par BENIRINA Fabrice Davy, doctorant : estimation et suivi à long terme des populations dans le Parc national d’Ankarafantsika, avec Re:wild.'],
            ['Projets & partenariats', 9, 'cop17.jpg', 'L’EDEN représentée à la COP17 de la CNULCD en Mongolie',
                'Le Dr Irinomenjanahary RANDRIANJAFY a participé à la 17ᵉ Conférence des Parties à Oulan-Bator, au sein de la délégation malagasy, avec le soutien de la fondation Stiftung Artenschutz.'],
            ['Projets & partenariats', 9, 'clp-rufford.jpg', 'Learning Event CLP & Rufford à Andasibe',
                'Deux postdoctorants et un doctorant de l’EDEN ont participé à cinq jours d’ateliers sur le leadership en conservation, le changement de comportement et la recherche de financements.'],
            ['L’EDEN en action', 10, null, 'L’EDEN au Salon des Étudiants 2026',
                '21 et 22 août 2026, Mahajanga. Présentation du parcours doctoral, des domaines de recherche et des perspectives de l’EDEN aux étudiants et jeunes visiteurs.'],
            ['L’EDEN en ligne', 10, null, 'Un nouveau site web pour l’EDEN',
                'Un espace central d’information et de valorisation : formations doctorales, équipes de recherche, projets, publications et actualités.'],
        ];

        return array_map(fn (array $row) => array_filter([
            'rubric' => $row[0],
            'page' => $row[1],
            'image' => $row[2] ? $this->media($row[2]) : null,
            'title' => $row[3],
            'summary' => $row[4],
        ]), $sections);
    }

    private function media(string $file): ?string
    {
        $path = self::DIR.'/'.$file;

        return Storage::disk('public')->exists($path) ? Storage::url($path) : null;
    }

    private function size(string $file): ?int
    {
        $path = self::DIR.'/'.$file;

        return Storage::disk('public')->exists($path) ? Storage::disk('public')->size($path) : null;
    }
}
