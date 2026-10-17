<?php

namespace App\Contracts;

use Carbon\CarbonInterface;

/**
 * Contenu pouvant être envoyé aux abonnés de la newsletter
 * (actualité, événement, numéro de revue).
 */
interface Newsletterable
{
    /** Date à partir de laquelle le contenu est public (null tant qu'il ne l'est pas). */
    public function newsletterReadyAt(): ?CarbonInterface;

    /**
     * @return array{kind:string,title:string,excerpt:?string,image:?string,url:string,details:array<int,string>,cta:string}
     */
    public function newsletterContent(string $locale): array;
}
