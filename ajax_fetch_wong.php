<?php

// This script demonstrates how to extract EAN/GTIN codes from a database and include them in the JSON response

header('Content-Type: application/json');

// Simulated database connection (replace with your actual connection code)
function getDatabaseConnection() {
    // Placeholder for database connection
    $pdo = new PDO('mysql:host=localhost;dbname=sales_db', 'username', 'password');
    return $pdo;
}

// Fetch data including EAN/GTIN codes from the database
function fetchProducts() {
    $pdo = getDatabaseConnection();
    $statement = $pdo->prepare('SELECT id, name, price, ean FROM products');
    $statement->execute();
    
    return $statement->fetchAll(PDO::FETCH_ASSOC);
}

// Main logic
try {
    $products = fetchProducts();

    $response = [
        'success' => true,
        'data' => $products,
    ];

    echo json_encode($response);
} catch (Exception $e) {
    $response = [
        'success' => false,
        'error' => $e->getMessage(),
    ];

    echo json_encode($response);
}
