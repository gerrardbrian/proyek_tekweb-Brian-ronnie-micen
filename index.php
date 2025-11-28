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
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top p-3">
        <div class="container">
            <a class="navbar-brand text-warning" href="#">LUXURY STORE</a>
            <div class="d-flex">
                <a href="cart_view.php" class="btn btn-outline-warning position-relative">
                    🛒 Keranjang
                    <span id="cart-count" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?php 
                            require_once 'cart.php'; 
                            $c = new Cart(); 
                            echo $c->totalItems(); 
                        ?>
                    </span>
                </a>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <h2 class="text-center mb-4">Exclusive Collection</h2>
        
        <div class="row" id="product-grid">
            <div class="text-center"><div class="spinner-border" role="status"></div></div>
        </div>

        <nav class="mt-4">
            <ul class="pagination justify-content-center" id="pagination-links">
                </ul>
        </nav>
    </div>

    <script>
    $(document).ready(function() {
        
        // --- LOGIKA 1: Load Produk & Pagination (AJAX) ---
        function loadProducts(page) {
            $.ajax({
                url: 'api_catalog.php',
                type: 'GET',
                data: { page: page },
                dataType: 'json',
                success: function(response) {
                    let html = '';
                    
                    // Render Produk
                    $.each(response.products, function(i, product) {
                        // Format Rupiah
                        let price = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(product.price);
                        
                        html += `
                        <div class="col-md-4 mb-4">
                            <div class="card product-card h-100">
                                <img src="uploads/${product.image}" class="card-img-top" style="height:250px; object-fit:cover;">
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
                    $('#product-grid').html(html);

                    // Render Pagination
                    let paginationHtml = '';
                    for (let i = 1; i <= response.totalPages; i++) {
                        let active = (i == response.currentPage) ? 'active' : '';
                        paginationHtml += `<li class="page-item ${active}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
                    }
                    $('#pagination-links').html(paginationHtml);
                }
            });
        }

        // Load halaman 1 saat pertama buka
        loadProducts(1);

        // Handle klik Pagination
        $(document).on('click', '.page-link', function(e) {
            e.preventDefault();
            let page = $(this).data('page');
            loadProducts(page);
        });

        // --- LOGIKA 2: Add to Cart (AJAX) ---
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
                        // Update angka di navbar tanpa reload
                        $('#cart-count').text(res.total_qty);
                        
                        // Efek visual tombol
                        let originalText = btn.text();
                        btn.removeClass('btn-dark').addClass('btn-success').text('Added!');
                        setTimeout(() => {
                            btn.removeClass('btn-success').addClass('btn-dark').text(originalText);
                        }, 1000);
                    }
                }
            });
        });

    });
    </script>
</body>
</html>