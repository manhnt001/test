<link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/assets/css/single-product.css" type="text/css">
<?php
defined('ABSPATH') || exit; // Đảm bảo file không bị truy cập trực tiếp
get_header(); // Lấy header của theme

// Bắt đầu vòng lặp để lấy thông tin sản phẩm
while (have_posts()) : the_post();
    global $product;
?>
    <div class="container my-5">
        <div class="row">
            <div class="col-md-6">
                <div class="product-image">
                    <?php echo woocommerce_get_product_thumbnail(); ?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="product-details">
                    <h1 class="product-title">Gói <?php the_title(); ?></h1>
                    <!-- Phần để hiển thị giá sản phẩm mà không kèm theo số ngày -->
                    <div class="product-price" id="product-price">
                        <?php echo $product->get_price_html(); ?> <!-- Chỉ hiển thị giá ban đầu -->
                    </div>
                    <div class="product-description mt-4">
                        <?php the_content(); ?>
                    </div>

                    <!-- Phần chọn loại SIM -->
                    <div class="sim-type box-content mt-4">
                        <h2 class="title">Loại hình SIM</h2>
                        <div class="sim-options d-flex">
                        <button class="btn btn-outline-primary sim-option" data-sim-type="0" aria-pressed="true"> <!--0: sim vật lý-->
    <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/image/svl.svg" alt="SIM Vật lý" style="width: 24px; height: auto; margin-right: 5px;">
    Sử dụng SIM Vật lý
    <img class="check-icon" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/image/check-checkout.svg" alt="check-checkout" style="display:none;">
</button>
<button class="btn btn-outline-primary sim-option ml-2" data-sim-type="1"> <!--1: esim -->
    <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/image/esim.svg" alt="eSIM" style="width: 24px; height: auto; margin-right: 5px;">
    Sử dụng eSIM
    <img class="check-icon" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/image/check-checkout.svg" alt="check-checkout" style="display:none;">
</button>

                        </div>
                    </div>

                    <?php
                    // Kiểm tra xem sản phẩm có phải là biến thể không
                    if ($product->is_type('variable')) {
                        // Lấy danh sách biến thể
                        $available_variations = $product->get_available_variations();
                        echo '<h2>Chọn gói cước:</h2>';
                        echo '<div class="d-flex variations-buttons mb-4">';

                        foreach ($available_variations as $variation) {
                            $variation_id = $variation['variation_id'];
                            $attributes = $variation['attributes'];
                            $variation_name = '';
                            $month_value = 0;

                            // Xử lý tên biến thể từ các thuộc tính
                            foreach ($attributes as $attribute_name => $attribute_value) {
                                $attribute_value = str_replace('-thang', ' tháng', $attribute_value);

                                if (preg_match('/(\d+) tháng/', $attribute_value, $matches)) {
                                    $month_value = intval($matches[1]);
                                    $variation_name = $month_value . ' tháng'; // Chỉ hiển thị số tháng
                                }
                            }

                            // Hiển thị mỗi gói cước dưới dạng button
                            echo '<div class="variation-item">'; // Giãn cách các nút
                            echo '<button class="btn btn-outline-primary variation-select" data-variation-id="' . esc_attr($variation_id) . '" data-price="' . esc_attr($variation['display_price']) . '" data-months="' . esc_attr($month_value) . '">';
                            echo esc_html($variation_name); // Hiển thị số tháng
                            echo '<div class="variation-price">' . wc_price($variation['display_price']) . '</div>'; // Hiển thị giá bên dưới nút
                            echo '</button>';
                            echo '</div>';
                        }
                        echo '</div>';
                    }
                    ?>

                    <!-- Nút Thêm vào giỏ hàng luôn hiển thị từ đầu -->
                    <button id="add-to-cart-button" class="btn btn-primary">Thêm vào giỏ hàng</button>
                    <!-- Popup Đánh Giá -->
<div id="review-popup" class="review-popup" style="display:none;">
    <div class="review-popup-content">
        <span class="close">&times;</span>
        <h2>Đánh giá sản phẩm</h2>
        <form id="review-form">
            <label for="review-name">Tên của bạn:</label>
            <input type="text" id="review-name" required>

            <label for="review-rating">Đánh giá:</label>
            <select id="review-rating" required>
                <option value="">Chọn đánh giá</option>
                <option value="1">1 sao</option>
                <option value="2">2 sao</option>
                <option value="3">3 sao</option>
                <option value="4">4 sao</option>
                <option value="5">5 sao</option>
            </select>

            <label for="review-comment">Nhận xét:</label>
            <textarea id="review-comment" rows="4" required></textarea>

            <button type="submit">Gửi đánh giá</button>
        </form>
    </div>
</div>



                </div>
            </div>
        </div>
    </div>

<?php
endwhile;
get_footer();
?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let selectedVariationID = null;
        const addToCartButton = document.getElementById('add-to-cart-button');
        const productPrice = document.getElementById('product-price');

        // Xử lý sự kiện cho các nút loại SIM
        document.querySelectorAll('.sim-option').forEach(button => {
            button.addEventListener('click', function() {
                // Bỏ chọn tất cả các nút và ẩn icon
                document.querySelectorAll('.sim-option').forEach(btn => {
                    btn.classList.remove('active');
                    btn.querySelector('.check-icon').style.display = 'none'; // Ẩn icon khi không được chọn
                });

                // Đánh dấu nút hiện tại là đang được chọn và hiển thị icon
                this.classList.add('active');
                this.querySelector('.check-icon').style.display = 'inline'; // Hiển thị icon cho nút được chọn
                
                // Thực hiện hành động khác nếu cần (ví dụ: lưu loại SIM đã chọn)
                const selectedSimType = this.getAttribute('data-sim-type');
                console.log('Loại SIM đã chọn:', selectedSimType);
            });
        });

        // Lấy tất cả nút chọn gói cước và gắn sự kiện click cho chúng
        document.querySelectorAll('.variation-select').forEach(button => {
            button.addEventListener('click', function() {
                // Bỏ chọn nút trước đó (nếu có)
                document.querySelectorAll('.variation-select').forEach(btn => btn.classList.remove('active'));
                // Đánh dấu nút hiện tại là đang được chọn
                this.classList.add('active');

                // Lấy ID, giá và số tháng của biến thể đã chọn
                selectedVariationID = this.getAttribute('data-variation-id');
                const selectedPrice = parseFloat(this.getAttribute('data-price'));
                const monthValue = parseInt(this.getAttribute('data-months'));
                const days = monthValue * 30; // Quy đổi tháng sang ngày

                // Cập nhật giá trên giao diện với giá biến thể đã chọn và số ngày
                productPrice.innerHTML = new Intl.NumberFormat('vi-VN', {
                    style: 'currency',
                    currency: 'VND'
                }).format(selectedPrice) + ' /' + days + ' ngày';
            });
        });

        // Xử lý sự kiện click của nút "Thêm vào giỏ hàng"
        addToCartButton.addEventListener('click', function() {
            if (selectedVariationID) {
                // Gửi AJAX request để thêm biến thể vào giỏ hàng
                fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            action: 'add_variation_to_cart',
                            variation_id: selectedVariationID,
                            product_id: '<?php echo $product->get_id(); ?>'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.data.message); // Hiển thị thông báo thành công
                        } else {
                            alert(data.data.message); // Hiển thị thông báo lỗi nếu có
                        }
                    })
                    .catch(error => {
                        console.error('AJAX error:', error);
                        alert('Có lỗi xảy ra khi thêm vào giỏ hàng.');
                    });
            } else {
                alert('Vui lòng chọn một gói cước trước.');
            }
        });

        // Xử lý popup đánh giá
        const reviewPopup = document.getElementById('review-popup');
        const closeBtn = reviewPopup.querySelector('.close');

        // Hiện popup khi người dùng nhấp vào nút đánh giá
        addToCartButton.addEventListener('click', function() {
            reviewPopup.style.display = 'flex';
        });

        // Đóng popup khi nhấp vào nút đóng
        closeBtn.addEventListener('click', function() {
            reviewPopup.style.display = 'none';
        });

        // Đóng popup khi nhấp ra ngoài vùng popup
        window.addEventListener('click', function(event) {
            if (event.target == reviewPopup) {
                reviewPopup.style.display = 'none';
            }
        });

        // Gửi đánh giá
        document.getElementById('review-form').addEventListener('submit', function(event) {
            event.preventDefault();
            const name = document.getElementById('review-name').value;
            const rating = document.getElementById('review-rating').value;
            const comment = document.getElementById('review-comment').value;

            // Xử lý gửi đánh giá qua AJAX (cần tạo endpoint xử lý phía server)
            fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'submit_product_review',
                    name: name,
                    rating: rating,
                    comment: comment,
                    product_id: '<?php echo $product->get_id(); ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Cảm ơn bạn đã gửi đánh giá!');
                    reviewPopup.style.display = 'none'; // Đóng popup

                    // Tải lại trang sau khi gửi đánh giá thành công
                    window.location.reload(); // Tải lại trang sau khi đánh giá thành công
                } else {
                    alert('Đã có lỗi xảy ra: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi gửi đánh giá.');
            });
        });
    });
</script>
