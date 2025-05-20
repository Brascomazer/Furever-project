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

    public function __construct(string $naam = "", string $soort = "", string $ras = "", int $leeftijd = 0, string $beschrijving = "") {
        $this->naam = $naam;
        $this->soort = $soort;
        $this->ras = $ras;
        $this->leeftijd = $leeftijd;
        $this->beschrijving = $beschrijving;
    }

    public function getId(): int {
        return $this->id;
    }

    public function setId(int $id): void {
        $this->id = $id;
    }

    public function getNaam(): string {
        return $this->naam;
    }

    public function setNaam(string $naam): void {
        $this->naam = $naam;
    }

    public function getSoort(): string {
        return $this->soort;
    }

    public function setSoort(string $soort): void {
        $this->soort = $soort;
    }

    public function getRas(): string {
        return $this->ras;
    }

    public function setRas(string $ras): void {
        $this->ras = $ras;
    }

    public function getLeeftijd(): int {
        return $this->leeftijd;
    }

    public function setLeeftijd(int $leeftijd): void {
        $this->leeftijd = $leeftijd;
    }

    public function getAsiel(): Asiel {
        return $this->asiel;
    }

    public function setAsiel(Asiel $asiel): void {
        $this->asiel = $asiel;
    }

    public function getBeschrijving(): string {
        return $this->beschrijving;
    }

    public function setBeschrijving(string $beschrijving): void {
        $this->beschrijving = $beschrijving;
    }

    public function toevoegen(): bool {
        if (!isset($this->asiel)) {
            return false; // Kan geen dier toevoegen zonder asiel
        }
        
        return $this->asiel->dierToevoegen($this);
    }

    public function bewerken(string $naam, string $soort, string $ras, int $leeftijd, string $beschrijving): bool {
        $this->naam = $naam;
        $this->soort = $soort;
        $this->ras = $ras;
        $this->leeftijd = $leeftijd;
        $this->beschrijving = $beschrijving;
        
        // Database update
        $conn = new mysqli("localhost", "gebruikersnaam", "wachtwoord", "furever_db");
        
        if ($conn->connect_error) {
            die("Verbinding mislukt: " . $conn->connect_error);
        }
        
        $stmt = $conn->prepare("UPDATE dieren SET naam = ?, soort = ?, ras = ?, leeftijd = ?, beschrijving = ? WHERE id = ?");
        $stmt->bind_param("sssisi", $this->naam, $this->soort, $this->ras, $this->leeftijd, $this->beschrijving, $this->id);
        $success = $stmt->execute();
        
        $stmt->close();
        $conn->close();
        
        return $success;
    }
}
?>