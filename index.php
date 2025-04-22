<!-- Inizio session -->
<?php session_start(); ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="styles.css" rel="stylesheet">
    <title>Book List</title>
</head>

<body>

    <main class="container">
        <form action="salva_elemento.php" method="POST">

            <div class="card">
                <div class="card-header">
                    Add a book
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="titolo">Titolo </label>
                        <input type="text" name="titolo" id="titolo" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="autore">Autore </label>
                        <input type="text" name="autore" id="autore" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="num_pagine">N° Pagine </label>
                        <input type="number" name="num_pagine" id="num_pagine" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="data">Data di pubblicazione </label>
                        <input type="date" name="data" id="data" class="form-control" required>
                    </div>
                </div>
                <div class="card-footer">
                    <input type="submit" class="btn btn-primary">
                </div>
            </div>

        </form>

        <!-- Notifica l'errore quando non viene inserito nessun dato -->
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger">
                <?php echo (urldecode($_GET['error'])); ?>
            </div>
        <?php endif; ?>

        <br>

        <!-- Notifica quando viene aggiunto un nuovo libro -->
        <?php
        if (isset($_GET['messaggio'])) {
            $message = htmlspecialchars(urldecode($_GET['messaggio']));
        }
        if (isset($_SESSION)) {
            $message = $_SESSION['popup_message'] ?? null;
        }
        if ($message && $message !== null) {
            echo '<div class="alert alert-primary" role="alert">' . $message . '</div>';
        }
        ?>

        <!-- Codice per la creazione della tabella libri e i bottoni per eliminare e modificare: -->
        <?php

        include("db.php");
        $conn = connect();

        $sql = "SELECT * FROM libri";
        $result = mysqli_query($conn, $sql);

        echo "Lista dei Libri: <br>";

        if (mysqli_num_rows($result) > 0) {

            // output data of each row
            echo "<table border='2'>";

            echo "<tr>";
            while ($field = $result->fetch_field()) {
                echo "<th>{$field->name}</th>";
            }

            echo "<th colspan=\"2\">actions</th>";

            echo "</tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>{$row['id']}</td>";
                echo "<td>{$row['titolo']}</td>";
                echo "<td>{$row['autore']}</td>";
                echo "<td>{$row['num_pagine']}</td>";
                echo "<td>{$row['data']}</td>";

                // Buttone modifica elemento, portando con se l'id dell'elemento:
                echo "<td><button type=\"button\" class=\"btn btn-primary\" onclick=\"window.open('modifica_elemento.php?id={$row['id']}', '_self')\">modifica</button></td>";

                // Bottone elimina elemento, portando con se l'id dell'elemento
                // dove è integrato la logica if confirm per verificare l'assicurazione dell'eliminazione
                echo "<td><button type=\"button\" class=\"btn btn-danger\" onclick=\"if(confirm('Are u sure you want to delete this element?')) window.open('elimina_elemento.php?id={$row['id']}', '_self')\">elimina</button></td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "0 results";
        }

        echo "<br>";

        // Notifica quando avviene una modifica
        if (isset($_SESSION['popup_message'])) {
            unset($_SESSION['popup_message']);
        }

        ?>

    </main>

</body>

</html>