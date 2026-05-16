<?php

namespace App;

// see https://symfony.com/doc/current/best_practices.html#use-constants-to-define-options-that-rarely-change
class Config
{
    public const SWK_YEAR = '2026';
    public const MEAL_COST = 90;

    public static function formatIban(string $iban): string
    {
        // Remove all non-alphanumeric characters first
        $cleanIban = preg_replace('/[^a-zA-Z0-9]/', '', $iban);

        // Convert to uppercase
        $cleanIban = strtoupper($cleanIban);

        // Insert a space every 4 characters
        return trim(chunk_split($cleanIban, 4, ' '));
    }
}

