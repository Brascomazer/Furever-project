<?php
require_once 'Dier.php';

class Asiel {
    private int $id;
    private string $naam;
    private string $locatie;
    private string $contactgegevens;
    private array $dieren = []; // array van Dier-objecten

    public function dierToevoegen(Dier $dier): void {
        // implementatie komt later
    }

    public function dierVerwijderen(Dier $dier): void {
        // implementatie komt later
    }
}
?>