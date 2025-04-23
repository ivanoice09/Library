<?php

include("gestione-db/db.php");
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

echo '<link href="css/modify-book-styles.css" rel="stylesheet">';

echo '<body>';

echo '<div class="form-title">Elemento che vuoi modificare</div>';

if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $domanda = "Are you sure you want to modify this book?";

    echo '<form method="POST" action="aggiorna_elemento.php" onsubmit="return confirm(\'' . $domanda . '\')" class="form-container">';

    echo '<input type="hidden" name="id" value="' . $row['id'] . '">';

    echo '<div class="form-fieldset">';

    // Title field
    echo '<label class="form-label">Titolo: </label>';
    echo '<input type="text" name="titolo" value="' . htmlspecialchars($row['titolo']) . '" class="form-input">';

    // Author field
    echo '<label class="form-label">Autore: </label>';
    echo '<input type="text" name="autore" value="' . htmlspecialchars($row['autore']) . '" class="form-input">';

    // Page count field
    echo '<label class="form-label">N° Pagine: </label>';
    echo '<input type="number" name="num_pagine" value="' . htmlspecialchars($row['num_pagine']) . '" class="form-input">';

    // Date field
    echo '<label class="form-label">Data di pubblicazione: </label>';
    echo '<input type="date" name="data" value="' . htmlspecialchars($row['data']) . '" class="form-input">';

    echo '</div>';

    // Submit button
    echo '<input type="submit" name="conferma" value="Conferma" class="form-submit">';

    echo "</form>";
} else {
    echo '<p class="error-message">No book found with this ID</p>';;
}

echo '<body>';
