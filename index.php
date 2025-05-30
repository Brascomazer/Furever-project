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
                
            case 'swipe':
                // Een swipe registreren
                if ($isIngelogd) {
                    $gebruiker = new Gebruiker();
                    $gebruiker->inloggen($_SESSION['gebruiker_email'], '');
                    
                    $dier = new Dier();
                    $dier->setId($_POST['dier_id']);
                    
                    $swipe = new Swipe();
                    if ($swipe->toevoegenSwipe($gebruiker, $dier, $_POST['richting'])) {
                        if ($_POST['richting'] === Swipe::LIKE) {
                            header("Location: index.php?melding=" . urlencode("Je hebt dit dier geliked!"));
                        } else {
                            header("Location: index.php?melding=" . urlencode("Je hebt dit dier niet geliked."));
                        }
                        exit;
                    } else {
                        header("Location: index.php?foutmelding=" . urlencode("Swipe kon niet worden toegevoegd."));
                        exit;
                    }
                }
                break;
        }
    }
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
            <h2>Welkom, <?php echo $_SESSION['gebruiker_naam']; ?>!</h2>
            
            <form method="post" action="">
                <input type="hidden" name="actie" value="uitloggen">
                <button type="submit">Uitloggen</button>
            </form>
            
            <h3>Jouw Profiel</h3>
            <form method="post" action="">
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
            
            <h3>Dieren om te ontdekken</h3>
            <div class="animal-card">
                <img src="https://via.placeholder.com/400x200?text=Schattige+Hond" alt="Hond" class="animal-img">
                <div class="animal-info">
                    <h3>Max</h3>
                    <p><strong>Soort:</strong> Hond</p>
                    <p><strong>Ras:</strong> Golden Retriever</p>
                    <p><strong>Leeftijd:</strong> 3 jaar</p>
                    <p><strong>Asiel:</strong> Dierenhuis Amsterdam</p>
                    <p>Max is een vrolijke hond die graag speelt. Hij is goed met kinderen en andere honden.</p>
                </div>
                <form method="post" action="" class="swipe-buttons">
                    <input type="hidden" name="actie" value="swipe">
                    <input type="hidden" name="dier_id" value="1">
                    <button type="submit" name="richting" value="DISLIKE" class="swipe-button dislike">Niet mijn type</button>
                    <button type="submit" name="richting" value="LIKE" class="swipe-button like">Like!</button>
                </form>
            </div>
            
        <?php else: ?>
            <!-- Niet ingelogde gebruiker interface -->
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
        <?php endif; ?>
    </div>
</body>
</html>