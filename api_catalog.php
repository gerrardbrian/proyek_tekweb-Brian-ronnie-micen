<?php
require_once 'catalog.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$catalog = new Catalog();
$products = $catalog->getProducts($page,6,$search);
$totalPages = $catalog->getTotalPages(6,$search);

header('Content-Type: application/json');//biar di baca json 

// Kembalikan JSON agar bisa diolah JS
echo json_encode([
    'products' => $products,
    'totalPages' => $totalPages,
    'currentPage' => $page
]);
?>