<?php
// 1. Nhúng file kết nối CSDL (Từ thư mục này lùi ra 2 cấp để vào includes)
require_once '../../includes/db.php';

// 2. Viết câu lệnh SQL lấy toàn bộ thể loại ra
try {
    $stmt = $pdo->query("SELECT * FROM genres ORDER BY id DESC");
    $genres = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Lỗi lấy dữ liệu: " . $e->getMessage());
}

// 3. Nhúng bộ khung giao diện Layout Admin vào
include '../layout_header.php';
include '../layout_sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fa-solid fa-tags me-2 text-primary"></i> Quản lý Thể loại</h2>
    <a href="create.php" class="btn btn-primary">
        <i class="fa-solid fa-plus me-1"></i> Thêm thể loại mới
    </a>
</div>

<div class="card shadow-sm mt-3">
    <div class="card-body p-0"> <div class="table-responsive">
            <table class="table table-hover m-0">
                <thead>
                    <tr>
                        <th width="80" class="text-center">ID</th>
                        <th>Tên thể loại</th>
                        <th>Slug (Đường dẫn đẹp)</th>
                        <th width="150" class="text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Đoạn này giả định câu lệnh SQL và vòng lặp của bạn, hãy giữ nguyên biến logic cũ
                    // Ví dụ nếu dùng PDO: while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    if (isset($genres) && count($genres) > 0) {
                        foreach ($genres as $row) {
                    ?>
                        <tr>
                    <td class="text-center text-muted fw-bold"><?= $row['id']; ?></td>
                    <td class="text-white fw-semibold"><?= htmlspecialchars($row['name']); ?></td>
                    <td class="text-warning" style="font-family: monospace; font-size: 14px; opacity: 0.8;"><?= htmlspecialchars($row['slug']); ?></td>
                    <td class="text-center">
                        <a href="edit.php?id=<?= $row['id']; ?>" class="btn-action btn-action-edit me-1">
                            <i class="fa-solid fa-pen"></i>
                        </a>
                        <a href="delete.php?id=<?= $row['id']; ?>" class="btn-action btn-action-delete" onclick="return confirm('Xóa hả má?');">
                            <i class="fa-solid fa-trash"></i>
                        </a>
                    </td>
                 </tr>
                    <?php 
                        }
                    } else { 
                    ?>
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">
                                <i class="fa-solid fa-folder-open me-2 fs-5"></i> Hiện chưa có thể loại nào. Bấm nút "Thêm thể loại mới" ở trên để tạo nha!
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php
// 4. Nhúng footer để đóng các thẻ HTML lại
include '../layout_footer.php';
?>