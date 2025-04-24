<?php
session_start();
include("gestione_db/db.php");
$conn = connect();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = intval($_GET['id']);
    $id = $_GET['id'];

    // First get the file path
    $sql = "SELECT file FROM books WHERE id = $id";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (!empty($row['file']) && file_exists($row['file'])) {
            unlink($row['file']);
        }
    }

    // Faccio un select per prendere i dati 
    $stmt = $conn->prepare("SELECT * FROM libri WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
}

if ($result) {
    $stmt = $conn->prepare("DELETE FROM libri WHERE id=?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute() === TRUE) {
        // messaggio di notifica
        $_SESSION['popup_message'] = "Un elemento è stato eliminato!";


        // valori che passo per logging di DELETE
        $_SESSION['operazione'] = "delete";
        $_SESSION['last_id'] = $id;
        $_SESSION['payload'] = $result;
        header("location: log_table.php");
        exit();
    } else {
        echo "Error deleting record: " . $conn->error;
    }
}

$conn->close();
