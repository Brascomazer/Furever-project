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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo-container">
                <div class="logo">Furever</div>
                <div class="logo-tagline">Vind jouw perfecte match</div>
            </div>

            <nav>
                <a href="index.php">Home</a>
                <a href="swipe.php" class="active">Swipe</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <?php if (isset($melding)): ?>
            <div class="message success">
                <?php echo $melding; ?>
                <span class="message-close" onclick="this.parentElement.style.display='none'">×</span>
            </div>
        <?php endif; ?>
        
        <?php if (isset($foutmelding)): ?>
            <div class="message error">
                <?php echo $foutmelding; ?>
                <span class="message-close" onclick="this.parentElement.style.display='none'">×</span>
            </div>
        <?php endif; ?>

        <h2>Ontdek Huisdieren</h2>
        
        <div class="swipe-container">
            <?php if ($huidigDier): ?>
                <div class="animal-card">
                    <img src="<?php echo !empty($huidigDier['foto']) ? htmlspecialchars($huidigDier['foto']) : 'https://via.placeholder.com/400x300?text=Schattig+Dier'; ?>" 
                         alt="<?php echo htmlspecialchars($huidigDier['naam']); ?>" class="animal-img">
                    
                    <div class="animal-info">
                        <h3 class="animal-name"><?php echo htmlspecialchars($huidigDier['naam']); ?></h3>
                        <p class="animal-breed"><?php echo htmlspecialchars($huidigDier['soort']); ?> - <?php echo htmlspecialchars($huidigDier['ras']); ?></p>
                        
                        <div class="animal-details">
                            <div class="animal-detail">
                                <span class="animal-detail-icon">🎂</span>
                                <span><?php echo htmlspecialchars($huidigDier['leeftijd']); ?> jaar</span>
                            </div>
                            <div class="animal-detail">
                                <span class="animal-detail-icon">🏠</span>
                                <span><?php echo htmlspecialchars($huidigDier['asiel_naam']); ?></span>
                            </div>
                        </div>
                        
                        <p class="animal-description"><?php echo htmlspecialchars($huidigDier['beschrijving']); ?></p>
                    </div>
                    
                    <form method="post" action="" class="swipe-buttons">
                        <input type="hidden" name="actie" value="swipe">
                        <input type="hidden" name="dier_id" value="<?php echo $huidigDier['id']; ?>">
                        <button type="submit" name="richting" value="DISLIKE" class="swipe-btn swipe-btn-dislike">✖️</button>
                        <button type="submit" name="richting" value="LIKE" class="swipe-btn swipe-btn-like">❤️</button>
                    </form>
                </div>
            <?php else: ?>
                <div class="no-animals">
                    <h3>Je hebt alle beschikbare dieren gezien!</h3>
                    <p>Kom later terug voor meer schattige dieren om te ontdekken.</p>
                    <a href="index.php" class="btn btn-gradient">Terug naar homepage</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <footer>
        <div class="footer-container">
            <div>
                <div class="footer-logo">Furever</div>
                <p class="footer-desc">Help huisdieren een thuis vinden en vind jouw perfecte match.</p>
            </div>
            
            <div>
                <h4 class="footer-heading">Links</h4>
                <ul class="footer-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="swipe.php">Swipe</a></li>
                </ul>
            </div>
            
            <div>
                <h4 class="footer-heading">Contact</h4>
                <ul class="footer-links">
                    <li>Email: info@furever.nl</li>
                    <li>Tel: 020 123 4567</li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p class="footer-copyright">&copy; <?php echo date("Y"); ?> Furever - Alle rechten voorbehouden</p>
        </div>
    </footer>
</body>
</html>