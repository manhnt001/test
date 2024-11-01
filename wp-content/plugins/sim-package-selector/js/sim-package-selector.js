jQuery(document).ready(function($) {
    // Khi nhấn nút "Chọn số"
    $('.select-number').click(function() {
        var productId = $(this).data('product-id');
        var phoneNumber = $(this).data('phone-number');

        // Gọi AJAX để lấy các gói cước
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'get_packages_by_network',
                network_provider: $(this).data('network-provider') // Lấy nhà mạng từ dữ liệu của SIM
            },
            success: function(response) {
                $('#carousel-items').html(response); // Cập nhật danh sách các gói cước trong carousel
                $('#package-popup').fadeIn();
                $('#package-popup').data('product-id', productId);
                $('#package-popup').data('phone-number', phoneNumber);
<<<<<<< HEAD

                // Kiểm tra kích thước màn hình
                if ($(window).width() < 768) { // Nếu là mobile
                    $('.carousel-item').slice(1).hide(); // Ẩn tất cả các gói cước ngoại trừ gói đầu tiên
                }
=======
>>>>>>> origin/main
            }
        });
    });

    // Đóng popup
    $('.close-popup').click(function() {
        $('#package-popup').fadeOut();
    });

<<<<<<< HEAD
    // Thêm sự kiện click cho từng gói cước
    $(document).on('click', '.package-item', function() {
        $('.package-item').removeClass('selected'); // Xóa lớp selected khỏi tất cả gói cước
        $(this).addClass('selected'); // Thêm lớp selected cho gói cước được chọn
    });

    // Thêm gói cước vào giỏ hàng
    $('.add-package').click(function() {
        var selectedVariationId = $('.package-item.selected').data('id');
=======
    // Thêm biến thể vào giỏ hàng
    $('.add-package').click(function() {
        var selectedVariationId = $('#carousel-items .carousel-item.active .package-item').data('id');
>>>>>>> origin/main
        var productId = $('#package-popup').data('product-id');
        var phoneNumber = $('#package-popup').data('phone-number');

        if (selectedVariationId) {
            // Thêm biến thể vào giỏ hàng
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'add_to_cart',
<<<<<<< HEAD
                    product_id: selectedVariationId
=======
                    product_id: selectedVariationId,
                    sim_package_selector_nonce_field: $('#package-popup').find('input[name="sim_package_selector_nonce_field"]').val() // Gửi nonce
>>>>>>> origin/main
                },
                success: function() {
                    // Thêm sản phẩm SIM vào giỏ hàng với số điện thoại
                    $.ajax({
<<<<<<< HEAD
                        url: ajaxurl,
=======
                        url: wc_add_to_cart_params.ajax_url,
>>>>>>> origin/main
                        type: 'POST',
                        data: {
                            action: 'add_to_cart',
                            product_id: productId,
                            phone_number: phoneNumber
                        },
                        success: function() {
<<<<<<< HEAD
                            alert('Gói cước và SIM đã được thêm vào giỏ hàng.');
=======
                            alert('Biến thể và SIM đã được thêm vào giỏ hàng.');
>>>>>>> origin/main
                            $('#package-popup').fadeOut();
                        }
                    });
                },
                error: function() {
                    alert('Có lỗi xảy ra khi thêm vào giỏ hàng.');
                }
            });
        } else {
            alert('Vui lòng chọn một gói cước.');
        }
    });
});
