<?php
// filepath: c:\xampp\htdocs\Furever-project\fix-photos.php
require_once 'config.php';

// Maak verbinding met de database
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Controleer verbinding
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Foto URLs repareren</h2>";

// Array met dieren en hun foto-URLs
$dierenFotos = [
    'Max' => 'https://images.unsplash.com/photo-1561495376-dc9c7c5b8726?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80',
    'Luna' => 'https://images.unsplash.com/photo-1533743983669-94fa5c4338ec?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80',
    'Rocky' => 'https://images.unsplash.com/photo-1537151608828-ea2b11777ee8?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80',
    'Bella' => 'https://images.unsplash.com/photo-1526336024174-e58f5cdd8e13?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80',
    'Charlie' => 'https://images.unsplash.com/photo-1505628346881-b72b27e84530?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80',
    'Milo' => 'https://images.unsplash.com/photo-1585110396000-c9ffd4e4b308?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80',
    'Daisy' => 'https://images.unsplash.com/photo-1503256207526-0d5d80fa2f47?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80',
    'Oscar' => 'https://images.unsplash.com/photo-1568152950566-c1bf43f4ab28?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80',
    'Buddy' => 'https://images.unsplash.com/photo-1551717743-49959800b1f6?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80',
    'Simba' => 'https://images.unsplash.com/photo-1543852786-1cf6624b9987?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80',
    'Lola' => 'https://images.unsplash.com/photo-1583511655857-d19b40a7a54e?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80',
    'Cooper' => 'https://images.unsplash.com/photo-1589941013453-ec89f33b5e95?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80',
    'Nala' => 'https://images.unsplash.com/photo-1513360371669-4adf3dd7dff8?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80',
    'Zeus' => 'https://images.unsplash.com/photo-1605568427385-334de4fbce92?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80',
    'Oliver' => 'https://images.unsplash.com/photo-1586289883499-f11d28abb121?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80',
    'Ruby' => 'https://images.unsplash.com/photo-1591382386627-349b692688ff?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80'
];

// Update elke dier met de juiste foto URL
foreach ($dierenFotos as $naam => $fotoUrl) {
    $stmt = $conn->prepare("UPDATE dieren SET foto = ? WHERE naam = ?");
    $stmt->bind_param("ss", $fotoUrl, $naam);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        echo "<p>Foto URL bijgewerkt voor <strong>$naam</strong></p>";
    } else {
        echo "<p>Geen update voor $naam (niet gevonden of foto al ingesteld)</p>";
    }
}

echo "<p style='margin-top: 20px; color: green; font-weight: bold;'>Foto URLs zijn bijgewerkt!</p>";
echo "<p><a href='index.php'>Terug naar de homepage</a> of <a href='swipe.php'>Ga naar swipe pagina</a></p>";

$conn->close();
?>