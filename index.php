<!-- Inizio session -->
<?php session_start(); ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href='styles.css' rel="stylesheet">
    <title>Book List</title>
</head>

<body>
    <div class="header">
        <header>
            <h1>The Library</h1>
        </header>
    </div>
    <h2>Add a book</h2>
    <form action="salva_elemento.php" method="POST">
        <fieldset>
            <label for="titolo">Title: </label>
            <input type="text" name="titolo" id="titolo" required>

            <label for="autore">Author: </label>
            <input type="text" name="autore" id="autore" required>

            <label for="num_pagine">N° Pages: </label>
            <input type="number" name="num_pagine" id="num_pagine" required>

            <label for="data">Date of release: </label>
            <input type="date" name="data" id="data" required>
        </fieldset>

        <input type="submit" value="Add">
    </form>

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
        echo '<style>
            .notification {
                position: fixed;
                top: 20px;
                left: 50%;
                transform: translateX(-50%);
                padding: 15px 25px;
                background-color: #4CAF50;
                color: white;
                border-radius: 5px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                display: flex;
                align-items: center;
                justify-content: space-between;
                min-width: 300px;
                max-width: 80%;
                z-index: 1000;
                animation: slideIn 0.5s, fadeOut 0.5s 3.5s forwards;
            }
            .notification.error {
                background-color: #f44336;
            }
            .notification.warning {
                background-color: #ff9800;
            }
            .notification.info {
                background-color: #2196F3;
            }
            .close-btn {
                margin-left: 15px;
                color: white;
                font-weight: bold;
                font-size: 18px;
                cursor: pointer;
                opacity: 0.8;
            }
            .close-btn:hover {
                opacity: 1;
            }
            @keyframes slideIn {
                from {top: -50px; opacity: 0;}
                to {top: 20px; opacity: 1;}
            }
            @keyframes fadeOut {
                from {opacity: 1;}
                to {opacity: 0; visibility: hidden;}
            }
        </style>';

        // Determine notification type based on message content
        $type = 'info';
        if (stripos($message, 'error') !== false || stripos($message, 'failed') !== false) {
            $type = 'error';
        } elseif (stripos($message, 'warning') !== false || stripos($message, 'attention') !== false) {
            $type = 'warning';
        } elseif (stripos($message, 'success') !== false || stripos($message, 'added') !== false || stripos($message, 'modified') !== false || stripos($message, 'deleted') !== false) {
            $type = '';
        }

        echo '<div class="notification' . ($type ? ' ' . $type : '') . '" id="notification">';
        echo $message;
        echo '<span class="close-btn" onclick="document.getElementById(\'notification\').style.display=\'none\'">&times;</span>';
        echo '</div>';

        // Auto-remove session message after displaying
        unset($_SESSION['popup_message']);
    }
    ?>

    <!-- Codice per la creazione della tabella libri e i bottoni per eliminare e modificare: -->
    <?php

    include("db.php");
    $conn = connect();

    $sql = "SELECT * FROM libri";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        echo '<div class="table-container" style="display: flex; justify-content: center; margin: 20px 0;">';
        echo '<div style="width: 90%; max-width: 1200px;">';

        echo '<style>
         .book-table {
             width: 100%;
             border-collapse: collapse;
             margin: 25px 0;
             font-size: 0.9em;
             font-family: sans-serif;
             box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
             border-radius: 10px;
             overflow: hidden;
         }
         .book-table thead tr {
             background-color: #2c3e50;
             color: #ffffff;
             text-align: left;
             font-weight: bold;
         }
         .book-table th,
         .book-table td {
             padding: 12px 15px;
             text-align: center;
         }
         .book-table tbody tr {
             border-bottom: 1px solid #dddddd;
         }
         .book-table tbody tr:nth-of-type(even) {
             background-color: #f3f3f3;
         }
         .book-table tbody tr:last-of-type {
             border-bottom: 2px solid #2c3e50;
         }
         .book-table tbody tr:hover {
             background-color: #e1f5fe;
             cursor: pointer;
         }
         .btn {
             padding: 8px 12px;
             border: none;
             border-radius: 4px;
             font-weight: 600;
             cursor: pointer;
             transition: all 0.3s;
         }
         .btn-primary {
             background-color: #3498db;
             color: white;
         }
         .btn-primary:hover {
             background-color: #2980b9;
         }
         .btn-danger {
             background-color: #e74c3c;
             color: white;
         }
         .btn-danger:hover {
             background-color: #c0392b;
         }
        </style>';

        echo "<table class='book-table'>";
        echo "<thead><tr>";
        while ($field = $result->fetch_field()) {
            echo "<th>{$field->name}</th>";
        }

        echo "<th colspan=\"2\">actions</th>";
        echo "</tr></thead>";

        echo "<tbody>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['titolo']}</td>";
            echo "<td>{$row['autore']}</td>";
            echo "<td>{$row['num_pagine']}</td>";
            echo "<td>" . date('d/m/Y', strtotime($row['data'])) . "</td>";

            echo "<td><button type='button' class='btn btn-primary' onclick=\"window.location.href='modifica_elemento.php?id={$row['id']}'\">Modifica</button></td>";
            echo "<td><button type='button' class='btn btn-danger' onclick=\"if(confirm('Sei sicuro di voler eliminare questo elemento?')) window.location.href='elimina_elemento.php?id={$row['id']}'\">Elimina</button></td>";
            echo "</tr>";
        }
        echo "</tbody></table>";
        echo '</div></div>';
    } else {
        echo '<div style="text-align: center; margin: 50px; font-size: 1.2em; color: #666;">Nessun risultato trovato</div>';
    }

    echo "<br>";

    ?>

</body>

</html>