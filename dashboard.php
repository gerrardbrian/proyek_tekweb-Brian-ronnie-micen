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

$swal_alert = ''; // Variabel untuk menampung script SweetAlert dari PHP

// Handle Form Submit (Tambah Produk)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_product'])) {
    if(!empty($_FILES['image']['name'])) {
        if($productObj->create($_POST['name'], $_POST['price'], $_POST['desc'], $_FILES['image'])) {
            // BERHASIL: Siapkan SweetAlert Sukses
            $swal_alert = "
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Produk berhasil ditambahkan!',
                        confirmButtonColor: '#000'
                    });
                });
            </script>";
        } else {
            // GAGAL: Siapkan SweetAlert Error
            $swal_alert = "
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Gagal upload gambar atau simpan database.',
                        confirmButtonColor: '#000'
                    });
                });
            </script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Lux Brand</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        body { background-color: #f8f9fa; font-family: 'Times New Roman', serif; }
        .sidebar { min-height: 100vh; background-color: #1a1a1a; color: white; }
        .sidebar a { color: #d4af37; text-decoration: none; padding: 10px; display: block; transition: 0.3s; }
        .sidebar a:hover { background-color: #333; color: #fff; border-radius: 5px; padding-left: 15px; }
        .sidebar .active { background-color: #d4af37; color: #000; border-radius: 5px; font-weight: bold; }
        .card { border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .btn-luxury { background-color: #000; color: #d4af37; border: 1px solid #d4af37; font-weight: bold; }
        .btn-luxury:hover { background-color: #d4af37; color: #000; }

        /* Custom Style SweetAlert (Tema Luxury) */
        div:where(.swal2-container) .swal2-title { font-family: 'Times New Roman', serif !important; color: #333; }
        div:where(.swal2-container) button.swal2-confirm { background-color: #000 !important; color: #d4af37 !important; border: none; }
        div:where(.swal2-container) button.swal2-cancel { background-color: #d33 !important; color: #fff !important; }
    </style>
</head>
<body>

<?php echo $swal_alert; ?>

<div class="container-fluid">
    <div class="row">
        
        <div class="col-md-2 sidebar p-3 collapse d-md-block" id="sidebarMenu">
            <h4 class="text-warning mb-4 text-center fw-bold">LUX ADMIN</h4>
            <ul class="nav flex-column">
                <li class="nav-item mb-2">
                    <a href="#products" class="active"><i class="fas fa-cube me-2"></i>Manajemen Produk</a>
                </li>
                <li class="nav-item mb-2">
                    <a href="#orders"><i class="fas fa-shopping-cart me-2"></i>Order Masuk</a>
                </li>
                <li class="nav-item mt-5">
                    <a href="logout.php" onclick="confirmLogout(event)" class="text-danger fw-bold">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                    </a>
                </li>
            </ul>
        </div>

        <div class="col-md-10 ms-sm-auto px-md-4 py-4">
            
            <div class="card mb-4" id="products">
                <div class="card-header bg-black text-white">
                    <h5 class="mb-0 text-warning">Tambah Produk Baru</h5>
                </div>
                <div class="card-body">
                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Nama Barang</label>
                                <input type="text" name="name" class="form-control" placeholder="Contoh: Sepatu Nike" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Harga (Rp)</label>
                                <input type="number" name="price" class="form-control" placeholder="Contoh: 150000" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Deskripsi</label>
                            <textarea name="desc" class="form-control" rows="2" placeholder="Detail produk..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Foto Produk</label>
                            <input type="file" name="image" class="form-control" accept="image/*" required>
                        </div>
                        <button type="submit" name="submit_product" class="btn btn-luxury w-100 py-2">
                            <i class="fas fa-upload me-1"></i> UPLOAD PRODUK
                        </button>
                    </form>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">Daftar Stok Barang</h5>
                    <input type="text" id="searchInput" class="form-control w-25" placeholder="Cari barang live..." onkeyup="searchProduct()">
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-dark text-warning">
                                <tr>
                                    <th>Gambar</th>
                                    <th>Nama Produk</th>
                                    <th>Harga</th>
                                    <th>Stok</th> <th width="200">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="productTableBody">
                                <?php
                                // Asumsi method readAll() mengambil semua kolom termasuk 'stock'
                                $stmt = $productObj->readAll(); 
                                // Jika pakai fetchAll di product.php:
                                foreach ($stmt as $row) {
                                    $price = number_format($row['price'], 0, ',', '.');
                                    // Default stock 0 jika null
                                    $stock = isset($row['stock']) ? $row['stock'] : 0; 

                                    echo "<tr id='row-{$row['id']}'>";
                                    echo "<td><img src='uploads/{$row['image']}' class='img-thumbnail' style='width: 60px; height: 60px; object-fit: cover;'></td>";
                                    echo "<td class='fw-bold'>{$row['name']}</td>";
                                    echo "<td>Rp {$price}</td>";
                                    echo "<td><span id='stock-display-{$row['id']}'>{$stock}</span> pcs</td>";
                                    echo "<td>
                                            <button class='btn btn-sm btn-primary me-1' onclick='updateStock({$row['id']}, {$stock})'>
                                                <i class='fas fa-edit'></i> Stok
                                            </button>
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
                    <h5 class="mb-0 fw-bold"><i class="fas fa-clipboard-list me-2"></i>Order Masuk (Rekap)</h5>
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
                            // Jika getAllOrders return PDOStatement
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>

// --- FUNGSI LOGOUT (SweetAlert) ---
function confirmLogout(event) {
    event.preventDefault();
    Swal.fire({
        title: 'Yakin ingin keluar?',
        text: "Sesi Anda akan berakhir.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#000',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Logout',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'logout.php';
        }
    });
}

// --- FUNGSI HAPUS PRODUK (SweetAlert) ---
function deleteProduct(id) {
    // Ganti confirm() bawaan dengan Swal.fire
    Swal.fire({
        title: 'Hapus Permanen?',
        text: "Data produk ini tidak bisa dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#000',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Hapus'
    }).then((result) => {
        if (result.isConfirmed) {
            
            // Proses Delete via AJAX
            fetch('api/admin_api.php?action=delete&id=' + id)
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Terhapus!',
                        text: 'Produk berhasil dihapus.',
                        confirmButtonColor: '#000'
                    });
                    
                    // Efek menghapus baris tabel
                    let row = document.getElementById('row-' + id);
                    row.style.transition = 'all 0.5s';
                    row.style.opacity = '0';
                    setTimeout(() => row.remove(), 500);
                } else {
                    Swal.fire('Gagal', 'Gagal menghapus data.', 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
            });
        }
    });
}

// --- FUNGSI SEARCH LIVE ---
function searchProduct() {
    let keyword = document.getElementById('searchInput').value;
    let tbody = document.getElementById('productTableBody');

    fetch('api/admin_api.php?action=search&keyword=' + keyword)
    .then(response => response.json())
    .then(data => {
        let html = '';
        if(data.length > 0) {
            data.forEach(item => {
                let price = new Intl.NumberFormat('id-ID').format(item.price);
                html += `<tr id='row-${item.id}'>
                            <td><img src='uploads/${item.image}' class='img-thumbnail' style='width: 60px; height: 60px; object-fit: cover;'></td>
                            <td class='fw-bold'>${item.name}</td>
                            <td>Rp ${price}</td>
                            <td><span id='stock-display-${item.id}'>${stock}</span> pcs</td>
                            <td>
                                <button class='btn btn-sm btn-primary me-1' onclick='updateStock(${item.id}, ${stock})'>
                                    <i class='fas fa-edit'></i> Stok
                                </button>
                                <button class='btn btn-sm btn-danger' onclick='deleteProduct(${item.id})'>
                                    <i class='fas fa-trash'></i> Hapus
                                </button>
                            </td>
                         </tr>`;
            });
        } else {
            html = '<tr><td colspan="5" class="text-center text-muted py-3">Barang tidak ditemukan</td></tr>';
        }
        tbody.innerHTML = html;
    });
}
</script>

</body>
</html>