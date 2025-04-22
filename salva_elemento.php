<?php

session_start();
// Richiamo connessione DB:
include("db.php");
$conn = connect();

// Inserimento e validazione dei dati con metodo POST
$titolo = $_POST['titolo'] ?? '';
$autore = $_POST['autore'] ?? '';
$num_pagine = isset($_POST['num_pagine']) && $_POST['num_pagine'] !== '' ? (int)$_POST['num_pagine'] : 0;
$date = $_POST['data'] ?? '';

// Validazione dei campi obbligatori
$errors = [];

if (empty($titolo)) {
        $errors[] = "Il titolo è obbligatorio";
}
if (empty($autore)) {
        $errors[] = "L'autore è obbligatorio";
}
if (empty($date)) {
        $errors[] = "La data è obbligatoria";
}

if (!empty($errors)) {
        $errorMessage = urlencode(implode("<br>", $errors));
        header("Location: index.php?error=" . $errorMessage);
        exit();
}

try {
        $date = new DateTime($date);
        $formattedDate = $date->format('Y-m-d');
        echo "Valid date: " . $formattedDate;
} catch (Exception $e) {
        die("Invalid date: " . $e->getMessage());
}

// Inserisco dati nel DB:
$stmt = $conn->prepare("INSERT INTO libri (titolo, autore, num_pagine, data) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssis", $titolo, $autore, $num_pagine, $formattedDate);

if ($stmt->execute() === TRUE) {
        // Messaggio di notifca
        $_SESSION['popup_message'] = "Un elemento è stato inserito!";

        // valore id_libro:
        $sql = "SELECT id FROM libri ORDER BY id DESC LIMIT 1"; // faccio una query per trovare l'id del libro appena salvato
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $_SESSION['last_id'] = $row['id'];
                $last_id = $row['id']; // MI SERVE QUESTO ID PER IL LOG

        } else {
                echo "Record non trovato";
        }

        // Faccio un select per prendere i dati 
        $stmt = $conn->prepare("SELECT * FROM libri WHERE id=?");
        $stmt->bind_param("i", $last_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        // prendo valore operazione e lo passo al Log page:
        $_SESSION['operazione'] = "insert";
        $_SESSION['payload'] = $result;
        header("Location: log_table.php");
} else {
        echo "Error: " . $stmt . "<br>" . $conn->error;
}

$conn->close();
