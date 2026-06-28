# Phim Sắp Chiếu - Upcoming Movies Feature

## Tính Năng (Features)

Tính năng "Phim Sắp Chiếu" cho phép:

1. **Hiển thị phim sắp chiếu** - Trang công khai để xem danh sách phim sắp được phát hành
2. **Lọc và tìm kiếm** - Người dùng có thể tìm kiếm phim theo:
   - Tên phim
   - Loại (Phim/Phim bộ)
   - Thể loại
   - Quốc gia
   - Sắp xếp (Ngày phát hành, Phổ biến nhất, Tên, Đánh giá)
3. **Quản lý phim sắp chiếu** - Admin có thể:
   - Thay đổi trạng thái phim (Sắp chiếu/Đang chiếu/Đã hoàn thành)
   - Thêm lịch phát hành chi tiết
   - Quản lý ghi chú về lịch chiếu

## Cấu Trúc Files

### Frontend (Giao diện người dùng)

- **`/pages/upcoming.php`** - Trang hiển thị danh sách phim sắp chiếu
- **`/assets/css/upcoming.css`** - Stylesheet cho trang phim sắp chiếu
- **`/includes/header.php`** - Đã thêm menu "Sắp chiếu" vào navigation

### Backend (API)

- **`/api/upcoming-movies.php`** - API endpoint để lấy danh sách phim sắp chiếu
  - URL: `/api/upcoming-movies.php`
  - Hỗ trợ tham số:
    - `page` (int) - Số trang (mặc định: 1)
    - `limit` (int) - Số phim trên mỗi trang (mặc định: 20)
    - `search` (string) - Tìm kiếm theo tên
    - `type` (string) - Lọc theo loại (movie/series)
    - `genre_id` (int) - Lọc theo thể loại
    - `country` (string) - Lọc theo quốc gia (code hoặc ID)
    - `sort_by` (string) - Sắp xếp theo (release_date/popularity/title/rating)
    - `order` (string) - Thứ tự (ASC/DESC)

### Admin Panel

- **`/admin/schedules/index.php`** - Quản lý lịch phát hành của từng phim
  - Xem lịch phát hành hiện tại
  - Thêm lịch phát hành mới
  - Cập nhật trạng thái phim
- **`/admin/schedules/delete.php`** - Xóa lịch phát hành
- **`/admin/movies/index.php`** - Đã cập nhật thêm nút "Schedule" để quản lý lịch chiếu

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

## Cách Sử Dụng

### Cho Người Dùng

1. Truy cập vào menu "Sắp chiếu" từ navigation header
2. Xem danh sách phim sắp chiếu
3. Sử dụng các bộ lọc để tìm kiếm phim cụ thể
4. Nhấp vào phim để xem chi tiết

### Cho Admin

1. Truy cập Admin Panel
2. Vào mục "Movies"
3. Tìm phim cần quản lý
4. Nhấp nút "Schedule" để quản lý lịch phát hành
5. Cập nhật trạng thái, thêm/xóa lịch phát hành

## API Endpoint Examples

### Lấy danh sách phim sắp chiếu

```
GET /api/upcoming-movies.php?page=1&limit=20
```

Response:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Avengers: Endgame",
      "original_title": "Avengers: Endgame",
      "poster": "https://image.tmdb.org/t/p/w500/...",
      "backdrop": "https://image.tmdb.org/t/p/w1280/...",
      "release_date": "2026-07-31",
      "upcoming_date": "2026-07-31",
      "type": "movie",
      "quality": "4K",
      "rating": 8.5,
      "status": "coming_soon",
      "is_premium": true,
      "popularity": 95.5,
      "runtime": 180,
      "genres": ["Action", "Adventure"],
      "country": {
        "code": "US",
        "name": "Hoa Kỳ"
      }
    }
  ],
  "meta": {
    "page": 1,
    "limit": 20,
    "total": 50,
    "total_pages": 3
  }
}
```

### Lọc theo thể loại

```
GET /api/upcoming-movies.php?genre_id=28&page=1
```

### Tìm kiếm phim

```
GET /api/upcoming-movies.php?search=Avengers&page=1
```

### Sắp xếp theo phổ biến

```
GET /api/upcoming-movies.php?sort_by=popularity&order=DESC&page=1
```

## Ghi Chú

- Phim được hiển thị trong trang sắp chiếu nếu:
  - Trạng thái là `coming_soon` HOẶC
  - Có lịch phát hành trong bảng `schedules` với ngày > hôm nay
- Admin có thể quản lý lịch phát hành chi tiết và ghi chú từ từng phim
- Trang sắp chiếu hỗ trợ pagination để hiển thị danh sách lớn

## Tích Hợp Thêm

Nếu cần, có thể mở rộng tính năng bằng:
- Thêm email notification cho các phim sắp chiếu yêu thích
- Thêm countdown timer cho phim sắp phát hành
- Thêm widget "Sắp chiếu" trên trang chủ
- Đồng bộ với TMDB API để cập nhật ngày phát hành tự động
