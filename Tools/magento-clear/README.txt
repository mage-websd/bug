# # # # # # # # # # # # # # # # # # # 
# CLEAR LOG
# # # # # # # # # # # # # # # # # # #
Kiểm tra các table log cần xóa, chạy lệnhsau tại thư mục chứa Magento
php -f shell/log.php status

# # # # # # # # # # # # # # # # # # #
# CLEAR ORDER
# # # # # # # # # # # # # # # # # # #
Kiểm tra các table order , copy file show-order-tables.php vào thư mục chứa Magento và chạy trên trình duyệt

# # # # # # # # # # # # # # # # # # #
# CLEAR CUSTOMER
# # # # # # # # # # # # # # # # # # #
Copy file n98-magerun.phar vào thư mục chứa Magento và chạy lệnh sau
php n98-magerun.phar customer:delete

