<?php
require_once 'Gebruiker.php';
require_once 'Dier.php';

class Match {
    private int $id;
    private Gebruiker $gebruiker;
    private Dier $dier;

    public function controlerenMatch(Gebruiker $gebruiker, Dier $dier): bool {
        return true;
    }
}
?>