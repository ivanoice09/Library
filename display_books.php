<!DOCTYPE html>
<html>

<head>
    <title>Your Books</title>
    <style>
        /* Basic CSS for the table */
        body {
            font-family: sans-serif;
        }

        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .view-button {
            background-color: #28a745;
            color: white;
            padding: 8px 12px;
            border: none;
            cursor: pointer;
            text-decoration: none;
        }

        .success {
            color: green;
            margin-top: 10px;
            text-align: center;
        }
    </style>
</head>

<body>
    <h2>Your Uploaded Books</h2>

    <?php if (isset($_GET['success'])): ?>
        <p class="success"><?php echo htmlspecialchars($_GET['success']); ?></p>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Filename</th>
                <th>Upload Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php

            include("gestione_db/db.php");
            $conn = connect();

            // Connect to the database
            $conn = new mysqli($servername, $username, $password, $dbname);

            // Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // Fetch book information from the database
            $sql = "SELECT id, filename, filepath, upload_date FROM libri ORDER BY upload_date DESC";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row["filename"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["upload_date"]) . "</td>";
                    echo "<td><a href='view_pdf.php?id=" . urlencode($row["id"]) . "' class='view-button'>View</a></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='3'>No books have been uploaded yet.</td></tr>";
            }

            $conn->close();
            ?>
        </tbody>
    </table>
</body>

</html>