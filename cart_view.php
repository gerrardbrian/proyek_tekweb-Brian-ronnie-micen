<?php
require_once 'cart.php';
$cart = new Cart();
$items = $cart->getContent();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja | Lux Brand</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        body { background-color: #f8f9fa; font-family: 'Times New Roman', serif; }
        .table-cart th { background-color: #1a1a1a; color: #d4af37; border: none; }
        .btn-luxury { background-color: #000; color: #fff; border: 1px solid #000; }
        .btn-luxury:hover { background-color: #d4af37; color: #000; border-color: #d4af37; }
        .cart-img { width: 80px; height: 80px; object-fit: cover; border-radius: 5px; }

        /* --- 2. CSS Khusus untuk Meniru Tampilan Gambar --- */
        /* Mengubah font popup menjadi Serif agar mirip gambar */
        div:where(.swal2-container) .swal2-title {
            font-family: 'Times New Roman', serif !important;
            color: #555 !important; /* Warna abu gelap seperti di gambar */
            font-weight: bold;
            font-size: 2rem;
        }
        
        div:where(.swal2-container) .swal2-html-container {
            font-family: 'Times New Roman', serif !important;
            color: #666;
            font-size: 1.1rem;
        }

        /* Tombol OK menjadi Hitam (Luxury) */
        div:where(.swal2-container) button:where(.swal2-styled).swal2-confirm {
            background-color: #000 !important;
            color: #d4af37 !important;
            border-radius: 5px; 
            box-shadow: none !important;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand text-warning" href="index.php">LUXURY STORE</a>
            <a href="index.php" class="btn btn-outline-light btn-sm">Kembali Belanja</a>
        </div>
    </nav>

    <div class="container">
        <h2 class="mb-4 fw-bold">Shopping Cart</h2>

        <?php if (empty($items)): ?>
            
            <div class="alert alert-warning text-center p-5">
                <h4>Keranjang Anda Kosong</h4>
                <p>Belum ada barang mewah yang Anda pilih.</p>
                <a href="index.php" class="btn btn-dark mt-3">Mulai Belanja</a>
            </div>

        <?php else: ?>

            <div class="row">
                <div class="col-lg-8">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle bg-white shadow-sm rounded">
                            <thead class="table-cart">
                                <tr>
                                    <th>Produk</th>
                                    <th>Harga</th>
                                    <th>Jumlah</th>
                                    <th>Subtotal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): 
                                    $subtotal = $item['price'] * $item['qty'];
                                ?>
                                <tr id="row-<?php echo $item['id']; ?>">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="uploads/<?php echo $item['image']; ?>" class="cart-img me-3" alt="Img">
                                            <div>
                                                <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($item['name']); ?></h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                                    <td>
                                        <input type="number" min="1" class="form-control qty-input" style="width: 70px;" 
                                            value="<?php echo $item['qty']; ?>" 
                                            data-id="<?php echo $item['id']; ?>"
                                            data-price="<?php echo $item['price']; ?>">
                                    </td>
                                    <td class="fw-bold text-success">
                                        Rp <span id="subtotal-<?php echo $item['id']; ?>">
                                            <?php echo number_format($subtotal, 0, ',', '.'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-danger btn-sm btn-remove" data-id="<?php echo $item['id']; ?>">
                                            &times; Hapus
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card shadow-sm border-0 rounded-0">
                        <div class="card-header bg-black text-white fw-bold">
                            RINGKASAN BELANJA
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-3">
                                <span>Total Belanja</span>
                                <span class="fw-bold fs-5">
                                    Rp <span id="grand-total">
                                        <?php echo number_format($cart->getTotalSum(), 0, ',', '.'); ?>
                                    </span>
                                </span>
                            </div>
                            <hr>
                           <button id="btn-checkout" class="btn btn-luxury w-100 py-2 fw-bold">
                                CHECKOUT SEKARANG
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        <?php endif; ?>
    </div>

    <script>
    $(document).ready(function() {
        // Update Quantity (Tetap sama)
        $('.qty-input').on('change', function() {
            let id = $(this).data('id');
            let qty = $(this).val();
            let price = $(this).data('price');
        
            let rowSubtotal = price * qty;
            $('#subtotal-' + id).text(new Intl.NumberFormat('id-ID').format(rowSubtotal));

            $.ajax({
                url: 'api_cart.php',
                type: 'POST',
                data: { action: 'update', id: id, qty: qty },
                dataType: 'json',
                success: function(response) {
                    $('#grand-total').text(response.total_sum);
                }
            });
        });

        // Remove Item (Menggunakan SweetAlert konfirmasi hapus)
        $('.btn-remove').on('click', function() {
            let id = $(this).data('id');

            Swal.fire({
                title: 'Hapus Item?',
                text: "Barang ini akan dihapus dari keranjang.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#d33',
                cancelButtonColor: '#000'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'api_cart.php',
                        type: 'POST',
                        data: { action: 'remove', id: id },
                        dataType: 'json',
                        success: function(response) {
                            $('#row-' + id).fadeOut(300, function() { 
                                $(this).remove(); 
                                if(response.total_sum == 0) location.reload(); 
                            });
                            $('#grand-total').text(response.total_sum);
                        }
                    });
                }
            });
        });

        // --- 3. Checkout dengan Tampilan Sesuai Gambar ---
        $('#btn-checkout').on('click', function() {
            
            // Konfirmasi awal
            Swal.fire({
                title: 'Konfirmasi',
                text: "Apakah Anda yakin ingin menyelesaikan pembelian?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#000', // Hitam Luxury
                cancelButtonColor: '#666',
                confirmButtonText: 'Ya, Beli',
                cancelButtonText: 'Batal'
            }).then((result) => {
                
                if (result.isConfirmed) {
                    let btn = $('#btn-checkout');
                    let originalText = btn.text();
                    
                    // Tampilkan Loading
                    btn.prop('disabled', true).text('Memproses...');
                    Swal.fire({
                        title: 'Memproses...',
                        didOpen: () => { Swal.showLoading() }
                    });

                    $.ajax({
                        url: 'api_checkout.php',
                        type: 'POST',
                        dataType: 'json',
                        success: function(response){
                            if (response.status == 'success'){
                                // TAMPILAN SUKSES SESUAI GAMBAR
                                Swal.fire({
                                    icon: 'success', // Ini yang membuat ikon centang hijau animasi
                                    title: 'Terima Kasih', // Judul besar serif
                                    text: 'Pesanan Anda berhasil diproses!', // Text bawah
                                    confirmButtonText: 'OK',
                                    padding: '2em',
                                    customClass: {
                                        popup: 'animated fadeInDown' // Animasi halus
                                    }
                                }).then(() => {
                                    window.location.href = 'index.php';
                                });

                            } else {
                                // Tampilan Gagal
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: response.message,
                                    confirmButtonColor: '#000'
                                });
                                btn.prop('disabled', false).text(originalText);
                            }
                        },
                        error: function(){
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Terjadi kesalahan sistem.',
                                confirmButtonColor: '#000'
                            });
                            btn.prop('disabled', false).text(originalText);
                        }
                    });
                }
            });
        });
    });
    </script>
</body>
</html>