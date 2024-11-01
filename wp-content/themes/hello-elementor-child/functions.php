<?php
function hello_elementor_child_enqueue_styles() {
    wp_enqueue_style( 'hello-elementor-parent', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'hello-elementor-child', get_stylesheet_directory_uri() . '/style.css', array( 'hello-elementor-parent' ) );

    wp_enqueue_script( 'jquery' ); 
    if (!is_admin()) {
        wp_deregister_script('jquery');
        wp_enqueue_script('jquery', 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js', array(), null, true);
    }
    wp_enqueue_script('custom-ajax', get_stylesheet_directory_uri() . '/assets/js/custom-ajax.js', array('jquery'), null, true);
    
}
add_action( 'wp_enqueue_scripts', 'hello_elementor_child_enqueue_styles' );


add_action('admin_post_process_checkout', 'handle_checkout_payment');
add_action('admin_post_nopriv_process_checkout', 'handle_checkout_payment');

function handle_checkout_payment() {
    if (!isset($_POST['checkout_nonce']) || !wp_verify_nonce($_POST['checkout_nonce'], 'checkout_payment')) {
        wp_send_json_error('Nonce verification failed');
        wp_die();
    }

    $products = isset($_POST['products']) ? $_POST['products'] : [];
    global $wpdb;

    // Lấy dữ liệu từ form
    $province = isset($_POST['province']) ? sanitize_text_field($_POST['province']) : '';
    $district = isset($_POST['district']) ? sanitize_text_field($_POST['district']) : '';
    $ward = isset($_POST['ward']) ? sanitize_text_field($_POST['ward']) : '';
    $detailed_address = isset($_POST['detailed_address']) ? sanitize_text_field($_POST['detailed_address']) : '';
    $sim_type = isset($_POST['sim_type']) ? sanitize_text_field($_POST['sim_type']) : '';
    $payment_method = isset($_POST['payment_method']) ? sanitize_text_field($_POST['payment_method']) : '';
    $customer_name = isset($_POST['customer_name']) ? sanitize_text_field($_POST['customer_name']) : '';
    $customer_phone = isset($_POST['customer_phone']) ? sanitize_text_field($_POST['customer_phone']) : '';

    // Tên bảng
    $table_name = 'wp_esim_orders';
    $order_inserted = false;

    foreach ($products as $product) {
        $sim_id = sanitize_text_field($product['sim_id']);
        $goicuoc_id = sanitize_text_field($product['goicuoc_id']);
        $chuky = sanitize_text_field($product['chuky']);

        
        // Lấy thông tin sản phẩm từ DB
        $sim_product = wc_get_product($sim_id);
        $goicuoc_product = wc_get_product($goicuoc_id);

        if ($sim_product && $goicuoc_product) {
            // Dữ liệu cần lưu cho mỗi cặp sản phẩm
            $data = [
                // 'created_date' => current_time('mysql'),
                'customer_name' => $customer_name,
                'customer_phone' => $customer_phone,
                'customer_add' => $detailed_address . ', ' . $ward . ', ' . $district . ', ' . $province,
                'sim_id' => $sim_id,
                'sim_price' => $sim_product->get_price(),
                'goicuoc_id' => $goicuoc_id,
                'goicuoc_price' => $goicuoc_product->get_price(),
                'sim_type' => $sim_type,
                'package_cycle' => $chuky, 
                'sim_priceShip' => 0,
                'total_price' => $sim_product->get_price() + $goicuoc_product->get_price(),
                'sales_channel' => 'website',
            ];

            $result = $wpdb->insert($table_name, $data);

            if ($result !== false) {
                $order_inserted = true;
            }
        }
    }

    if ($order_inserted) {
        wp_send_json_success('Insert successful.');
    } else {
        $error = $wpdb->last_error;
        wp_send_json_error('Insert failed: ' . $error);
    }
    wp_die();
}

// Hàm xử lý thêm sản phẩm biến thể vào giỏ hàng qua AJAX
function add_variation_to_cart_ajax() {
    // Kiểm tra tham số `variation_id` và `product_id` có hợp lệ
    if (isset($_POST['variation_id']) && isset($_POST['product_id'])) {
        $variation_id = absint($_POST['variation_id']);
        $product_id = absint($_POST['product_id']);
        
        // Kiểm tra nếu sản phẩm có trong giỏ hàng
        $added = WC()->cart->add_to_cart($product_id, 1, $variation_id);
        
        if ($added) {
            // Trả về kết quả thành công nếu thêm vào giỏ hàng thành công
            wp_send_json_success(['message' => 'Sản phẩm đã được thêm vào giỏ hàng!']);
        } else {
            wp_send_json_error(['message' => 'Lỗi khi thêm sản phẩm vào giỏ hàng.']);
        }
    } else {
        wp_send_json_error(['message' => 'Dữ liệu không hợp lệ.']);
    }
}

// Đăng ký AJAX cho người dùng đã đăng nhập và khách
add_action('wp_ajax_add_variation_to_cart', 'add_variation_to_cart_ajax');
add_action('wp_ajax_nopriv_add_variation_to_cart', 'add_variation_to_cart_ajax');

add_action('wp_ajax_submit_product_review', 'submit_product_review');
add_action('wp_ajax_nopriv_submit_product_review', 'submit_product_review');

function submit_product_review() {
    // Kiểm tra nonce và xác thực dữ liệu nếu cần
    $name = sanitize_text_field($_POST['name']);
    $rating = intval($_POST['rating']);
    $comment = sanitize_textarea_field($_POST['comment']);
    $product_id = intval($_POST['product_id']);

    // Lưu đánh giá vào cơ sở dữ liệu (ví dụ sử dụng wp_insert_comment)
    $data = array(
        'comment_post_ID' => $product_id,
        'comment_author' => $name,
        'comment_content' => $comment,
        'comment_type' => 'product_review',
        'comment_approved' => 1,
        'meta_value' => $rating, // Có thể lưu đánh giá vào meta
    );

    $comment_id = wp_insert_comment($data);

    if ($comment_id) {
        // Nếu cần, bạn có thể thêm mã để lưu thêm meta hoặc thông tin
        wp_send_json_success(array('message' => 'Đánh giá đã được gửi thành công.'));
    } else {
        wp_send_json_error(array('message' => 'Không thể gửi đánh giá. Vui lòng thử lại.'));
    }
}












