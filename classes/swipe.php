<?php
require_once 'Gebruiker.php';
require_once 'Dier.php';

class Swipe {
    private int $id;
    private Gebruiker $gebruiker;
    private Dier $dier;
    private string $richting; // enum: LIKE of DISLIKE

    public function toevoegenSwipe(Gebruiker $gebruiker, Dier $dier, string $richting): void {
        // implementatie komt later
    }
}
?>