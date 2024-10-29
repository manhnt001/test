document.addEventListener('DOMContentLoaded', function() {
    const addToCartButtons = document.querySelectorAll('.chon-so-button');

    addToCartButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.getAttribute('data-product-id');
            const networkProvider = this.getAttribute('data-network-provider');
            const phoneNumber = this.getAttribute('data-phone-number');

            // Gọi AJAX để lấy các gói cước có cùng nhà mạng
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    'action': 'get_packages_by_network',
                    'network_provider': networkProvider
                })
            })
            .then(response => response.text())
            .then(data => {
                document.getElementById('carousel-items').innerHTML = data; // Cập nhật các item trong carousel
                document.getElementById('package-popup').style.display = 'flex';
                document.getElementById('package-popup').dataset.productId = productId;
                document.getElementById('package-popup').dataset.phoneNumber = phoneNumber;

                // Thêm sự kiện click cho các gói cước
                const packageItems = document.querySelectorAll('.package-item');
                packageItems.forEach(item => {
                    item.addEventListener('click', function() {
                        packageItems.forEach(i => i.classList.remove('selected')); // Xóa lớp selected khỏi tất cả
                        this.classList.add('selected'); // Thêm lớp selected cho gói cước đã chọn
                    });
                });

                currentIndex = 0; // Đặt lại chỉ số hiện tại
                updateCarousel(); // Cập nhật carousel
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    });

    // Đóng popup
    document.querySelector('.close-popup').addEventListener('click', function() {
        document.getElementById('package-popup').style.display = 'none';
    });

    // Cập nhật carousel
    function updateCarousel() {
        const items = document.querySelectorAll('.package-item');
        items.forEach(item => item.style.display = 'none'); // Ẩn tất cả các gói cước
        for (let i = currentIndex; i < currentIndex + 3 && i < items.length; i++) {
            items[i].style.display = 'block'; // Hiển thị 3 gói cước
        }
    }

    // Nút Next
    document.querySelector('#next-btn').addEventListener('click', function() {
        currentIndex += 3; // Tăng chỉ số
        if (currentIndex >= document.querySelectorAll('.package-item').length) {
            currentIndex = document.querySelectorAll('.package-item').length - 3; // Giới hạn chỉ số
        }
        updateCarousel(); // Cập nhật carousel
    });

    // Nút Previous
    document.querySelector('#prev-btn').addEventListener('click', function() {
        currentIndex -= 3; // Giảm chỉ số
        if (currentIndex < 0) {
            currentIndex = 0; // Giới hạn chỉ số
        }
        updateCarousel(); // Cập nhật carousel
    });

    // Thêm gói cước vào giỏ hàng
    document.querySelector('.add-package').addEventListener('click', function() {
        const selectedPackageId = document.querySelector('.package-item.selected')?.dataset.id; // Lấy ID gói cước đã chọn
        const productId = document.getElementById('package-popup').dataset.productId; // Lấy ID SIM
        const phoneNumber = document.getElementById('package-popup').dataset.phoneNumber; // Lấy số điện thoại

        if (selectedPackageId) {
            // Thêm gói cước vào giỏ hàng
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    'action': 'add_to_cart',
                    'product_id': selectedPackageId // Gói cước
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Thêm sản phẩm SIM vào giỏ hàng với số điện thoại
                    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            'action': 'add_to_cart',
                            'product_id': productId, // SIM
                            'phone_number': phoneNumber // Số điện thoại
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Gói cước và SIM đã được thêm vào giỏ hàng.');
                            document.getElementById('package-popup').style.display = 'none';
                            location.reload(); // Tải lại trang sau khi thêm thành công
                        } else {
                            alert('Có lỗi xảy ra khi thêm SIM vào giỏ hàng.');
                        }
                    });
                } else {
                    alert('Có lỗi xảy ra khi thêm gói cước vào giỏ hàng.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        } else {
            alert('Vui lòng chọn một gói cước.');
        }
    });
});
