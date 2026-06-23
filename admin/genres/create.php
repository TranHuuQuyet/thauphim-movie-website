<?php
// 1. Nhúng file kết nối CSDL
require_once '../../includes/db.php';

// Khai báo biến báo lỗi hoặc thành công nếu có
$error = '';
$success = '';

// 2. Xử lý khi Admin bấm nút "Lưu lại" (Gửi form POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy dữ liệu từ form và loại bỏ khoảng trắng thừa
    $name = trim($_POST['name']);
    $slug = trim($_POST['slug']);

    // Kiểm tra xem admin có để trống trường nào không
    if (empty($name) || empty($slug)) {
        $error = 'Vui lòng nhập đầy đủ Tên thể loại và Slug!';
    } else {
        try {
            // Kiểm tra xem tên hoặc slug này đã tồn tại trong database chưa
            $checkStmt = $pdo->prepare("SELECT id FROM genres WHERE name = ? OR slug = ?");
            $checkStmt->execute([$name, $slug]);
            
            if ($checkStmt->rowCount() > 0) {
                $error = 'Tên thể loại hoặc Slug này đã tồn tại rồi!';
            } else {
                // Nếu mọi thứ ổn, tiến hành chèn (INSERT) vào database
                $insertStmt = $pdo->prepare("INSERT INTO genres (name, slug) VALUES (?, ?)");
                $insertStmt->execute([$name, $slug]);
                
                $success = 'Thêm thể loại mới thành công!';
                // Reset form sau khi thêm thành công
                $name = $slug = '';
            }
        } catch (PDOException $e) {
            $error = 'Lỗi hệ thống: ' . $e->getMessage();
        }
    }
}

// 3. Nhúng bộ khung giao diện Layout Admin vào
include '../layout_header.php';
include '../layout_sidebar.php';
?>

<div class="mb-4">
    <h2><i class="fa-solid fa-plus text-success me-2"></i> Thêm Thể loại Mới</h2>
    <p class="text-muted">Tạo thêm các thể loại phim mới cho hệ thống (Hành động, Tình cảm, Viễn tưởng...)</p>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body">
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i> <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fa-solid fa-circle-check me-2"></i> <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form action="create.php" method="POST">
                    <div class="mb-3">
                        <label for="name" class="form-label fw-bold">Tên thể loại</label>
                        <input type="text" class="form-line form-control" id="name" name="name" placeholder="Ví dụ: Hành Động" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="slug" class="form-label fw-bold">Slug (Đường dẫn đẹp)</label>
                        <input type="text" class="form-control" id="slug" name="slug" placeholder="Ví dụ: hanh-dong" value="<?php echo isset($slug) ? htmlspecialchars($slug) : ''; ?>" required>
                        <div class="form-text text-muted">Viết liền không dấu, ngăn cách bằng dấu gạch ngang (không chứa khoảng trắng).</div>
                    </div>

                    <div class="d-flex gap-2 pt-2">
                        <button type="submit" class="btn btn-success">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Lưu lại
                        </button>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fa-solid fa-arrow-left me-1"></i> Quay lại danh sách
                        </a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('name').addEventListener('input', function() {
    let title = this.value;
    let slug = title.toLowerCase()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '') // Xóa dấu tiếng Việt
        .replace(/[đĐ]/g, 'd')
        .replace(/([^a-z0-9\s- ]+)/g, '') // Xóa ký tự đặc biệt
        .replace(/&/g, '-and-')
        .replace(/[\s-]+/g, '-') // Thay khoảng trắng bằng dấu -
        .trim();
    document.getElementById('slug').value = slug;
});
</script>

<?php
// 4. Nhúng footer đóng các thẻ HTML
include '../layout_footer.php';
?>