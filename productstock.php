<?php

require("../init.php");

use WHMCS\Database\Capsule;

// Check if the 'pid' parameter is provided in the query string
$pid = isset($_GET['pid']) ? (int) $_GET['pid'] : 0;
$isJson = isset($_GET['json']) && $_GET['json'] === 'yes'; // Check if json=yes is set

header('Content-Type: ' . ($isJson ? 'application/json' : 'text/javascript'));

// If no valid pid is provided, show an error
if ($pid <= 0) {
    $response = ['error' => 'Invalid product ID'];
    echo $isJson ? json_encode($response) : "document.write('Invalid product ID');";
    exit;
}

// Fetch the product details from the database based on the pid
$product = Capsule::table('tblproducts')
    ->where('id', $pid)
    ->first(['name', 'qty', 'stockcontrol']); // Fetch stock control and quantity

if ($product) {
    $productName = htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8');
    $stockControl = (int) $product->stockcontrol;
    $stock = (int) $product->qty;

    // Prepare the stock display message
    if ($stockControl === 1) {
        if ($stock > 0) {
            $stockDisplay = $stock ; // Show the available quantity
        } else {
            $stockDisplay = 'Out of stock'; // No stock available
        }
    } else {
        // If stock control is disabled, show "Stocked"
        $stockDisplay = 'Stocked'; // Unlimited stock
    }

    // Prepare the response based on the format (JSON or JS)
    $response = [
        'product' => $productName,
        'stock' => $stockDisplay
    ];

    if ($isJson) {
        // If JSON format is requested
        echo json_encode($response);
    } else {
        // Default JS format
        echo "document.write('{$stockDisplay}');";
    }
} else {
    $response = ['error' => 'Product not found.'];
    echo $isJson ? json_encode($response) : "document.write('Product not found.');";
}
