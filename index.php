<?php
// Start de sessie zodat we gebruikersgegevens kunnen opslaan
session_start();

// Include de database configuratie
require_once 'config.php';

// Include de benodigde classes
require_once 'classes/Gebruiker.php';
require_once 'classes/profiel.php';
require_once 'classes/Asiel.php';
require_once 'classes/dier.php'; 
require_once 'classes/swipe.php'; 
require_once 'classes/match.php'; 
require_once 'classes/Bericht.php';

// Controleer of de gebruiker is ingelogd
$isIngelogd = isset($_SESSION['gebruiker_id']);

// BELANGRIJKE FIX: Initialiseer $gebruiker als gebruiker is ingelogd
if ($isIngelogd) {
    $gebruiker = new Gebruiker();
    $gebruiker->inloggen($_SESSION['gebruiker_email'], ''); // Laad gebruiker zonder wachtwoordcontrole
}

// Controleer of er een melding is in de URL
if (isset($_GET['melding'])) {
    $melding = $_GET['melding'];
}

if (isset($_GET['foutmelding'])) {
    $foutmelding = $_GET['foutmelding'];
}

// Verwerk formulier verzoeken
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['actie'])) {
        switch ($_POST['actie']) {
            case 'registreren':
                // Nieuwe gebruiker registreren
                $gebruiker = new Gebruiker($_POST['naam'], $_POST['email'], $_POST['wachtwoord']);
                if ($gebruiker->registreren()) {
                    header("Location: index.php?melding=" . urlencode("Registratie succesvol. U kunt nu inloggen."));
                    exit;
                } else {
                    header("Location: index.php?foutmelding=" . urlencode("Registratie mislukt. Probeer het opnieuw."));
                    exit;
                }
                break;
                
            case 'inloggen':
                // Gebruiker inloggen
                $gebruiker = new Gebruiker();
                if ($gebruiker->inloggen($_POST['email'], $_POST['wachtwoord'])) {
                    header("Location: index.php");
                    exit;
                } else {
                    header("Location: index.php?foutmelding=" . urlencode("Inloggen mislukt. Controleer uw e-mail en wachtwoord."));
                    exit;
                }
                break;
                
            case 'uitloggen':
                // Gebruiker uitloggen
                $gebruiker = new Gebruiker();
                $gebruiker->uitloggen();
                header("Location: index.php?melding=" . urlencode("U bent uitgelogd."));
                exit;
                break;
                
            case 'profielbewerken':
                // Profiel bewerken
                if ($isIngelogd) {
                    // Laad de gebruiker
                    $gebruiker = new Gebruiker();
                    $gebruiker->inloggen($_SESSION['gebruiker_email'], ''); // Alleen om het profiel te laden
                    
                    // Bewerk het profiel
                    $profiel = $gebruiker->getProfiel();
                    if ($profiel->bewerken($_POST['bio'], $_POST['foto'], $_POST['voorkeuren'])) {
                        header("Location: index.php?melding=" . urlencode("Profiel succesvol bijgewerkt."));
                        exit;
                    } else {
                        header("Location: index.php?foutmelding=" . urlencode("Profiel bijwerken mislukt."));
                        exit;
                    }
                }
                break;
        }
    }
}

// Haal de matches op voor de ingelogde gebruiker
$matches = [];
if ($isIngelogd) {
    $matches = DierMatch::getMatchesVoorGebruiker($gebruiker);
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Furever - Vind je dierenmatch</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Furever</h1>
        <p>Vind jouw perfecte dierenmatch!</p>
        <?php if ($isIngelogd): ?>
        <nav>
            <a href="index.php" class="active">Home</a>
            <a href="swipe.php">Swipe</a>
        </nav>
        <?php endif; ?>
    </header>

    <div class="container">
        <?php if (isset($melding)): ?>
            <div class="message success"><?php echo $melding; ?></div>
        <?php endif; ?>
        
        <?php if (isset($foutmelding)): ?>
            <div class="message error"><?php echo $foutmelding; ?></div>
        <?php endif; ?>
        
        <?php if ($isIngelogd): ?>
            <!-- Ingelogde gebruiker interface -->
            <div class="dashboard">
                <div class="profile-section">
                    <h2>Welkom, <?php echo $_SESSION['gebruiker_naam']; ?>!</h2>
                    
                    <form method="post" action="" class="logout-form">
                        <input type="hidden" name="actie" value="uitloggen">
                        <button type="submit">Uitloggen</button>
                    </form>
                    
                    <h3>Jouw Profiel</h3>
                    <form method="post" action="" class="profile-form">
                        <input type="hidden" name="actie" value="profielbewerken">
                        
                        <div class="form-group">
                            <label for="bio">Over mij:</label>
                            <textarea id="bio" name="bio" rows="4"><?php echo htmlspecialchars($gebruiker->getProfiel()->getBio()); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="foto">Profielfoto URL:</label>
                            <input type="text" id="foto" name="foto" value="<?php echo htmlspecialchars($gebruiker->getProfiel()->getFoto()); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="voorkeuren">Voorkeuren voor huisdieren:</label>
                            <textarea id="voorkeuren" name="voorkeuren" rows="4"><?php echo htmlspecialchars($gebruiker->getProfiel()->getVoorkeuren()); ?></textarea>
                        </div>
                        
                        <button type="submit">Profiel Bijwerken</button>
                    </form>
                </div>
                
                <div class="matches-section">
                    <h3>Jouw Matches</h3>
                    <?php if (count($matches) > 0): ?>
                        <div class="matches-container">
                        <?php foreach ($matches as $match): ?>
                            <div class="match-card">
                                <div class="match-header">
                                    <h4><?php echo htmlspecialchars($match->getDier()->getNaam()); ?></h4>
                                </div>
                                <div class="match-details">
                                    <p><strong>Soort:</strong> <?php echo htmlspecialchars($match->getDier()->getSoort()); ?></p>
                                    <p><strong>Ras:</strong> <?php echo htmlspecialchars($match->getDier()->getRas()); ?></p>
                                    <p><strong>Asiel:</strong> <?php echo htmlspecialchars($match->getDier()->getAsiel()->getNaam()); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>Je hebt nog geen matches. Ga naar de <a href="swipe.php">swipe pagina</a> om dieren te ontdekken!</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="cta-card">
                <h3>Ontdek Meer Dieren</h3>
                <p>Ga naar de swipe pagina om meer schattige dieren te ontdekken die een thuis zoeken!</p>
                <a href="swipe.php" class="button primary-button">Begin met swipen</a>
            </div>
            
        <?php else: ?>
            <!-- Niet ingelogde gebruiker interface -->
            <div class="welcome-section">
                <h2>Welkom bij Furever!</h2>
                <p>Furever helpt jou bij het vinden van de perfecte huisdier-match. Maak kennis met verschillende dieren uit asielen en vind een maatje voor het leven!</p>
            </div>
            
            <div class="auth-container">
                <div class="auth-box">
                    <h2>Registreren</h2>
                    <form method="post" action="">
                        <input type="hidden" name="actie" value="registreren">
                        
                        <div class="form-group">
                            <label for="reg_naam">Naam:</label>
                            <input type="text" id="reg_naam" name="naam" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="reg_email">E-mail:</label>
                            <input type="email" id="reg_email" name="email" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="reg_wachtwoord">Wachtwoord:</label>
                            <input type="password" id="reg_wachtwoord" name="wachtwoord" required>
                        </div>
                        
                        <button type="submit">Registreren</button>
                    </form>
                </div>
                
                <div class="auth-box">
                    <h2>Inloggen</h2>
                    <form method="post" action="">
                        <input type="hidden" name="actie" value="inloggen">
                        
                        <div class="form-group">
                            <label for="login_email">E-mail:</label>
                            <input type="email" id="login_email" name="email" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="login_wachtwoord">Wachtwoord:</label>
                            <input type="password" id="login_wachtwoord" name="wachtwoord" required>
                        </div>
                        
                        <button type="submit">Inloggen</button>
                    </form>
                </div>
            </div>
            
            <div class="features">
                <div class="feature">
                    <h3>Ontdek</h3>
                    <p>Bekijk profielen van dieren die op zoek zijn naar een nieuw thuis.</p>
                </div>
                <div class="feature">
                    <h3>Match</h3>
                    <p>Like dieren die je leuk vindt en maak een match!</p>
                </div>
                <div class="feature">
                    <h3>Contact</h3>
                    <p>Neem contact op met het asiel en plan een ontmoeting.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <footer>
        <p>&copy; <?php echo date("Y"); ?> Furever - Help huisdieren een thuis vinden.</p>
    </footer>
</body>
</html>