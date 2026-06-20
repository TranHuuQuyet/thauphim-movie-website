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

<div class="card shadow-sm">
    <div class="card-body">
        <table class="table table-hover table-bordered align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th width="10%">ID</th>
                    <th width="45%">Tên thể loại</th>
                    <th width="30%">Slug (Đường dẫn đẹp)</th>
                    <th width="15%" class="text-center">Hành động</th>
                </tr>
            </table>
            
            <tbody>
                <?php if (empty($genres)): ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">
                            Hiện chưa có thể loại nào. Bấm nút "Thêm thể loại mới" ở trên để tạo nha!
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($genres as $genre): ?>
                        <tr>
                            <td><?php echo $genre['id']; ?></td>
                            <td class="fw-bold"><?php echo htmlspecialchars($genre['name']); ?></td>
                            <td><code class="text-danger"><?php echo htmlspecialchars($genre['slug']); ?></code></td>
                            <td class="text-center">
                                <a href="edit.php?id=<?php echo $genre['id']; ?>" class="btn btn-sm btn-warning me-1" title="Sửa">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <a href="delete.php?id=<?php echo $genre['id']; ?>" class="btn btn-sm btn-danger" title="Xóa" onclick="return confirm('Bạn có chắc chắn muốn xóa thể loại này không?');">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
// 4. Nhúng footer để đóng các thẻ HTML lại
include '../layout_footer.php';
?>