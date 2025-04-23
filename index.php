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
    <div class="new-book-form">
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
    </div>
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

    <!-- Search bar logic -->
    <div class="search-container">
        <form method="GET" action="">
            <div class="input-style">

                <!-- Search input -->
                <div class="input-group">
                    <input type="text" class="form-control" name="search" placeholder="Search books..."
                        value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>" required>

                    <div class="input-group-append">
                        <button class="btn_search btn-primary" type="submit">Search</button>
                        <?php if (isset($_GET['search']) && !empty($_GET['search'])): ?>
                            <a href="?" class="btn_search btn-secondary">Clear</a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="sort-group">
                    <select name="sort" class="form-control" onchange="this.form.submit()">
                        <option value="">Sort by...</option>
                        <option value="titolo_asc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'titolo_asc') ? 'selected' : '' ?>>Title (A-Z)</option>
                        <option value="titolo_desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'titolo_desc') ? 'selected' : '' ?>>Title (Z-A)</option>
                        <option value="data_asc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'data_asc') ? 'selected' : '' ?>>Release Date (Oldest)</option>
                        <option value="data_desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'data_desc') ? 'selected' : '' ?>>Release Date (Newest)</option>
                        <option value="autore_asc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'autore_asc') ? 'selected' : '' ?>>Author (A-Z)</option>
                        <option value="autore_desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'autore_desc') ? 'selected' : '' ?>>Author (Z-A)</option>
                        <option value="pagine_asc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'pagine_asc') ? 'selected' : '' ?>>Pages (Fewest)</option>
                        <option value="pagine_desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'pagine_desc') ? 'selected' : '' ?>>Pages (Most)</option>
                    </select>
                </div>

            </div>
        </form>
    </div>

    <!-- Codice per la creazione della tabella libri e i bottoni per eliminare e modificare: -->
    <?php

    include("db.php");
    $conn = connect();

    $sql = "SELECT * FROM libri";

    // Controllo se è possibile effettuare la ricerca avendo i requisiti richiesti dall'utente
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $search_term = $conn->real_escape_string($_GET['search']);
        $sql .= " WHERE LOWER(titolo) LIKE LOWER('%$search_term%') 
                  OR LOWER(autore) LIKE LOWER('%$search_term%') 
                  OR id LIKE '%$search_term%'";
    }

    // Add sorting based on selection
    if (isset($_GET['sort']) && !empty($_GET['sort'])) {
        $sort_option = $_GET['sort'];
        switch ($sort_option) {
            case 'titolo_asc':
                $sql .= " ORDER BY titolo ASC";
                break;
            case 'titolo_desc':
                $sql .= " ORDER BY titolo DESC";
                break;
            case 'data_asc':
                $sql .= " ORDER BY data ASC";
                break;
            case 'data_desc':
                $sql .= " ORDER BY data DESC";
                break;
            case 'autore_asc':
                $sql .= " ORDER BY autore ASC";
                break;
            case 'autore_desc':
                $sql .= " ORDER BY autore DESC";
                break;
            case 'pagine_asc':
                $sql .= " ORDER BY num_pagine ASC";
                break;
            case 'pagine_desc':
                $sql .= " ORDER BY num_pagine DESC";
                break;
            default:
                $sql .= " ORDER BY titolo ASC";
        }
    } else {
        // Default sorting if none selected
        $sql .= " ORDER BY titolo ASC";
    }

    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {

        // Contatore totale elementi
        $count = mysqli_num_rows($result);
        $search_term = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
        $sort_option = isset($_GET['sort']) ? $_GET['sort'] : '';

        $message = $search_term ?
            "Found $count books matching \"$search_term\"" :
            "Showing all $count books";

        // Add sorting info if sorted
        if ($sort_option) {
            $sort_map = [
                'titolo_asc' => 'sorted by title (A-Z)',
                'titolo_desc' => 'sorted by title (Z-A)',
                'data_asc' => 'sorted by release date (oldest first)',
                'data_desc' => 'sorted by release date (newest first)',
                'autore_asc' => 'sorted by author (A-Z)',
                'autore_desc' => 'sorted by author (Z-A)',
                'pagine_asc' => 'sorted by page count (fewest first)',
                'pagine_desc' => 'sorted by page count (most first)'
            ];
            $message .= ' - ' . $sort_map[$sort_option];
        }

        echo '<div class="book-counter">';
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