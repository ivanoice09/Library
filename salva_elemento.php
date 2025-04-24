<?php
session_start();

// Richiamo connessione DB:
include("gestione_db/db.php");
$conn = connect();

// File upload handling
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // Inserimentob dei dati con metodo POST
        $titolo = $_POST['titolo'] ?? '';
        $autore = $_POST['autore'] ?? '';;
        $num_pagine = isset($_POST['num_pagine']) && $_POST['num_pagine'] !== '' ? (int)$_POST['num_pagine'] : 0;
        $date = $_POST['data'] ?? '';

        $uploadDir = 'uploads/';
        $maxFileSize = 5 * 1024 * 1024; // 5MB

        if (isset($_FILES['pdf_file'])) {
                $file = $_FILES['pdf_file'];

                // Validate file
                if ($file['error'] === UPLOAD_ERR_OK) {
                        if ($file['size'] > $maxFileSize) {
                                $_SESSION['popup_message'] = "Error: File size exceeds 5MB limit";
                                header("Location: index.php");
                                exit();
                        }

                        if ($file['type'] != 'application/pdf') {
                                $_SESSION['popup_message'] = "Error: Only PDF files are allowed";
                                header("Location: index.php");
                                exit();
                        }

                        // Create uploads directory if it doesn't exist
                        if (!file_exists($uploadDir)) {
                                mkdir($uploadDir, 0755, true);
                        }

                        // Generate unique filename
                        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
                        $fileName = uniqid() . '.' . $fileExtension;
                        $filePath = $uploadDir . $fileName;

                        if (move_uploaded_file($file['tmp_name'], $filePath)) {
                                // Save file path in database
                                $pdfPath = $conn->real_escape_string($filePath);
                        } else {
                                $_SESSION['popup_message'] = "Error uploading file";
                                header("Location: index.php");
                                exit();
                        }
                }
        }
}

// formattazione data
try {
        $date = new DateTime($date);
        $formattedDate = $date->format('Y-m-d');
        echo "Valid date: " . $formattedDate;
} catch (Exception $e) {
        die("Invalid date: " . $e->getMessage());
}

// Inserisco dati nel DB:
$stmt = $conn->prepare("INSERT INTO libri (titolo, autore, num_pagine, data, file) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("ssiss", $titolo, $autore, $num_pagine, $formattedDate, $pdfPath);

if ($stmt->execute() === TRUE) {
        // Messaggio di notifca
        $_SESSION['popup_message'] = "Un elemento è stato inserito!";

        // valore id_libro:
        $sql = "SELECT id FROM libri ORDER BY id DESC LIMIT 1"; // faccio una query per trovare l'id del libro appena salvato
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $_SESSION['last_id'] = $row['id'];
                $last_id = $row['id']; // mi serve per il payload

        } else {
                echo "Record non trovato";
        }

        // Faccio un select per prendere il PAYLOAD dell'utente appena creato
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
