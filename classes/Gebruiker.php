<?php
require_once 'Profiel.php';

class Gebruiker {
    private int $id;
    private string $naam;
    private string $email;
    private string $wachtwoord;
    private Profiel $profiel;

    public function registreren(): void {
        // implementatie komt later
    }

    public function inloggen(string $email, string $wachtwoord): bool {
        return true;
    }

    public function uitloggen(): void {
        // implementatie komt later
    }
}
?>