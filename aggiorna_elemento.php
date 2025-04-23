<?php

session_start();
include("gestione-db/db.php");
$conn = connect();

// check del metodo di richiesta della pagina vuol dire che sto cercando di salvare il dato
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // prendo l'ID di riferimento
    $id = $_POST['id'];

    // Prendo i dati da modificare
    $titolo = $_POST['titolo'];
    $autore = $_POST['autore'];
    $num_pagine = $_POST['num_pagine'];
    $data = $_POST['data'];

    // Faccio un select per prendere i dati 
    $stmt = $conn->prepare("SELECT * FROM libri WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
}

// Controllo se ci sono state apportate delle modifiche

if ($result) {
    $stmt = $conn->prepare("UPDATE libri SET titolo=?, autore=?, num_pagine=?, data=? WHERE id=?");
    $stmt->bind_param("ssisi", $titolo, $autore, $num_pagine, $data, $id);

    if ($stmt->execute() === TRUE) {
        // messaggio di notifica
        $_SESSION['popup_message'] = "Un elemento è stato aggiornato!";

        // valori che passo per logging di UPDATE:
        $_SESSION['operazione'] = "update";
        $_SESSION['last_id'] = $id;
        $_SESSION['payload'] = $result;
        header("Location: log_table.php");
        exit();
    } else {
        echo "Error updating record: " . $conn->error;
    }
}

$conn->close();
