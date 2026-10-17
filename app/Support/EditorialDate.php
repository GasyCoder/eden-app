<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * Conversion entre les champs « datetime-local » de l'administration
 * (heure de Madagascar) et le stockage en UTC.
 */
class EditorialDate
{
    public static function timezone(): string
    {
        return config('eden.timezone', 'Indian/Antananarivo');
    }

    public static function fromInput(?string $value): ?Carbon
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        return Carbon::parse($value, self::timezone())->utc();
    }

    public static function toInput(?CarbonInterface $date): string
    {
        return $date ? $date->copy()->timezone(self::timezone())->format('Y-m-d\TH:i') : '';
    }
}
