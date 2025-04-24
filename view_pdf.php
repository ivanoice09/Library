<?php
// Database connection
require_once 'gestione_db/db.php';
$conn = connect();

// Get book ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch PDF path from database
$sql = "SELECT file FROM libri WHERE id = $id";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $filePath = $row['file'];
    
    if (file_exists($filePath)) {
        // Set headers for PDF display
        header('Content-type: application/pdf');
        header('Content-Disposition: inline; filename="' . basename($filePath) . '"');
        header('Content-Transfer-Encoding: binary');
        header('Accept-Ranges: bytes');
        
        // Output the PDF
        readfile($filePath);
        exit();
    }
}

// If PDF not found
header("HTTP/1.0 404 Not Found");
echo "<h1>PDF Not Found</h1>";
echo "<p>The requested PDF file could not be found.</p>";
?>