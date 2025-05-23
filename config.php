<?php

// Database configuratie
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root'); // Default XAMPP MySQL username
define('DB_PASSWORD', '');     // Default XAMPP MySQL password is empty
define('DB_NAME', 'furever_db');

// Maak een verbinding met de database
try {
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    
    // Select the database
    $conn->select_db(DB_NAME);
    
    
} catch(Exception $e) {
    die("Fout bij verbinden met de database: " . $e->getMessage());
}
?>