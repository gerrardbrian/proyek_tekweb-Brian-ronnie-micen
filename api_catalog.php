<?php
require_once 'catalog.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$catalog = new Catalog();
$products = $catalog->getProducts($page);
$totalPages = $catalog->getTotalPages();

// Kembalikan JSON agar bisa diolah JS
echo json_encode([
    'products' => $products,
    'totalPages' => $totalPages,
    'currentPage' => $page
]);
?>