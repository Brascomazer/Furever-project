<?php
require_once 'Gebruiker.php';
require_once 'Asiel.php';

class Bericht {
    private int $id;
    private Gebruiker $afzender;
    private Asiel $ontvanger;
    private string $inhoud;
    private DateTime $verzendtijd;

    public function verzenden(): void {
        // implementatie komt later
    }

    public function ontvangen(): array {
        return []; // lijst van Bericht-objecten
    }
}
?>