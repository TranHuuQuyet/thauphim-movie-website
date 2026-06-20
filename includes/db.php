<?php
// Thông tin kết nối CSDL (Đã cập nhật PORT 3307 dựa theo file config của bạn)
$host = '127.0.0.1';
$port = '3307'; 
$dbname = 'thauphim_db';
$username = 'root';
$password = '';

try {
    // Thêm ";port=$port" vào trong chuỗi cấu hình PDO
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Thiết lập chế độ báo lỗi (Exception) để dễ debug khi code sai
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Cấu hình mặc định trả về mảng kết hợp (Associative Array)
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    // Nếu kết nối thất bại thì dừng chương trình và báo lỗi
    die("Lỗi kết nối CSDL: " . $e->getMessage());
}
?>