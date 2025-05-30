<?php
require_once 'Profiel.php';

class Gebruiker {
    private int $id;
    private string $naam;
    private string $email;
    private string $wachtwoord;
    private Profiel $profiel;

    public function __construct(string $naam = "", string $email = "", string $wachtwoord = "") {
        $this->naam = $naam;
        $this->email = $email;
        if ($wachtwoord !== "") {
            $this->wachtwoord = password_hash($wachtwoord, PASSWORD_DEFAULT);
        }
        $this->profiel = new Profiel();
    }

    public function setId(int $id): void {
        $this->id = $id;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getNaam(): string {
        return $this->naam;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getProfiel(): Profiel {
        return $this->profiel;
    }

    public function setProfiel(Profiel $profiel): void {
        $this->profiel = $profiel;
    }

    public function registreren(): bool {
        // Verbinding met de database
        $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
        
        if ($conn->connect_error) {
            die("Verbinding mislukt: " . $conn->connect_error);
        }
        
        // Controleer of email al bestaat
        $stmt = $conn->prepare("SELECT id FROM gebruikers WHERE email = ?");
        $stmt->bind_param("s", $this->email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $stmt->close();
            $conn->close();
            return false; // Email bestaat al
        }
        
        // Nieuwe gebruiker toevoegen
        $stmt = $conn->prepare("INSERT INTO gebruikers (naam, email, wachtwoord) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $this->naam, $this->email, $this->wachtwoord);
        $success = $stmt->execute();
        
        if ($success) {
            $this->id = $conn->insert_id;
            
            // Maak een profiel aan voor de gebruiker
            $stmt = $conn->prepare("INSERT INTO profielen (gebruiker_id) VALUES (?)");
            $stmt->bind_param("i", $this->id);
            $stmt->execute();
        }
        
        $stmt->close();
        $conn->close();
        return $success;
    }

    public function inloggen(string $email, string $wachtwoord): bool {
        // Verbinding met de database
        $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
        
        if ($conn->connect_error) {
            die("Verbinding mislukt: " . $conn->connect_error);
        }
        
        // Gebruiker zoeken op email
        $stmt = $conn->prepare("SELECT id, naam, wachtwoord FROM gebruikers WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            
            // Voor debuggen, voeg deze regels toe:
            error_log("Retrieved password hash: " . $row["wachtwoord"]);
            error_log("Input password: " . $wachtwoord);
            
            // Wachtwoord controleren
            if ($wachtwoord === '' || password_verify($wachtwoord, $row["wachtwoord"])) {
                $this->id = $row["id"];
                $this->naam = $row["naam"];
                $this->email = $email;
                
                // Profiel laden
                $this->laadProfiel();
                
                // Zorg ervoor dat we de sessie starten zonder foutmeldingen
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                }
                
                $_SESSION["gebruiker_id"] = $this->id;
                $_SESSION["gebruiker_naam"] = $this->naam;
                $_SESSION["gebruiker_email"] = $email;
                
                $stmt->close();
                $conn->close();
                return true;
            } else {
                error_log("Password verification failed");
            }
        } else {
            error_log("No user found with email: " . $email);
        }
        
        $stmt->close();
        $conn->close();
        return false;
    }

    private function laadProfiel(): void {
        $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
        
        if ($conn->connect_error) {
            die("Verbinding mislukt: " . $conn->connect_error);
        }
        
        // Controleer eerst of er een profiel bestaat
        $stmt = $conn->prepare("SELECT id, bio, foto, voorkeuren FROM profielen WHERE gebruiker_id = ?");
        $stmt->bind_param("i", $this->id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            // Profiel gevonden, laad het
            $row = $result->fetch_assoc();
            $profiel = new Profiel();
            $profiel->setId($row["id"]);
            $profiel->setBio($row["bio"] ?? '');
            $profiel->setFoto($row["foto"] ?? '');
            $profiel->setVoorkeuren($row["voorkeuren"] ?? '');
            $this->profiel = $profiel;
        } else {
            // Geen profiel gevonden, maak een nieuw profiel aan
            $stmt->close();
            $stmt = $conn->prepare("INSERT INTO profielen (gebruiker_id, bio, foto, voorkeuren) VALUES (?, '', '', '')");
            $stmt->bind_param("i", $this->id);
            $stmt->execute();
            
            $profielId = $conn->insert_id;
            $profiel = new Profiel();
            $profiel->setId($profielId);
            $this->profiel = $profiel;
        }
        
        $stmt->close();
        $conn->close();
    }

    public function uitloggen(): void {
        // Als er geen sessie is, start er een
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Verwijder alle sessievariabelen
        $_SESSION = array();
        
        // Vernietig de sessie
        session_destroy();
    }
}
?>