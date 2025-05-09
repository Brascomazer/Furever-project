<?php
require_once 'Asiel.php';

class Dier {
    private int $id;
    private string $naam;
    private string $soort;
    private string $ras;
    private int $leeftijd;
    private Asiel $asiel;
    private string $beschrijving;

    public function toevoegen(): void {
        // implementatie komt later
    }

    public function bewerken(string $naam, string $soort, string $ras, int $leeftijd, string $beschrijving): void {
        // implementatie komt later
    }
}
?>