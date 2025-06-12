<?php
// filepath: c:\xampp\htdocs\Furever-project\swipe.php
// Start de sessie zodat we gebruikersgegevens kunnen opslaan
session_start();

// Include de database configuratie
require_once 'config.php';

// Include de benodigde classes
require_once 'classes/Gebruiker.php';
require_once 'classes/profiel.php';
require_once 'classes/Asiel.php';
require_once 'classes/dier.php'; 
require_once 'classes/Swipe.php'; 
require_once 'classes/match.php'; 
require_once 'classes/Bericht.php';

// Controleer of de gebruiker is ingelogd
$isIngelogd = isset($_SESSION['gebruiker_id']);

// Als gebruiker niet is ingelogd, stuur terug naar index
if (!$isIngelogd) {
    header("Location: index.php?foutmelding=" . urlencode("U moet ingelogd zijn om te kunnen swipen."));
    exit;
}

// Initialiseer gebruiker
$gebruiker = new Gebruiker();
$gebruiker->inloggen($_SESSION['gebruiker_email'], ''); // Laad gebruiker zonder wachtwoordcontrole

// Controleer of er een melding is in de URL
if (isset($_GET['melding'])) {
    $melding = $_GET['melding'];
}

if (isset($_GET['foutmelding'])) {
    $foutmelding = $_GET['foutmelding'];
}

// Verwerk swipe verzoeken
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['actie']) && $_POST['actie'] === 'swipe') {
        $gebruiker = new Gebruiker();
        $gebruiker->inloggen($_SESSION['gebruiker_email'], '');
        
        $dier = new Dier();
        $dier->setId($_POST['dier_id']);
        
        $swipe = new Swipe();
        if ($swipe->toevoegenSwipe($gebruiker, $dier, $_POST['richting'])) {
            if ($_POST['richting'] === Swipe::LIKE) {
                header("Location: swipe.php?melding=" . urlencode("Je hebt dit dier geliked!"));
            } else {
                header("Location: swipe.php?melding=" . urlencode("Je hebt dit dier niet geliked."));
            }
            exit;
        } else {
            header("Location: swipe.php?foutmelding=" . urlencode("Swipe kon niet worden toegevoegd."));
            exit;
        }
    }
}

// Haal een willekeurig dier op om te tonen
function haalWillekeurigDier() {
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
    if ($conn->connect_error) {
        die("Verbinding mislukt: " . $conn->connect_error);
    }
    
    // Haal een willekeurig dier op dat nog niet is geswiped door de huidige gebruiker
    $stmt = $conn->prepare("
        SELECT d.*, a.naam as asiel_naam 
        FROM dieren d
        JOIN asielen a ON d.asiel_id = a.id
        LEFT JOIN swipes s ON d.id = s.dier_id AND s.gebruiker_id = ?
        WHERE s.id IS NULL
        ORDER BY RAND()
        LIMIT 1
    ");
    $gebruiker_id = $_SESSION['gebruiker_id'];
    $stmt->bind_param("i", $gebruiker_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        return $result->fetch_assoc();
    } else {
        // Als er geen dieren meer zijn om te swipen
        return null;
    }
}

$huidigDier = haalWillekeurigDier();
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Swipe - Furever</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Furever</h1>
        <p>Vind jouw perfecte dierenmatch!</p>
        <nav>
            <a href="index.php">Home</a>
            <a href="swipe.php" class="active">Swipe</a>
        </nav>
    </header>

    <div class="container">
        <?php if (isset($melding)): ?>
            <div class="message success"><?php echo $melding; ?></div>
        <?php endif; ?>
        
        <?php if (isset($foutmelding)): ?>
            <div class="message error"><?php echo $foutmelding; ?></div>
        <?php endif; ?>

        <h2>Dieren om te ontdekken</h2>
        
        <?php if ($huidigDier): ?>
            <div class="animal-card">
                <img src="<?php echo !empty($huidigDier['foto']) ? htmlspecialchars($huidigDier['foto']) : 'https://via.placeholder.com/400x200?text=Schattige+Dier'; ?>" 
                     alt="<?php echo htmlspecialchars($huidigDier['naam']); ?>" class="animal-img">
                <div class="animal-info">
                    <h3><?php echo htmlspecialchars($huidigDier['naam']); ?></h3>
                    <p><strong>Soort:</strong> <?php echo htmlspecialchars($huidigDier['soort']); ?></p>
                    <p><strong>Ras:</strong> <?php echo htmlspecialchars($huidigDier['ras']); ?></p>
                    <p><strong>Leeftijd:</strong> <?php echo htmlspecialchars($huidigDier['leeftijd']); ?> jaar</p>
                    <p><strong>Asiel:</strong> <?php echo htmlspecialchars($huidigDier['asiel_naam']); ?></p>
                    <p><?php echo htmlspecialchars($huidigDier['beschrijving']); ?></p>
                </div>
                <form method="post" action="" class="swipe-buttons">
                    <input type="hidden" name="actie" value="swipe">
                    <input type="hidden" name="dier_id" value="<?php echo $huidigDier['id']; ?>">
                    <button type="submit" name="richting" value="DISLIKE" class="swipe-button dislike">Niet mijn type</button>
                    <button type="submit" name="richting" value="LIKE" class="swipe-button like">Like!</button>
                </form>
            </div>
        <?php else: ?>
            <div class="no-animals">
                <p>Je hebt alle beschikbare dieren gezien! Kom later terug voor meer.</p>
                <a href="index.php" class="button">Terug naar homepage</a>
            </div>
        <?php endif; ?>
    </div>
    
    <footer>
        <p>&copy; <?php echo date("Y"); ?> Furever - Help huisdieren een thuis vinden.</p>
    </footer>
</body>
</html>