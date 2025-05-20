<?php

class Profiel {
    private int $id;
    private string $bio = '';
    private string $foto = '';
    private string $voorkeuren = '';

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

    public function setBio(?string $bio): void {
        $this->bio = $bio ?? '';  // Gebruik null coalescing operator om null te verwerken
    }

    public function getFoto(): string {
        return $this->foto;
    }

    public function setFoto(?string $foto): void {
        $this->foto = $foto ?? '';  // Verwerk null waarden
    }

    public function getVoorkeuren(): string {
        return $this->voorkeuren;
    }

    public function setVoorkeuren(?string $voorkeuren): void {
        $this->voorkeuren = $voorkeuren ?? '';  // Verwerk null waarden
    }

    public function bewerken(string $bio, string $foto, string $voorkeuren): bool {
        // Update profiel gegevens
        $this->bio = $bio;
        $this->foto = $foto;
        $this->voorkeuren = $voorkeuren;
        
        // Database update
        $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
        
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