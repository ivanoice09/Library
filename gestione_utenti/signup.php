<?php
session_start();

include("../gestione_db/db.php");
$conn = connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = $_POST['name'] ?? '';
    $surname = $_POST['surname'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT) ?? '';

    $stmt = $conn->prepare("INSERT INTO utenti (name, surname, email, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $surname, $email, $password);
}

if ($stmt->execute() === TRUE) {
    $_SESSION['popup_message'] = "You have successfully signed up!";
    header("Location: login_page.html");
    exit;
} else {
    echo "Error: " . $stmt . "<br>" . $conn->error;
}

$conn->close();
