<?php

function connect()
{
    // Connessione database:
    $servername = "localhost";
    $username = "root";
    $password = "1234";
    $nomedb = "biblioteca";

    $conn = mysqli_connect($servername, $username, $password, $nomedb);

    // Controllo connessione DB:
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    return $conn;
}

// Prevent directory traversal
function sanitizePath($path)
{
    $path = str_replace('../', '', $path);
    $path = str_replace('..\\', '', $path);
    return $path;
}
