<?php
require_once 'Gebruiker.php';
require_once 'Dier.php';
require_once 'match.php'; // Make sure to include this file

class Swipe {
    private int $id;
    private Gebruiker $gebruiker;
    private Dier $dier;
    private string $richting; // enum: LIKE of DISLIKE
    
    const LIKE = 'LIKE';
    const DISLIKE = 'DISLIKE';

    public function __construct(Gebruiker $gebruiker = null, Dier $dier = null, string $richting = '') {
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
        if ($richting !== self::LIKE && $richting !== self::DISLIKE) {
            return false;
        }
        
        $this->gebruiker = $gebruiker;
        $this->dier = $dier;
        $this->richting = $richting;
        
        // Toevoegen aan de database
        $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
        
        if ($conn->connect_error) {
            die("Verbinding mislukt: " . $conn->connect_error);
        }
        
        $stmt = $conn->prepare("INSERT INTO swipes (gebruiker_id, dier_id, richting) VALUES (?, ?, ?)");
        $gebruikerId = $gebruiker->getId();
        $dierId = $dier->getId();
        $stmt->bind_param("iis", $gebruikerId, $dierId, $richting);
        $success = $stmt->execute();
        
        if ($success) {
            $this->id = $conn->insert_id;
            
            // Als het een like is, controleer op match
            if ($richting === self::LIKE) {
                $this->controlerenOpMatch($gebruikerId, $dierId);
            }
        }
        
        $stmt->close();
        $conn->close();
        
        return $success;
    }

    private function controlerenOpMatch(int $gebruikerId, int $dierId): void {
        // Een match ontstaat als het asiel ook interesse heeft getoond in de gebruiker
        // Dit is een vereenvoudigde implementatie waarbij we altijd een match maken als een gebruiker een dier liked
        $match = new DierMatch(); // Changed from Match to DierMatch
        
        // Simuleer dat er altijd een match is
        $gebruiker = new Gebruiker();
        $gebruiker->setId($gebruikerId);
        
        $dier = new Dier();
        $dier->setId($dierId);
        
        $match->controlerenMatch($gebruiker, $dier);
    }
}
?>