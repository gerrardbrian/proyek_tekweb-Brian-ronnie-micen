<?php
session_start();
include_once 'database.php';
include_once 'product.php';
include_once 'admin_order.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header("Location: login.php"); exit; }

$database = new database();
$db = $database->getConnection();
$productObj = new product($db);
$orderObj = new admin_order($db);

// Handle Form Submit (Tambah Produk)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_product'])) {
    // Validasi sederhana
    if(!empty($_FILES['image']['name'])) {
        if($productObj->create($_POST['name'], $_POST['price'], $_POST['desc'], $_FILES['image'])) {
            $success_msg = "Produk berhasil ditambahkan!";
        } else {
            $error_msg = "Gagal upload gambar atau simpan database.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Ronnie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background-color: #343a40; color: white; }
        .sidebar a { color: #adb5bd; text-decoration: none; padding: 10px; display: block; }
        .sidebar a:hover { background-color: #495057; color: white; border-radius: 5px; }
        .sidebar .active { background-color: #0d6efd; color: white; border-radius: 5px; }
        .card { border: none; shadow-sm; box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075); }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        
        <div class="col-md-2 sidebar p-3 collapse d-md-block" id="sidebarMenu">
            <h4 class="text-white mb-4"><i class="fas fa-box-open me-2"></i>Admin Panel</h4>
            <ul class="nav flex-column">
                <li class="nav-item mb-2">
                    <a href="#products" class="active"><i class="fas fa-cube me-2"></i>Manajemen Produk</a>
                </li>
                <li class="nav-item mb-2">
                    <a href="#orders"><i class="fas fa-shopping-cart me-2"></i>Order Masuk</a>
                </li>
                <li class="nav-item mt-4">
                    <a href="logout.php" class="text-danger"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
                </li>
            </ul>
        </div>

        <div class="col-md-10 ms-sm-auto px-md-4 py-4">
            
            <?php if(isset($success_msg)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $success_msg; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card mb-4" id="products">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Tambah Produk Baru</h5>
                </div>
                <div class="card-body">
                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Barang</label>
                                <input type="text" name="name" class="form-control" placeholder="Contoh: Sepatu Nike" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Harga (Rp)</label>
                                <input type="number" name="price" class="form-control" placeholder="Contoh: 150000" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="desc" class="form-control" rows="2" placeholder="Detail produk..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Foto Produk</label>
                            <input type="file" name="image" class="form-control" accept="image/*" required>
                        </div>
                        <button type="submit" name="submit_product" class="btn btn-success">
                            <i class="fas fa-upload me-1"></i> Upload Produk
                        </button>
                    </form>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Daftar Stok Barang</h5>
                    <input type="text" id="searchInput" class="form-control w-25" placeholder="Cari barang live..." onkeyup="searchProduct()">
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Gambar</th>
                                    <th>Nama Produk</th>
                                    <th>Harga</th>
                                    <th width="150">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="productTableBody">
                                <?php
                                $stmt = $productObj->readAll();
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    $price = number_format($row['price'], 0, ',', '.');
                                    echo "<tr id='row-{$row['id']}'>";
                                    echo "<td><img src='uploads/{$row['image']}' class='img-thumbnail' style='width: 60px; height: 60px; object-fit: cover;'></td>";
                                    echo "<td class='fw-bold'>{$row['name']}</td>";
                                    echo "<td>Rp {$price}</td>";
                                    echo "<td>
                                            <button class='btn btn-sm btn-danger' onclick='deleteProduct({$row['id']})'>
                                                <i class='fas fa-trash'></i> Hapus
                                            </button>
                                          </td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card" id="orders">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Order Masuk (Rekap)</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#ID Order</th>
                                <th>Nama Pembeli</th>
                                <th>Total Belanja</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmtOrder = $orderObj->getAllOrders();
                            while ($order = $stmtOrder->fetch(PDO::FETCH_ASSOC)) {
                                $total = number_format($order['total_amount'], 0, ',', '.');
                                $badge = $order['status'] == 'completed' ? 'bg-success' : 'bg-secondary';
                                
                                echo "<tr>";
                                echo "<td>#{$order['id']}</td>";
                                echo "<td>{$order['username']}</td>";
                                echo "<td>Rp {$total}</td>";
                                echo "<td><span class='badge {$badge}'>{$order['status']}</span></td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
<script>
function deleteProduct(id) {
    if(confirm('Yakin ingin menghapus barang ini secara permanen?')) {
        fetch('api/admin_api.php?action=delete&id=' + id)
        .then(response => response.json())
        .then(data => {
            if(data.status === 'success') {
                // Animasi fade out sebelum menghapus elemen
                let row = document.getElementById('row-' + id);
                row.style.opacity = '0';
                setTimeout(() => row.remove(), 500);
            } else {
                alert('Gagal menghapus data.');
            }
        });
    }
}

function searchProduct() {
    let keyword = document.getElementById('searchInput').value;
    let tbody = document.getElementById('productTableBody');

    fetch('api/admin_api.php?action=search&keyword=' + keyword)
    .then(response => response.json())
    .then(data => {
        let html = '';
        if(data.length > 0) {
            data.forEach(item => {
                // Format angka ke format Rupiah
                let price = new Intl.NumberFormat('id-ID').format(item.price);
                
                html += `<tr id='row-${item.id}'>
                            <td><img src='uploads/${item.image}' class='img-thumbnail' style='width: 60px; height: 60px; object-fit: cover;'></td>
                            <td class='fw-bold'>${item.name}</td>
                            <td>Rp ${price}</td>
                            <td>
                                <button class='btn btn-sm btn-danger' onclick='deleteProduct(${item.id})'>
                                    <i class='fas fa-trash'></i> Hapus
                                </button>
                            </td>
                         </tr>`;
            });
        } else {
            html = '<tr><td colspan="4" class="text-center text-muted py-3">Barang tidak ditemukan</td></tr>';
        }
        tbody.innerHTML = html;
    });
}
</script>

</body>
</html>