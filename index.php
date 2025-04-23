<!-- Inizio session -->
<?php session_start(); ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href='css/index-styles.css' rel="stylesheet">
    <title>Book List</title>
</head>

<body>
    <div class="sticky-header">
        <header>
            <h1>The Library</h1>
            <nav class="menu-nav">
                <ul>
                    <li><a href="">Home</a></li>
                    <li><a href="">Account</a></li>
                </ul>
            </nav>
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

    <h2>Book Records</h2>

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

    <div class="search-container" style="margin: 20px auto; max-width: 600px; text-align: center;">
        <form method="GET" action="">
            <div class="input-group">
                <input type="text" class="form-control" name="search" placeholder="Search books..."
                    value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">

                <div class="input-group-append">
                    <button class="btn btn-primary" type="submit">Search</button>
                    <?php if (isset($_GET['search']) && !empty($_GET['search'])): ?>
                        <a href="?" class="btn btn-secondary">Clear</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <!-- Codice per la creazione della tabella libri e i bottoni per eliminare e modificare: -->
    <?php

    include("db.php");
    $conn = connect();

    $sql = "SELECT * FROM libri";

    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $search_term = $conn->real_escape_string($_GET['search']);
        $sql .= " WHERE titolo LIKE '%$search_term%' 
                  OR autore LIKE '%$search_term%' 
                  OR id LIKE '%$search_term%'";
    }

    // Add sorting if needed (optional)
    $sql .= " ORDER BY titolo ASC";

    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {

        // Book counter
        $count = mysqli_num_rows($result);
        $search_term = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
        $message = $search_term ?
            "Found $count books matching \"$search_term\"" :
            "Showing all $count books";

        echo '<div style="text-align: center; margin: 10px 0 20px; color: #555;">';
        echo $message;
        echo '</div>';

        // Structure and Desgin of the book table
        // echo '<link href="css/index-styles.css" rel="stylesheet">';
        echo '<div class="table-container" style="display: flex; justify-content: center; margin: 20px 0;">';
        echo '<div style="width: 90%; max-width: 1200px;">';

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
            echo "<td>";
            if (!empty($row['data'])) {
                try {
                    $date = new DateTime($row['data']);
                    echo $date->format('d/m/Y');
                } catch (Exception $e) {
                    // Fallback to simple display if DateTime fails
                    echo htmlspecialchars($row['data']);
                }
            } else {
                echo '<span class="empty-value">N/A</span>';
            }
            echo "</td>";

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