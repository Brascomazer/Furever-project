<?php
// Start de sessie zodat we gebruikersgegevens kunnen opslaan
session_start();

// Include de database configuratie
require_once 'config.php';

// Include de benodigde classes (let op de hoofdletters in de bestandsnamen)
require_once 'classes/Gebruiker.php';
require_once 'classes/Profiel.php';
require_once 'classes/Asiel.php';
require_once 'classes/Dier.php'; // Let op: kleine letter in bestandsnaam
require_once 'classes/Swipe.php'; // Let op: kleine letter in bestandsnaam
require_once 'classes/Match.php'; // Let op: kleine letter in bestandsnaam
require_once 'classes/Bericht.php';

// Controleer of de gebruiker is ingelogd
$isIngelogd = isset($_SESSION['gebruiker_id']);

// Verwerk formulier verzoeken
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['actie'])) {
        switch ($_POST['actie']) {
            case 'registreren':
                // Nieuwe gebruiker registreren
                $gebruiker = new Gebruiker($_POST['naam'], $_POST['email'], $_POST['wachtwoord']);
                if ($gebruiker->registreren()) {
                    $melding = "Registratie succesvol. U kunt nu inloggen.";
                } else {
                    $foutmelding = "Registratie mislukt. Probeer het opnieuw.";
                }
                break;
                
            case 'inloggen':
                // Gebruiker inloggen
                $gebruiker = new Gebruiker();
                if ($gebruiker->inloggen($_POST['email'], $_POST['wachtwoord'])) {
                    $melding = "U bent succesvol ingelogd.";
                } else {
                    $foutmelding = "Inloggen mislukt. Controleer uw e-mail en wachtwoord.";
                }
                break;
                
            case 'uitloggen':
                // Gebruiker uitloggen
                $gebruiker = new Gebruiker();
                $gebruiker->uitloggen();
                $melding = "U bent uitgelogd.";
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
                        $melding = "Profiel succesvol bijgewerkt.";
                    } else {
                        $foutmelding = "Profiel bijwerken mislukt.";
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
                            $melding = "Je hebt dit dier geliked!";
                        } else {
                            $melding = "Je hebt dit dier niet geliked.";
                        }
                    } else {
                        $foutmelding = "Swipe kon niet worden toegevoegd.";
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
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        header {
            background-color: #ff6b6b;
            color: white;
            padding: 10px 0;
            text-align: center;
        }
        .auth-container {
            display: flex;
            justify-content: space-between;
            margin: 20px 0;
        }
        .auth-box {
            width: 45%;
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        form {
            margin-top: 15px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"],
        textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background-color: #ff6b6b;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
        .animal-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            overflow: hidden;
        }
        .animal-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .animal-info {
            padding: 15px;
        }
        .swipe-buttons {
            display: flex;
            justify-content: space-between;
            padding: 10px;
        }
        .swipe-button {
            width: 48%;
            padding: 10px 0;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .like {
            background-color: #4CAF50;
            color: white;
        }
        .dislike {
            background-color: #f44336;
            color: white;
        }
    </style>
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
                    <textarea id="bio" name="bio" rows="4"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="foto">Profielfoto URL:</label>
                    <input type="text" id="foto" name="foto">
                </div>
                
                <div class="form-group">
                    <label for="voorkeuren">Voorkeuren voor huisdieren:</label>
                    <textarea id="voorkeuren" name="voorkeuren" rows="4"></textarea>
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