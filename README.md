# ThauPhim - Online Movie Streaming Website

## 1. Gioi thieu du an

ThauPhim la website xem phim truc tuyen duoc xay dung cho do an mon Lap trinh Web. Du an su dung PHP, MySQL, HTML, CSS va JavaScript de tao mot he thong co trang user, API backend va trang quan tri.

Huong du lieu chinh cua du an:

```text
TMDB API
-> tools/import_tmdb.php
-> MySQL Database
-> PHP API noi bo
-> Website user + Admin
```

TMDB chi duoc dung de import metadata phim ban dau. Sau khi import, MySQL la nguon du lieu chinh. Website user khong nen goi TMDB truc tiep nua. Admin co quyen sua toan bo du lieu phim da import va tu nhap link YouTube embed cho tung phim/tap phim.

## 2. Muc tieu

- Xay dung website xem phim co giao dien de su dung, ho tro desktop va mobile.
- Dung TMDB API de import metadata phim le va phim bo vao MySQL.
- Dung MySQL lam nguon du lieu chinh cho website va admin.
- Dung PHP API noi bo de website lay du lieu tu MySQL.
- Cho admin CRUD phim, tap phim, the loai, quoc gia va dien vien.
- Cho admin tu nhap link YouTube iframe vao `episodes.youtube_url`.
- Ho tro authentication, membership mo phong, binh luan, danh gia, yeu thich va lich su xem theo tien do.
- Deploy len hosting PHP/MySQL de demo.

## 3. Luong du lieu he thong

### 3.1 Import metadata tu TMDB

```text
TMDB API
-> Lay movie + tv series
-> Loc trung theo tmdb_id + tmdb_type
-> Luu vao movies, genres, countries, actors
-> Luu quan he movie_genres, movie_actors
```

Script import:

```text
tools/import_tmdb.php
```

Script nay import khoang 100 phim/phim bo, uu tien can bang gan 50 phim le va 50 phim bo. Script khong tao episode va khong gan link YouTube. Link xem phim/tap phim se do admin nhap sau.

### 3.2 Website doc tu MySQL

```text
MySQL
-> PHP API trong /api
-> Frontend user
```

Frontend nen goi API noi bo nhu:

```text
/api/movies.php
/api/movie-detail.php?id=1
/api/genres.php
/api/countries.php
/api/actors.php
/api/episodes.php?movie_id=1
```

Khong expose `TMDB_API_KEY` ra JavaScript frontend trong flow chinh thuc.

### 3.3 Admin quan ly du lieu

```text
Admin
-> CRUD movies/episodes/genres/countries/actors
-> Tu tim video tren YouTube
-> Copy link embed
-> Luu vao episodes.youtube_url
```

Vi du link hop le:

```text
https://www.youtube.com/embed/VIDEO_ID
```

## 4. Trang thai hien tai

| Hang muc | Trang thai | Ghi chu |
| --- | --- | --- |
| Schema MySQL phu hop TMDB + admin | Done | `database/schema.sql` da co field TMDB va episode YouTube |
| Seed admin toi thieu | Done | `database/seed.sql` chi tao admin mac dinh |
| Script import TMDB | Done | `tools/import_tmdb.php` |
| Import 100 phim/phim bo vao local MySQL | Done | Da test 50 movie + 50 series, khong trung |
| API PHP doc MySQL | Done | Cac endpoint trong `/api` |
| Frontend doc API MySQL | Pending | Hien frontend cu van can duoc chuyen dan |
| Admin CRUD movies/episodes | Pending | Can lam tiep de admin gan YouTube URL |
| Login/register dung bang users | Pending | Login cu chua dong bo voi DB moi |
| Favorite/comment/rating/history doc ghi DB | Pending | Schema da co, can trien khai sau |

## 5. Cong nghe su dung

### Frontend

- HTML5
- CSS3
- JavaScript ES6
- Responsive layout voi Flexbox, CSS Grid va Media Queries
- Dark Mode / Light Mode voi CSS variables va localStorage
- Font Awesome Icons

### Backend

- PHP 8+
- PDO prepared statements
- Session-based authentication
- PHP API tra JSON tu MySQL

### Database

- MySQL/MariaDB
- `database/schema.sql` de tao cau truc DB
- `database/seed.sql` de tao admin mac dinh

### Third-party Services

- TMDB API: import metadata phim/phim bo ban dau.
- TMDB Image CDN: hien thi poster/backdrop tu `poster_path` va `backdrop_path`.
- YouTube iframe: phat video phim/tap phim bang link embed do admin nhap.

## 6. Chuc nang chinh

### 6.1 Trang chu

- Header va menu dieu huong.
- Thanh tim kiem phim.
- Banner phim noi bat.
- Danh sach phim moi cap nhat.
- Danh sach phim le.
- Danh sach phim bo.
- Footer thong tin website.
- Nguon du lieu muc tieu: `/api/movies.php`.

### 6.2 Duyet phim, tim kiem va loc

- Danh sach tat ca phim.
- Tim kiem theo ten phim.
- Loc theo the loai.
- Loc theo quoc gia.
- Loc theo nam phat hanh.
- Loc theo loai phim: phim le, phim bo.
- Sap xep theo moi nhat, pho bien, top rated, luot xem.
- Phan trang.
- Nguon du lieu muc tieu: `/api/movies.php`.

### 6.3 Trang chi tiet phim

- Poster va anh nen phim.
- Ten phim, ten goc, nam, quoc gia, thoi luong, trang thai, chat luong.
- Mo ta phim.
- Danh sach the loai.
- Danh sach dien vien.
- Danh sach tap phim da publish.
- Nut xem phim.
- Phim lien quan theo the loai hoac quoc gia.
- Nguon du lieu muc tieu: `/api/movie-detail.php?id=...`.

### 6.4 Trang xem phim

- Video player bang YouTube iframe.
- Danh sach tap phim.
- Chuyen tap.
- Luu lich su xem neu user da dang nhap.
- Nguon video: `episodes.youtube_url`.
- Nguon tap phim: `/api/episodes.php?movie_id=...`.

### 6.5 Tai khoan nguoi dung

- Dang ky.
- Dang nhap.
- Dang xuat.
- Kiem tra session.
- Trang thai tai khoan: active/locked.
- Trang thai membership: free/premium.

### 6.6 Trang quan tri

- Dashboard tong quan.
- CRUD phim.
- CRUD tap phim va link YouTube embed.
- CRUD the loai.
- CRUD quoc gia.
- CRUD dien vien.
- Quan ly user va membership.
- Quan ly binh luan/danh gia khi cac tinh nang user duoc trien khai.
- Quan ly lich chieu/thong bao phim sap chieu bang `schedules`.

### 6.7 Binh luan, danh gia, yeu thich, lich su

- Schema da co cac bang `comments`, `ratings`, `favorites`, `watch_history`.
- Cac chuc nang nay nen lam sau khi movie API, admin CRUD va watch page on dinh.

## 7. API PHP noi bo

Tat ca API tra JSON voi format thanh cong:

```json
{
  "success": true,
  "data": [],
  "meta": {
    "page": 1,
    "limit": 20,
    "total": 100
  }
}
```

Format loi:

```json
{
  "success": false,
  "message": "Thong bao loi ngan gon"
}
```

### Danh sach API

| Endpoint | Chuc nang |
| --- | --- |
| `/api/movies.php` | Danh sach phim, tim kiem, loc, sap xep, phan trang |
| `/api/movie-detail.php?id=1` | Chi tiet phim, genres, actors, episodes |
| `/api/genres.php` | Danh sach the loai |
| `/api/countries.php` | Danh sach quoc gia |
| `/api/actors.php` | Danh sach dien vien, co phan trang |
| `/api/episodes.php?movie_id=1` | Danh sach tap phim da publish |
| `/api/movies-by-country.php?code=US` | Phim theo ma quoc gia |

### Query cua `/api/movies.php`

```text
type=movie|series
genre_id=1
country=US
year=2026
q=spider
sort=newest|popular|top_rated|most_viewed
limit=20
page=1
```

## 8. Import TMDB

### Dieu kien

- MySQL dang chay.
- `includes/config.php` co dung:
  - `TMDB_API_KEY`
  - `DB_HOST`
  - `DB_PORT`
  - `DB_NAME`
  - `DB_USER`
  - `DB_PASS`

### Chay rebuild DB local

```powershell
D:\Xampp\mysql\bin\mysql.exe -uroot -P3306 --default-character-set=utf8mb4 -e "SOURCE D:/Xampp/htdocs/thauphim-movie-website/database/schema.sql; SOURCE D:/Xampp/htdocs/thauphim-movie-website/database/seed.sql;"
```

### Chay import TMDB

```powershell
D:\Xampp\php\php.exe tools\import_tmdb.php
```

Script se:

- Goi cac endpoint TMDB:
  - `movie/popular`
  - `movie/top_rated`
  - `movie/now_playing`
  - `movie/upcoming`
  - `tv/popular`
  - `tv/top_rated`
  - `tv/on_the_air`
  - `trending/movie/week`
  - `trending/tv/week`
- Loc trung theo `tmdb_id + tmdb_type`.
- Luu metadata vao MySQL.
- Khong tao episode.
- Khong luu YouTube URL.
- Neu DB da du 100 record thi dung ngay, khong import trung.

### Kiem tra sau import

```sql
SELECT COUNT(*) FROM movies;
SELECT type, COUNT(*) FROM movies GROUP BY type;
SELECT COUNT(*) FROM genres;
SELECT COUNT(*) FROM actors;
SELECT COUNT(*) FROM movie_genres;
SELECT COUNT(*) FROM movie_actors;
SELECT tmdb_id, tmdb_type, COUNT(*)
FROM movies
GROUP BY tmdb_id, tmdb_type
HAVING COUNT(*) > 1;
```

Ket qua mong muon:

```text
movies: 100
movie: 50
series: 50
duplicate tmdb_id + tmdb_type: 0
episodes: co the rong luc moi import
```

## 9. Thiet ke database

### users

- id
- username
- email
- password_hash
- role: user/admin
- membership: free/premium
- status: active/locked
- last_login_at
- created_at
- updated_at

### movies

- id
- tmdb_id
- tmdb_type: movie/tv
- title
- original_title
- overview
- description
- poster
- backdrop
- poster_path
- backdrop_path
- release_date
- release_year
- runtime
- type: movie/series
- quality
- country_id
- status: coming_soon/ongoing/completed
- is_premium
- views
- rating_average
- rating_count
- vote_average
- vote_count
- popularity
- original_language
- imported_at
- last_synced_at
- created_at
- updated_at

Ghi chu:

- `tmdb_id + tmdb_type` la unique key de chong import trung.
- `tmdb_type` la loai goc cua TMDB: `movie` hoac `tv`.
- `type` la loai dung trong website: `movie` hoac `series`.
- `overview` la mo ta goc tu TMDB.
- `description` la mo ta hien thi/admin co the sua.

### episodes

- id
- movie_id
- episode_number
- title
- youtube_url
- duration_seconds
- is_published
- created_at
- updated_at

Ghi chu:

- `youtube_url` co the rong luc moi import.
- Admin se tu nhap link YouTube embed.
- Chi episodes co `is_published = 1` moi nen hien cho user.

### genres

- id
- tmdb_genre_id
- name
- slug
- created_at
- updated_at

### countries

- id
- code
- name
- created_at
- updated_at

### actors

- id
- tmdb_actor_id
- name
- avatar
- profile_path
- biography
- known_for_department
- created_at
- updated_at

### movie_genres

- movie_id
- genre_id

### movie_actors

- movie_id
- actor_id
- character_name
- cast_order

### schedules

- id
- movie_id
- release_date
- note
- is_published
- created_at
- updated_at

Ghi chu: `schedules` dung cho lich chieu/thong bao phim sap chieu.

### favorites

- id
- user_id
- movie_id
- created_at

### watch_history

- id
- user_id
- movie_id
- episode_id
- progress_seconds
- watched_at

### comments

- id
- user_id
- movie_id
- content
- status: visible/hidden
- created_at
- updated_at

### ratings

- id
- user_id
- movie_id
- rating: 1-5
- created_at
- updated_at

## 10. Quan he database

- Mot phim thuoc mot quoc gia.
- Mot phim co nhieu tap.
- Mot phim co nhieu the loai thong qua `movie_genres`.
- Mot phim co nhieu dien vien thong qua `movie_actors`.
- Mot phim co the co nhieu lich/thong bao sap chieu thong qua `schedules`.
- Mot user co nhieu phim yeu thich.
- Mot user co nhieu lich su xem.
- Mot user co nhieu binh luan.
- Mot user co nhieu danh gia phim.
- Mot phim co nhieu binh luan va danh gia.
- Mot admin co quyen quan ly du lieu he thong.

## 11. Cau truc thu muc

```text
thauphim-movie-website/
|-- admin/
|   |-- dashboard.php
|   |-- movies/
|   |-- episodes/
|   |-- genres/
|   |-- countries/
|   |-- actors/
|   |-- users/
|   |-- comments/
|
|-- api/
|   |-- _helpers.php
|   |-- movies.php
|   |-- movie-detail.php
|   |-- genres.php
|   |-- countries.php
|   |-- actors.php
|   |-- episodes.php
|   |-- movies-by-country.php
|
|-- assets/
|   |-- css/
|   |-- js/
|   |-- images/
|
|-- database/
|   |-- schema.sql
|   |-- seed.sql
|
|-- includes/
|   |-- config.php
|   |-- db.php
|   |-- header.php
|   |-- footer.php
|   |-- functions.php
|
|-- pages/
|   |-- movie-detail.php
|   |-- country.php
|   |-- actor.php
|
|-- tools/
|   |-- import_tmdb.php
|
|-- index.php
|-- login.php
|-- register.php
|-- logout.php
|-- README.md
```

Ghi chu: poster/backdrop hien uu tien lay tu TMDB path va TMDB Image CDN. Thu muc upload co the them sau neu admin can upload anh local.

## 12. Main task cua du an

### Main Task 1 - UI Layout & Homepage

- Xay dung layout dung chung.
- Header, navigation, footer.
- Trang chu.
- Responsive desktop/mobile.
- Dark Mode / Light Mode Toggle.
- Chuan hoa CSS component cho card phim, button, form.

### Main Task 2 - Browse, Search & Filter

- Trang danh sach phim.
- Tim kiem theo ten phim.
- Loc theo the loai, quoc gia, nam, loai phim.
- Sap xep va phan trang.
- Toi uu query MySQL cho danh sach phim.
- Chuyen du lieu trang danh sach/tim kiem/loc sang doc `/api/movies.php` sau khi API o Main Task 5 on dinh.

### Main Task 3 - Movie Detail & Watch Page

- Trang chi tiet phim.
- Danh sach tap phim.
- Trang xem phim.
- YouTube iframe player.
- Phim lien quan.
- Xu ly luu lich su xem khi user dang nhap.
- Chuyen trang chi tiet sang doc `/api/movie-detail.php`.
- Chuyen danh sach tap phim sang doc `/api/episodes.php`.
- Phat video tu `episodes.youtube_url` do admin nhap.

### Main Task 4 - User Features & Metadata Pages

- Dang ky, dang nhap, dang xuat.
- Session user.
- Phim yeu thich.
- Lich su xem.
- Binh luan phim.
- Danh gia phim.
- Trang quoc gia.
- Trang dien vien.
- Xem thong bao phim sap chieu tu icon chuong.
- Chuyen trang quoc gia/dien vien sang doc API MySQL khi backend san sang.

### Main Task 5 - Backend, Database, API, Admin & Deployment

- Thiet ke schema phu hop TMDB + admin.
- Tao `database/schema.sql` va `database/seed.sql`.
- Tao admin seed toi thieu.
- Ket noi database bang PDO.
- Tao `tools/import_tmdb.php`.
- Import 100 phim/phim bo vao MySQL.
- Kiem tra khong trung `tmdb_id + tmdb_type`.
- Tao API PHP doc MySQL trong `/api`.
- Admin dashboard.
- Dang nhap/dang xuat bang bang `users`.
- Admin auth check theo role admin.
- CRUD phim, tap phim, the loai, quoc gia, dien vien.
- Admin nhap va publish `episodes.youtube_url`.
- Quan ly user va membership.
- Quan ly binh luan va thong ke danh gia khi cac tinh nang user duoc trien khai.
- Deploy len hosting va cau hinh ten mien.

## 13. Ke hoach 1 thang

| Tuan | Muc tieu | Ket qua can co |
| --- | --- | --- |
| Tuan 1 | Chot yeu cau, UI, schema, TMDB import | Schema SQL, seed admin, import script, 100 phim trong MySQL |
| Tuan 2 | API va frontend user | API MySQL, trang chu/danh sach/chi tiet doc API |
| Tuan 3 | Watch page va admin CRUD | Admin movies/episodes, nhap YouTube URL, watch page |
| Tuan 4 | User features, test, deploy | Auth, comment/rating/favorite/history, responsive, deploy, bao cao |

## 14. Bao mat

- Mat khau phai hash bang `password_hash()`.
- Dang nhap kiem tra bang `password_verify()`.
- Truy van database dung PDO prepared statements.
- Validate du lieu dau vao tu form va query API.
- Kiem tra quyen admin truoc khi vao trang quan tri.
- Kiem tra user bi khoa truoc khi cho dang nhap.
- Escape noi dung binh luan khi hien thi de tranh XSS.
- Chi cho user dang nhap binh luan va danh gia.
- Gioi han moi user mot danh gia cho moi phim.
- Dung session de quan ly trang thai dang nhap.
- Them CSRF token cho cac form admin.
- Validate `youtube_url`, chi cho phep link YouTube embed hop le.
- Khong expose `TMDB_API_KEY` ra frontend runtime.

## 15. Deploy

### Moi truong

- Hosting ho tro PHP 8+ va MySQL/MariaDB.
- cPanel, DirectAdmin hoac moi truong tu quan ly.
- Custom domain.
- SSL/HTTPS.

### Checklist deploy

- Tao database va user MySQL tren hosting.
- Cau hinh `includes/config.php`:
  - `TMDB_API_KEY`
  - `DB_HOST`
  - `DB_PORT`
  - `DB_NAME`
  - `DB_USER`
  - `DB_PASS`
- Import `database/schema.sql`.
- Import `database/seed.sql`.
- Chay `tools/import_tmdb.php` mot lan neu hosting cho phep CLI/network.
- Neu hosting khong cho CLI, import DB local roi export SQL data len hosting.
- Kiem tra cac API `/api/*.php`.
- Kiem tra trang user.
- Kiem tra trang admin.
- Kiem tra dang nhap, tim kiem, chi tiet phim, xem phim va CRUD.

## 16. Tai khoan demo

```text
Admin:
Username: admin
Email: admin@thauphim.local
Password: admin123
```

Ghi chu: `seed.sql` hien chi tao admin. User Free/User Premium se tao sau qua register hoac admin.

## 17. Checklist kiem thu

### Database va import

- Chay `database/schema.sql` thanh cong.
- Chay `database/seed.sql` tao 1 admin.
- Chay `tools/import_tmdb.php` import du 100 phim/phim bo.
- Co 50 phim le va 50 phim bo.
- Khong co trung `tmdb_id + tmdb_type`.
- `genres`, `countries`, `actors`, `movie_genres`, `movie_actors` co du lieu.
- `episodes` co the rong sau import.

### API

- `/api/movies.php?type=movie` tra danh sach phim le.
- `/api/movies.php?type=series` tra danh sach phim bo.
- `/api/movies.php?sort=popular` sap xep theo popularity.
- `/api/movie-detail.php?id=1` tra movie, genres, actors, episodes.
- `/api/genres.php` tra danh sach the loai.
- `/api/countries.php` tra danh sach quoc gia.
- `/api/actors.php?limit=2` tra danh sach dien vien.
- `/api/episodes.php?movie_id=1` tra mang rong neu admin chua nhap tap.
- `/api/movies-by-country.php?code=US` tra phim theo quoc gia.

### Admin va user

- Admin dang nhap thanh cong.
- Admin tao, sua, xoa phim.
- Admin tao, sua, xoa tap phim.
- Admin nhap `youtube_url` va publish episode.
- API episodes tra episode da publish.
- Watch page phat dung YouTube iframe.
- Tim kiem, loc, phan trang hoat dong dung.
- Binh luan, danh gia, yeu thich, lich su xem hoat dong khi duoc trien khai.

## 18. San pham ban giao

- Source code day du.
- File `database/schema.sql`.
- File `database/seed.sql`.
- Script `tools/import_tmdb.php`.
- API PHP trong `/api`.
- README.md.
- Demo website tren domain hoac localhost.
- Tai khoan demo admin.
- Bao cao do an.
- Anh chup man hinh cac chuc nang chinh.

## 19. Huong phat trien sau do an

- Dong bo lai metadata TMDB thu cong tu admin.
- Import them phim theo quoc gia/the loai.
- Cap nhat poster/backdrop tu TMDB theo tung phim.
- Reply binh luan.
- Like/dislike binh luan.
- Bao cao binh luan vi pham.
- Goi y phim theo lich su xem.
- Xac thuc email.
- Thanh toan online that cho Premium.
- PWA.
- Da ngon ngu.
- Toi uu SEO.
