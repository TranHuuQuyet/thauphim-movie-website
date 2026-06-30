# Thông Báo Phim Sắp Chiếu

## Tính Năng (Features)

Tính năng thông báo phim sắp chiếu cho phép:

1. **Hiển thị thông báo phim sắp chiếu** - Người dùng đăng nhập có thể xem thông báo từ biểu tượng chuông trên header
2. **Đã đọc/chưa đọc** - Mỗi người dùng có trạng thái đã đọc riêng; mở chuông sẽ đánh dấu các thông báo đang hiển thị là đã đọc
3. **Quản lý phim sắp chiếu** - Admin có thể:
   - Thêm/sửa/xóa lịch chiếu
   - Chọn ngày chiếu, giờ chiếu tùy chọn, ghi chú
   - Chọn trạng thái Công bố/Nháp

## Cấu Trúc Files

### Header Notification

- **`/includes/header.php`** - Hiển thị biểu tượng chuông và danh sách thông báo
- **`/includes/upcoming_notifications.php`** - Lấy danh sách thông báo phim sắp chiếu đã công bố
- **`/assets/js/notifications.js`** - Điều khiển mở/đóng panel thông báo
- **`/api/notifications.php`** - Đánh dấu thông báo đang hiển thị là đã đọc

### Admin Panel

- **`/admin/schedules/index.php`** - Trang Lịch chiếu tổng hợp, có lọc theo phim/trạng thái/thời gian
- **`/admin/schedules/delete.php`** - Xóa lịch phát hành bằng POST + CSRF
- **`/admin/layout_sidebar.php`** - Có mục "Lịch chiếu"
- **`/admin/movies/index.php`** - Có nút "Lịch chiếu" để quản lý lịch của từng phim

## Database

### Bảng `movies` (Hiện có)

Sử dụng các cột:
- `status` - ENUM('coming_soon', 'ongoing', 'completed') - Trạng thái phim
- `release_date` - DATE - Ngày phát hành

### Bảng `schedules` (Hiện có)

```sql
CREATE TABLE schedules (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  movie_id INT UNSIGNED NOT NULL,
  release_date DATE NOT NULL,
  show_time TIME DEFAULT NULL,
  note VARCHAR(255) DEFAULT NULL,
  is_published TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_schedules_release_date (release_date),
  KEY idx_schedules_movie_id (movie_id),
  KEY idx_schedules_published (is_published),
  CONSTRAINT fk_schedules_movie
    FOREIGN KEY (movie_id) REFERENCES movies(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Bảng `notification_reads`

Lưu trạng thái đã đọc theo từng user và từng lịch chiếu.

```sql
CREATE TABLE notification_reads (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  schedule_id INT UNSIGNED NOT NULL,
  read_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_notification_reads_user_schedule (user_id, schedule_id)
);
```

Database hiện có cần chạy `database/notification_upgrade.sql`.

## Cách Sử Dụng

### Cho Người Dùng

1. Đăng nhập tài khoản
2. Nhấp biểu tượng chuông trên header
3. Xem danh sách phim sắp chiếu đã được admin công bố
4. Khi mở chuông, các thông báo đang hiển thị được đánh dấu đã đọc
5. Nhấp vào thông báo để xem chi tiết phim

### Cho Admin

1. Truy cập Admin Panel
2. Vào mục "Lịch chiếu" ở sidebar hoặc vào "Movies" rồi nhấp nút "Lịch chiếu" của từng phim
3. Thêm/sửa/xóa lịch chiếu
4. Chọn trạng thái Công bố để lịch được hiển thị trên chuông thông báo

## Ghi Chú

- Phim được hiển thị trong chuông thông báo nếu:
  - Có lịch phát hành trong bảng `schedules` với ngày từ hôm nay trở đi
  - Lịch phát hành đã được công bố (`is_published = 1`)
- Badge trên chuông chỉ đếm thông báo chưa đọc của user hiện tại
- Giờ chiếu là tùy chọn; nếu bỏ trống, chuông chỉ hiển thị ngày

## Tích Hợp Thêm

Nếu cần, có thể mở rộng tính năng bằng:
- Thêm email notification cho các phim sắp chiếu yêu thích
- Thêm countdown timer cho phim sắp phát hành
- Đồng bộ với TMDB API để cập nhật ngày phát hành tự động
