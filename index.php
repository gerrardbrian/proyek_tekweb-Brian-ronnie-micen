<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Luxury Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .product-card { transition: transform 0.3s; border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .product-card:hover { transform: translateY(-5px); }
        .navbar-brand { font-family: 'Times New Roman', serif; font-weight: bold; }
        
        .search-container { width: 40%; }
        @media (max-width: 768px) { .search-container { width: 100%; margin: 10px 0; } }
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top p-3">
        <div class="container">
            <a class="navbar-brand text-warning" href="#">LUXURY STORE</a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                
                <div class="mx-auto search-container">
                    <input class="form-control" type="text" id="search-input" placeholder="Cari koleksi exclusive...">
                </div>

                <div class="d-flex gap-2">
                    <a href="cart_view.php" class="btn btn-outline-warning position-relative">
                        🛒 Keranjang
                        <span id="cart-count" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?php 
                                // Pastikan file cart.php ada dan class Cart sudah dibuat
                                if(file_exists('cart.php')){
                                    require_once 'cart.php'; 
                                    // Cek apakah class cart ada biar ga error
                                    if(class_exists('cart')){
                                        $c = new cart(); 
                                        echo $c->totalItems(); 
                                    } else { echo "0"; } //biar angka di atas keranjang itu 0
                                } else { echo "0"; } //biar angka di atas keranjang itu 0
                            ?>
                        </span>
                    </a>
                    
                    <a href="login.html" class="btn btn-danger" onclick="return confirm('Yakin ingin keluar?');">
                        Logout
                    </a>
                </div>  
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <h2 class="text-center mb-4">Exclusive Collection</h2>
        
        <div class="row" id="product-grid">
            <div class="text-center mt-5"><div class="spinner-border" role="status"></div></div>
        </div>

        <nav class="mt-4">
            <ul class="pagination justify-content-center" id="pagination-links">
            </ul>
        </nav>
    </div>

    <script>
    $(document).ready(function() {

        // buat menyimpan kata kunci pencarian
        var currentKeyword = ''; 
        
        // Load Produk ---
        function loadProducts(page, search = '') { // search kosong jika tidak dikirim
            
            // Tampilkan loading sebelum data muncul
            $.ajax({
                url: 'api_catalog.php',
                type: 'GET',
                data: { 
                    page: page,
                    search: search 
                },
                dataType: 'json',
                success: function(response) {
                    let html = '';
                    
                    //Cek 'products' ada dan array-nya tidak kosong
                    if(response.products && response.products.length > 0){
                
                        // Loop data produk
                        $.each(response.products, function(i, product) {
                            // Format harga ke Rupiah
                            let price = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(product.price);
                            
                            html += `
                            <div class="col-md-4 mb-4">
                                <div class="card product-card h-100">
                                    <img src="uploads/${product.image}" class="card-img-top" style="height:250px; object-fit:cover;" alt="${product.name}">
                                    <div class="card-body text-center">
                                        <h5 class="card-title">${product.name}</h5>
                                        <p class="card-text text-warning fw-bold">${price}</p>
                                        <button class="btn btn-dark w-100 add-to-cart" 
                                            data-id="${product.id}" 
                                            data-name="${product.name}" 
                                            data-price="${product.price}" 
                                            data-image="${product.image}">
                                            Add to Cart
                                        </button>
                                    </div>
                                </div>
                            </div>`;
                        });
                    } else {
                        // Jika tidak ada barang / hasil search tidak di temukan
                        html = '<div class="col-12 text-center text-muted py-5"><h4>Produk tidak ditemukan.</h4></div>';
                    }

                    // Masukkan HTML ke div
                    $('#product-grid').html(html);

                    // Render Tombol Pagination
                    let paginationHtml = '';
                    if(response.totalPages) {
                        for (let i = 1; i <= response.totalPages; i++) {
                            let active = (i == response.currentPage) ? 'active' : '';
                            // Penting: Link pagination tidak href, tapi pakai data-page
                            paginationHtml += `<li class="page-item ${active}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
                        }
                    }
                    $('#pagination-links').html(paginationHtml);
                },
                error: function(xhr, status, error) {
                    console.log("Error:", error);
                    $('#product-grid').html('<div class="col-12 text-center text-danger">Gagal memuat data. Cek Console (F12) & Network tab.</div>');
                }
            });
        }

        //Load halaman pertama saat website dibuka
        loadProducts(1, '');

        //event listener: Search (Saat mengetik)
        $('#search-input').on('keyup', function() {
            currentKeyword = $(this).val(); // Simpan kata kunci ke variabel global
            loadProducts(1, currentKeyword); // Reset ke halaman 1 dengan kata kunci baru
        });

        // event listener klik pagination
        $(document).on('click', '.page-link', function(e) {
            e.preventDefault(); // Mencegah scroll ke atas
            let page = $(this).data('page');
            
            // loadProducts dengan halaman baru DAN kata kunci yang sedang aktif
            loadProducts(page, currentKeyword);
        });

        // event listener: Add to Cart
        $(document).on('click', '.add-to-cart', function() {
            let btn = $(this);
            
            $.ajax({
                url: 'api_cart.php',
                type: 'POST',
                data: {
                    action: 'add',
                    id: btn.data('id'),
                    name: btn.data('name'),
                    price: btn.data('price'),
                    image: btn.data('image')
                },
                dataType: 'json',
                success: function(res) {
                    if(res.status === 'success') {
                        // update angka di navbar
                        $('#cart-count').text(res.total_qty);
                        
                        // efek tombol berubah jadi hijau sebentar
                        let originalText = btn.text();
                        btn.removeClass('btn-dark').addClass('btn-success').text('Added!');
                        setTimeout(() => {
                            btn.removeClass('btn-success').addClass('btn-dark').text(originalText);
                        }, 1000); // berubah warna hijau 1 detik
                    }
                },
                error: function() {
                    alert('Gagal menambah ke keranjang');
                }
            });
        });

    });
    </script>
</body>
</html>