<?php
session_start();
include("gestione-db/db.php");
$conn = connect();

/*
Questo file gestisce il log delle azioni dell'utente
Viene mostrato: 
- Che tipo di operazione
- Quando è stato effetuato l'operazione (Data e ora)
- L'id del libro salvato, modificato o eliminato
- il payload: per mostrare in dettagli il risultato dell'operazione
*/

if (!isset($conn)) {
    die("La connessione dal database non è stata stabilita");
}

// Variabili da inserire nel database:
$operazione = null;
$id_libro = null;
$payload = null;

if (isset($_SESSION['operazione']) && isset($_SESSION['last_id']) && isset($_SESSION['payload'])) {
    $operazione = $_SESSION['operazione'];
    $id_libro = $_SESSION['last_id'];
    $payload = $_SESSION['payload'];

    $stmt = $conn->prepare("INSERT INTO log_table (operazione, id_libro, payload) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $operazione, $id_libro, json_encode($payload));

    if ($stmt->execute() === TRUE) {
        echo "New log created successfully";

        unset($_SESSION['operazione']);
        unset($_SESSION['last_id']);
        unset($_SESSION['payload']);
        
    } else {
        echo "Error: " . $stmt->error;
    }

    $conn->close();
}

// Ritorna all'index:
header("Location: index.php");

// Qui fa l'inserimento alla tabella attività