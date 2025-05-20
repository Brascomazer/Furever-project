<?php

class Profiel {
    private int $id;
    private string $bio;
    private string $foto;
    private string $voorkeuren;

    public function __construct(string $bio = "", string $foto = "", string $voorkeuren = "") {
        $this->bio = $bio;
        $this->foto = $foto;
        $this->voorkeuren = $voorkeuren;
    }

    public function getId(): int {
        return $this->id;
    }

    public function setId(int $id): void {
        $this->id = $id;
    }

    public function getBio(): string {
        return $this->bio;
    }

    public function setBio(string $bio): void {
        $this->bio = $bio;
    }

    public function getFoto(): string {
        return $this->foto;
    }

    public function setFoto(string $foto): void {
        $this->foto = $foto;
    }

    public function getVoorkeuren(): string {
        return $this->voorkeuren;
    }

    public function setVoorkeuren(string $voorkeuren): void {
        $this->voorkeuren = $voorkeuren;
    }

    public function bewerken(string $bio, string $foto, string $voorkeuren): bool {
        // Update profiel gegevens
        $this->bio = $bio;
        $this->foto = $foto;
        $this->voorkeuren = $voorkeuren;
        
        // Database update
        $conn = new mysqli("localhost", "gebruikersnaam", "wachtwoord", "furever_db");
        
        if ($conn->connect_error) {
            die("Verbinding mislukt: " . $conn->connect_error);
        }
        
        $stmt = $conn->prepare("UPDATE profielen SET bio = ?, foto = ?, voorkeuren = ? WHERE id = ?");
        $stmt->bind_param("sssi", $this->bio, $this->foto, $this->voorkeuren, $this->id);
        $success = $stmt->execute();
        
        $stmt->close();
        $conn->close();
        
        return $success;
    }
}
?>