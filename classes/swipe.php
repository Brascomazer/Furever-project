<?php
// filepath: c:\xampp\htdocs\Furever-project\classes\Swipe.php
require_once 'Gebruiker.php';
require_once 'Dier.php';
require_once 'match.php';

class Swipe {
    private int $id;
    private Gebruiker $gebruiker;
    private Dier $dier;
    private string $richting;
    
    // Constanten voor de richting van de swipe
    const LIKE = 'LIKE';
    const DISLIKE = 'DISLIKE';
    
    public function __construct(Gebruiker $gebruiker = null, Dier $dier = null, string $richting = "") {
        if ($gebruiker !== null) {
            $this->gebruiker = $gebruiker;
        }
        if ($dier !== null) {
            $this->dier = $dier;
        }
        $this->richting = $richting;
    }
    
    public function getId(): int {
        return $this->id;
    }
    
    public function getGebruiker(): Gebruiker {
        return $this->gebruiker;
    }
    
    public function getDier(): Dier {
        return $this->dier;
    }
    
    public function getRichting(): string {
        return $this->richting;
    }
    
    public function toevoegenSwipe(Gebruiker $gebruiker, Dier $dier, string $richting): bool {
        $this->gebruiker = $gebruiker;
        $this->dier = $dier;
        $this->richting = $richting;
        
        // Verbinding met de database
        $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
        
        if ($conn->connect_error) {
            die("Verbinding mislukt: " . $conn->connect_error);
        }
        
        // Controleer of er al een swipe is voor deze gebruiker en dit dier
        $stmt = $conn->prepare("SELECT id FROM swipes WHERE gebruiker_id = ? AND dier_id = ?");
        $gebruikerId = $gebruiker->getId();
        $dierId = $dier->getId();
        $stmt->bind_param("ii", $gebruikerId, $dierId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Update bestaande swipe
            $row = $result->fetch_assoc();
            $swipeId = $row["id"];
            $stmt = $conn->prepare("UPDATE swipes SET richting = ? WHERE id = ?");
            $stmt->bind_param("si", $richting, $swipeId);
        } else {
            // Voeg nieuwe swipe toe - zonder datum kolom
            $stmt = $conn->prepare("INSERT INTO swipes (gebruiker_id, dier_id, richting) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $gebruikerId, $dierId, $richting);
        }
        
        $success = $stmt->execute();
        
        if ($success && $richting === self::LIKE) {
            // Als het een like is, controleer op een match
            $this->controlerenOpMatch($gebruikerId, $dierId);
        }
        
        $stmt->close();
        $conn->close();
        
        return $success;
    }
    
    private function controlerenOpMatch(int $gebruikerId, int $dierId): void {
        // Controleer of er een match is (in dit geval betekent dat gewoon een like)
        $match = new DierMatch();
        $gebruiker = new Gebruiker();
        $gebruiker->setId($gebruikerId);
        
        $dier = new Dier();
        $dier->setId($dierId);
        
        $match->controlerenMatch($gebruiker, $dier);
    }
}
?>