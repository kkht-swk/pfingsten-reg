<?php

namespace App\Service;

class ToolRepository 
{
    public static $ALTERSKLASSEN = array("wU12", "mU12", "wU14","mU14");
    public function getAK(): array {
        return ToolRepository::$ALTERSKLASSEN;
    }

    public static $NAHRUNG = array("vegan", "fleischhaltig");
    public function getNahrung(): array {
        return ToolRepository::$NAHRUNG;
    }

    public function validateIBAN(string $iban): bool {
        return true;
    }

    public function iban2bank(string $iban): string {
        return "Dummy Bank";
    }

}