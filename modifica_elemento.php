<?php

include("db.php");
$conn = connect();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // qui verifico solo il metodo, vuol dire che arrivo dalla index per usare la ui di modifica
    $id = $_GET['id'];
}

// mostrare prima l'elemento da modificare:
$stmt = $conn->prepare("SELECT id, titolo, autore, num_pagine, data FROM libri WHERE id=?");
$stmt->bind_param("i", $id);

if ($stmt->execute() === TRUE) {
    // se la query è andata a buon fine prendo il risultato
    $result = mysqli_stmt_get_result($stmt);
} else {
    echo "Error: " . $stmt . "<br>" . $conn->error;
}

$conn->close();

echo "Elemento che vuoi modificare: <br><br>";

if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $domanda = "Are you sure you want to modify this book?";

    echo "<form method=\"POST\" action=\"aggiorna_elemento.php\" onsubmit=\"return confirm('$domanda')\">";
    echo "<input type=\"hidden\" name=\"id\" value=\"{$row['id']}\">";
    echo "<p>Titolo: <input type=\"text\" name=\"titolo\" value=" . htmlspecialchars($row['titolo']) . "></p>";
    echo "<p>Autore: <input type=\"text\" name=\"autore\" value=" . htmlspecialchars($row['autore']) . "></p>";
    echo "<p>N° Pagine: <input type=\"number\" name=\"num_pagine\" value=" . htmlspecialchars($row['num_pagine']) . "></p>";
    echo "<p>Data di pubblicazione: <input type=\"date\" name=\"data\" value=" . htmlspecialchars($row['data']) . "></p>";

    // Submit button
    echo "<input type=\"submit\" name=\"conferma\" value=\"conferma\">";
    echo "</form>";

} else {
    echo "No user found with ID 1";
}
