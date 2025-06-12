<?php
// filepath: c:\xampp\htdocs\Furever-project\classes\match.php
require_once 'Gebruiker.php';
require_once 'Dier.php';

class DierMatch { // Changed from Match to DierMatch
    private int $id;
    private Gebruiker $gebruiker;
    private Dier $dier;

    public function getId(): int {
        return $this->id;
    }

    public function getGebruiker(): Gebruiker {
        return $this->gebruiker;
    }

    public function getDier(): Dier {
        return $this->dier;
    }

    public function controlerenMatch(Gebruiker $gebruiker, Dier $dier): bool {
        $this->gebruiker = $gebruiker;
        $this->dier = $dier;
        
        // Voeg de match toe aan de database
         $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
        
        if ($conn->connect_error) {
            die("Verbinding mislukt: " . $conn->connect_error);
        }
        
        $stmt = $conn->prepare("INSERT INTO matches (gebruiker_id, dier_id, datum) VALUES (?, ?, NOW())");
        $gebruikerId = $gebruiker->getId();
        $dierId = $dier->getId();
        $stmt->bind_param("ii", $gebruikerId, $dierId);
        $success = $stmt->execute();
        
        if ($success) {
            $this->id = $conn->insert_id;
        }
        
        $stmt->close();
        $conn->close();
        
        return $success;
    }
    
    public static function getMatchesVoorGebruiker(Gebruiker $gebruiker): array {
        $matches = [];
        $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
        
        if ($conn->connect_error) {
            die("Verbinding mislukt: " . $conn->connect_error);
        }
        
        $stmt = $conn->prepare("
            SELECT m.id, m.gebruiker_id, m.dier_id, d.naam, d.soort, d.ras, d.leeftijd, d.beschrijving, a.id as asiel_id, a.naam as asiel_naam 
            FROM matches m
            JOIN dieren d ON m.dier_id = d.id
            JOIN asielen a ON d.asiel_id = a.id
            WHERE m.gebruiker_id = ?
        ");
        $gebruikerId = $gebruiker->getId();
        $stmt->bind_param("i", $gebruikerId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $match = new DierMatch();
            $match->id = $row["id"];
            
            $match->gebruiker = $gebruiker;
            
            // Creëer een Asiel object en vul alleen de benodigde velden in
            $asiel = new Asiel($row["asiel_naam"]); // Gebruik de constructor met naam
            $asiel->setId($row["asiel_id"]);
            
            $dier = new Dier();
            $dier->setId($row["dier_id"]);
            $dier->setNaam($row["naam"]);
            $dier->setSoort($row["soort"]);
            $dier->setRas($row["ras"]);
            $dier->setLeeftijd($row["leeftijd"]);
            $dier->setBeschrijving($row["beschrijving"]);
            $dier->setAsiel($asiel);
            
            $match->dier = $dier;
            
            $matches[] = $match;
        }
        
        $stmt->close();
        $conn->close();
        
        return $matches;
    }
}
?>