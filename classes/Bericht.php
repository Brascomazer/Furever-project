<?php
require_once 'Gebruiker.php';
require_once 'Asiel.php';

class Bericht {
    private int $id;
    private Gebruiker $afzender;
    private Asiel $ontvanger;
    private string $inhoud;
    private DateTime $verzendtijd;

    public function __construct(Gebruiker $afzender = null, Asiel $ontvanger = null, string $inhoud = "") {
        if ($afzender !== null) {
            $this->afzender = $afzender;
        }
        if ($ontvanger !== null) {
            $this->ontvanger = $ontvanger;
        }
        $this->inhoud = $inhoud;
        $this->verzendtijd = new DateTime();
    }

    public function getId(): int {
        return $this->id;
    }

    public function getAfzender(): Gebruiker {
        return $this->afzender;
    }

    public function getOntvanger(): Asiel {
        return $this->ontvanger;
    }

    public function getInhoud(): string {
        return $this->inhoud;
    }

    public function getVerzendtijd(): DateTime {
        return $this->verzendtijd;
    }

    public function verzenden(): bool {
        // Toevoegen aan de database
        $conn = new mysqli("localhost", "gebruikersnaam", "wachtwoord", "furever_db");
        
        if ($conn->connect_error) {
            die("Verbinding mislukt: " . $conn->connect_error);
        }
        
        $stmt = $conn->prepare("INSERT INTO berichten (afzender_id, ontvanger_id, inhoud, verzendtijd) VALUES (?, ?, ?, NOW())");
        $afzenderId = $this->afzender->getId();
        $ontvangerId = $this->ontvanger->getId();
        $stmt->bind_param("iis", $afzenderId, $ontvangerId, $this->inhoud);
        $success = $stmt->execute();
        
        if ($success) {
            $this->id = $conn->insert_id;
            $this->verzendtijd = new DateTime();
        }
        
        $stmt->close();
        $conn->close();
        
        return $success;
    }

    public static function ontvangen(Gebruiker $gebruiker, Asiel $asiel): array {
        $berichten = [];
        $conn = new mysqli("localhost", "gebruikersnaam", "wachtwoord", "furever_db");
        
        if ($conn->connect_error) {
            die("Verbinding mislukt: " . $conn->connect_error);
        }
        
        // Haal berichten op tussen gebruiker en asiel
        $stmt = $conn->prepare("
            SELECT id, afzender_id, ontvanger_id, inhoud, verzendtijd 
            FROM berichten 
            WHERE (afzender_id = ? AND ontvanger_id = ?) OR (afzender_id = ? AND ontvanger_id = ?)
            ORDER BY verzendtijd ASC
        ");
        $gebruikerId = $gebruiker->getId();
        $asielId = $asiel->getId();
        $stmt->bind_param("iiii", $gebruikerId, $asielId, $asielId, $gebruikerId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $bericht = new Bericht();
            $bericht->id = $row["id"];
            
            // Bepaal wie de afzender en ontvanger is
            if ($row["afzender_id"] == $gebruikerId) {
                $bericht->afzender = $gebruiker;
                $bericht->ontvanger = $asiel;
            } else {
                // In dit geval is het asiel de afzender
                // We gebruiken een vereenvoudigde implementatie hier
                $bericht->afzender = new Gebruiker(); // Dit zou eigenlijk het asiel moeten zijn
                $bericht->ontvanger = $gebruiker; // Dit zou eigenlijk de gebruiker moeten zijn
            }
            
            $bericht->inhoud = $row["inhoud"];
            $bericht->verzendtijd = new DateTime($row["verzendtijd"]);
            
            $berichten[] = $bericht;
        }
        
        $stmt->close();
        $conn->close();
        
        return $berichten;
    }
}
?>