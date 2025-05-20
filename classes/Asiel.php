<?php
require_once 'Dier.php';

class Asiel {
    private int $id;
    private string $naam;
    private string $locatie;
    private string $contactgegevens;
    private array $dieren = []; // array van Dier-objecten

    public function __construct(string $naam = "", string $locatie = "", string $contactgegevens = "") {
        $this->naam = $naam;
        $this->locatie = $locatie;
        $this->contactgegevens = $contactgegevens;
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

    public function getLocatie(): string {
        return $this->locatie;
    }

    public function getContactgegevens(): string {
        return $this->contactgegevens;
    }

    public function getDieren(): array {
        // Haal dieren op uit database
         $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
        
        if ($conn->connect_error) {
            die("Verbinding mislukt: " . $conn->connect_error);
        }
        
        $stmt = $conn->prepare("SELECT id, naam, soort, ras, leeftijd, beschrijving FROM dieren WHERE asiel_id = ?");
        $stmt->bind_param("i", $this->id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $this->dieren = [];
        
        while ($row = $result->fetch_assoc()) {
            $dier = new Dier();
            $dier->setId($row["id"]);
            $dier->setNaam($row["naam"]);
            $dier->setSoort($row["soort"]);
            $dier->setRas($row["ras"]);
            $dier->setLeeftijd($row["leeftijd"]);
            $dier->setBeschrijving($row["beschrijving"]);
            $dier->setAsiel($this);
            
            $this->dieren[] = $dier;
        }
        
        $stmt->close();
        $conn->close();
        
        return $this->dieren;
    }

    public function dierToevoegen(Dier $dier): bool {
        // Verbinding met de database
        $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
        
        if ($conn->connect_error) {
            die("Verbinding mislukt: " . $conn->connect_error);
        }
        
        // Voeg dier toe aan de database
        $stmt = $conn->prepare("INSERT INTO dieren (naam, soort, ras, leeftijd, beschrijving, asiel_id) VALUES (?, ?, ?, ?, ?, ?)");
        $naam = $dier->getNaam();
        $soort = $dier->getSoort();
        $ras = $dier->getRas();
        $leeftijd = $dier->getLeeftijd();
        $beschrijving = $dier->getBeschrijving();
        
        $stmt->bind_param("sssisi", $naam, $soort, $ras, $leeftijd, $beschrijving, $this->id);
        $success = $stmt->execute();
        
        if ($success) {
            $dier->setId($conn->insert_id);
            $this->dieren[] = $dier;
        }
        
        $stmt->close();
        $conn->close();
        
        return $success;
    }

    public function dierVerwijderen(Dier $dier): bool {
        // Verbinding met de database
                $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

        
        if ($conn->connect_error) {
            die("Verbinding mislukt: " . $conn->connect_error);
        }
        
        // Verwijder dier uit de database
        $stmt = $conn->prepare("DELETE FROM dieren WHERE id = ? AND asiel_id = ?");
        $dierId = $dier->getId();
        $stmt->bind_param("ii", $dierId, $this->id);
        $success = $stmt->execute();
        
        if ($success) {
            // Verwijder dier uit de lokale array
            foreach ($this->dieren as $key => $value) {
                if ($value->getId() === $dier->getId()) {
                    unset($this->dieren[$key]);
                    break;
                }
            }
        }
        
        $stmt->close();
        $conn->close();
        
        return $success;
    }
}
?>