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
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .product-card { transition: transform 0.3s; border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .product-card:hover { transform: translateY(-5px); }
        .navbar-brand { font-family: 'Times New Roman', serif; font-weight: bold; }
        
        .search-container { width: 40%; }
        @media (max-width: 768px) { .search-container { width: 100%; margin: 10px 0; } }
        
        div:where(.swal2-container) button:where(.swal2-styled).swal2-confirm {
            background-color: #dc3545 !important;
        }

        /* cursor pointer biar user tahu gambar bisa diklik */
        .view-details { cursor: pointer; }

        /* biar overlay 'HABIS' tidak memblokir klik pada gambar */
        .sold-out-overlay { pointer-events: none; }
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
                                if(file_exists('cart.php')){
                                    require_once 'cart.php'; 
                                    if(class_exists('cart')){
                                        $c = new cart(); 
                                        echo $c->totalItems(); 
                                    } else { echo "0"; } 
                                } else { echo "0"; } 
                            ?>
                        </span>
                    </a>
                    
                    <a href="login.html" id="btn-logout" class="btn btn-danger">
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

    <div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalName">Detail Produk</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="" id="modalImage" class="img-fluid rounded mb-3" style="max-height: 300px; object-fit: cover;">
                    <h4 class="text-warning fw-bold mb-3" id="modalPrice">Rp 0</h4>
                    <p class="text-muted text-start px-3" id="modalDesc">Deskripsi produk...</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-dark" id="modalAddToCart">Add to Cart</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    $(document).ready(function() {

        // alert Logout
        $('#btn-logout').on('click', function(e) {
            e.preventDefault(); 
            var href = $(this).attr('href'); 

            Swal.fire({
                title: 'Yakin ingin keluar?',
                text: "Sesi Anda akan diakhiri.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Logout!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            });
        });

        var currentKeyword = ''; 
        
        function loadProducts(page, search = '') { 
            
            $.ajax({
                url: 'api_catalog.php',
                type: 'GET',
                data: { page: page, search: search },
                dataType: 'json',
                success: function(response) {
                    let html = '';
                    
                    if(response.products && response.products.length > 0){
                        $.each(response.products, function(i, product) {
                            let price = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(product.price);
                            
                            // kalo ga ada deskripsi kasih teks default ne
                            let desc = product.description ? product.description.replace(/"/g, '&quot;') : 'Tidak ada deskripsi.';

                            let buttonHtml = '';
                            let imageOverlay = '';

                            if (product.stock > 0){
                                buttonHtml =`
                                <button class="btn btn-dark w-100 add-to-cart" 
                                        data-id="${product.id}" 
                                        data-name="${product.name}" 
                                        data-price="${product.price}" 
                                        data-image="${product.image}">
                                        Add to Cart
                                </button>`;
                            }else{
                                buttonHtml=`
                                <button class="btn btn-secondary w-100" disabled style="cursor: not-allowed;">
                                        SOLD OUT
                                </button>`;
                                
                                // Tambahkan class 'sold-out-overlay' agar klik tembus ke gambar
                                imageOverlay=`
                                <div class="position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center sold-out-overlay" 
                                     style="background: rgba(0,0,0,0.5); color: white; font-weight: bold; letter-spacing: 2px;">
                                     HABIS
                                </div>`;
                            }

                            html += `
                            <div class="col-md-4 mb-4">
                                <div class="card product-card h-100 position-relative">
                                    <div class="position-relative">
                                        <img src="uploads/${product.image}" class="card-img-top view-details" 
                                             style="height:250px; object-fit:cover;" 
                                             alt="${product.name}"
                                             data-name="${product.name}"
                                             data-desc="${desc}" 
                                             data-price="${price}"
                                             data-image="${product.image}"
                                             data-id="${product.id}"
                                             data-stock="${product.stock}"
                                             data-rawprice="${product.price}"
                                        >
                                        ${imageOverlay} 
                                    </div>
                                    <div class="card-body text-center">
                                        <h5 class="card-title">${product.name}</h5>
                                        <div class="d-flex justify-content-center align-items-center mb-2 px-3">
                                            <p class="card-text text-warning fw-bold mb-0 fs-5">${price}</p>
                                        </div>
                                        ${buttonHtml}
                                    </div>
                                </div>
                            </div>`;
                        });
                    } else {
                        html = '<div class="col-12 text-center text-muted py-5"><h4>Produk tidak ditemukan.</h4></div>';
                    }

                    $('#product-grid').html(html);

                    // pagination
                    let paginationHtml = '';
                    if(response.totalPages) {
                        for (let i = 1; i <= response.totalPages; i++) {
                            let active = (i == response.currentPage) ? 'active' : '';
                            paginationHtml += `<li class="page-item ${active}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
                        }
                    }
                    $('#pagination-links').html(paginationHtml);
                },
                error: function(xhr, status, error) {
                    console.log("Error:", error);
                    // cek kalo error ke console
                    console.log(xhr.responseText); 
                    $('#product-grid').html('<div class="col-12 text-center text-danger">Gagal memuat data.</div>');
                }
            });
        }

        loadProducts(1, '');

        $('#search-input').on('keyup', function() {
            currentKeyword = $(this).val(); 
            loadProducts(1, currentKeyword); 
        });

        $(document).on('click', '.page-link', function(e) {
            e.preventDefault(); 
            let page = $(this).data('page');
            loadProducts(page, currentKeyword);
        });

        // klik gambar untuk detail modal
        $(document).on('click', '.view-details', function() {
            console.log('Gambar diklik!'); 

            try {
                let name = $(this).data('name');
                let desc = $(this).data('desc');
                let price = $(this).data('price');
                let image = $(this).data('image');
                let id = $(this).data('id');
                let rawPrice = $(this).data('rawprice');
                let stock = $(this).data('stock');

                // isi dri modal
                $('#modalName').text(name);
                $('#modalDesc').text(desc);
                $('#modalPrice').text(price);
                $('#modalImage').attr('src', 'uploads/' + image);

                // tombol Add to Cart yg ada di modalnya
                let modalBtn = $('#modalAddToCart');
                modalBtn.data('id', id);
                modalBtn.data('name', name);
                modalBtn.data('price', rawPrice);
                modalBtn.data('image', image);

                //kalo ada stock aktifin tombolnya, kalo ga ya disable lalu ada soldout
                if(stock > 0) {
                    modalBtn.prop('disabled', false).text('Add to Cart').addClass('add-to-cart');
                } else {
                    modalBtn.prop('disabled', true).text('SOLD OUT').removeClass('add-to-cart');
                }

                // Tampilkan Modal
                var myModal = new bootstrap.Modal(document.getElementById('productModal'));
                myModal.show();
            } catch (error) {
                console.error("Error menampilkan modal:", error);
                alert("Terjadi kesalahan saat membuka detail produk.");
            }
        });

        //  add to cart
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
                    //setelah kirim ke backend, dan sukses, update jumlah di icon keranjang
                    if(res.status === 'success') {
                        $('#cart-count').text(res.total_qty);
                        let originalText = btn.text();
                        //trs ubah button sementara jadi  added
                        btn.removeClass('btn-dark').addClass('btn-success').text('Added!');
                        setTimeout(() => {
                            //setelah 1 detik balik ke semula
                            btn.removeClass('btn-success').addClass('btn-dark').text(originalText);
                        }, 1000); 
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