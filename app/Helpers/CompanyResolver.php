<?php

namespace App\Helpers;

use App\Models\Company;

class CompanyResolver
{
    public static function resolve(?string $input): ?Company
    {
        if (!$input) return null;

        // normalize
        $name = strtolower(trim($input));
        $name = preg_replace('/[^a-z0-9\s]/', '', $name);
        $name = preg_replace('/\s+/', ' ', $name);
        $name = ucwords($name);

        // cari existing (case insensitive)
        $existing = Company::whereRaw('LOWER(name) = ?', [
            strtolower($name)
        ])->first();

        if ($existing) return $existing;

        // create baru
        return Company::create([
            'name' => $name
        ]);
    }
}
