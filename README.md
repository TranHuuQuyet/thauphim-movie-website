# ThauPhim - Online Movie Streaming Website

## 1. Gioi thieu du an

ThauPhim la website xem phim truc tuyen duoc xay dung cho do an mon Lap trinh Web. Du an lay cam hung tu cac website xem phim nhu RoPhim/Werp, tap trung vao cac chuc nang cot loi co the trien khai trong thoi gian 1 thang: duyet phim, tim kiem, loc phim, xem chi tiet phim, xem tap phim bang video nhung, quan ly yeu thich, lich su xem va trang quan tri du lieu.

He thong se duoc deploy that len hosting PHP/MySQL va su dung ten mien rieng de phuc vu demo cuoi ky.

## 2. Muc tieu

- Xay dung mot website xem phim co giao dien de su dung, ho tro desktop va mobile.
- Ap dung PHP, MySQL, HTML, CSS va JavaScript vao mot ung dung web hoan chinh.
- Co day du luong xu ly frontend, backend, database, authentication va admin CRUD.
- Co du lieu mau de demo cac chuc nang chinh.
- Deploy du an len hosting voi ten mien that.
- Phan chia cong viec thanh 5 main task ro rang trong thoi gian 1 thang.

## 3. Pham vi trong 1 thang

Du an uu tien hoan thanh cac chuc nang bat buoc truoc. Cac chuc nang nang cao se duoc mo phong hoac dua vao huong phat trien de tranh qua tai pham vi.

| Module | Muc do uu tien | Trang thai |
| --- | --- | --- |
| Trang chu | Bat buoc | Planned |
| Danh sach phim | Bat buoc | Planned |
| Tim kiem phim | Bat buoc | Planned |
| Loc theo the loai, quoc gia, nam | Bat buoc | Planned |
| Sap xep va phan trang | Bat buoc | Planned |
| Chi tiet phim | Bat buoc | Planned |
| Xem tap phim bang YouTube iframe | Bat buoc | Planned |
| Dang ky, dang nhap, dang xuat | Bat buoc | Planned |
| Yeu thich phim | Bat buoc | Planned |
| Lich su xem | Bat buoc | Planned |
| Trang quan tri | Bat buoc | Planned |
| CRUD phim, tap phim, the loai, quoc gia, dien vien | Bat buoc | Planned |
| Quan ly nguoi dung | Bat buoc | Planned |
| Membership Free/Premium | Mo phong | Planned |
| Quang cao cho Free user | Mo phong | Planned |
| Binh luan phim co ban, khong reply | Bat buoc | Planned |
| Danh gia phim 1-5 sao | Bat buoc | Planned |
| Dark Mode / Light Mode Toggle | Bat buoc | Planned |
| Thanh toan online | Khong nam trong pham vi | Future |
| Goi y phim nang cao | Khong nam trong pham vi | Future |

## 4. Vai tro nguoi dung

### Guest

- Xem trang chu.
- Duyet danh sach phim.
- Tim kiem va loc phim.
- Xem trang chi tiet phim.

### Member

- Dang ky, dang nhap, dang xuat.
- Xem phim va chon tap phim.
- Them hoac xoa phim khoi danh sach yeu thich.
- Xem lich su da xem.
- Tiep tuc xem tap phim gan nhat.
- Binh luan phim.
- Danh gia phim tu 1 den 5 sao.

### Premium Member

- Day la chuc nang mo phong cho do an.
- Admin co the doi trang thai tai khoan tu Free sang Premium.
- Premium user duoc mo phong quyen xem noi dung premium va khong hien quang cao.
- Du an khong tich hop cong thanh toan that.

### Admin

- Dang nhap vao trang quan tri.
- Quan ly phim, tap phim, the loai, quoc gia, dien vien va lich chieu.
- Quan ly nguoi dung.
- Khoa hoac mo khoa tai khoan.
- Doi trang thai membership cua nguoi dung.
- Quan ly va an/xoa binh luan khong phu hop.

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
- Session-based authentication
- PDO hoac MySQLi prepared statements

### Database

- MySQL 8+

### Deployment

- Shared hosting hoac cloud hosting ho tro PHP/MySQL
- cPanel hoac DirectAdmin
- PHP 8+
- MySQL 8+
- Ten mien rieng
- HTTPS bang SSL co san tren hosting neu duoc ho tro

### Third-party Services

- YouTube iframe embed de nhung video phim/tap phim.

## 6. Chuc nang chinh

### 6.1 Trang chu

- Header va menu dieu huong.
- Thanh tim kiem phim.
- Banner phim noi bat.
- Danh sach phim moi cap nhat.
- Danh sach phim le.
- Danh sach phim bo.
- The loai hot.
- Footer thong tin website.

### 6.2 Duyet phim, tim kiem va loc

- Danh sach tat ca phim.
- Tim kiem theo ten phim.
- Loc theo the loai.
- Loc theo quoc gia.
- Loc theo nam phat hanh.
- Loc theo loai phim: phim le, phim bo.
- Sap xep moi nhat/cu nhat.
- Phan trang.

### 6.3 Trang chi tiet phim

- Poster va anh nen phim.
- Ten phim, nam, quoc gia, thoi luong, trang thai, chat luong.
- Mo ta phim.
- Danh sach the loai.
- Danh sach dien vien.
- Danh sach tap phim.
- Nut xem phim.
- Nut them vao yeu thich.
- Khu vuc binh luan.
- Diem danh gia trung binh va form danh gia 1-5 sao.
- Phim lien quan theo the loai hoac quoc gia.

### 6.4 Trang xem phim

- Video player bang YouTube iframe.
- Danh sach tap phim.
- Chuyen tap.
- Luu lich su xem.
- Hien thi nut tiep tuc xem neu nguoi dung da tung xem.

### 6.5 Tai khoan nguoi dung

- Dang ky.
- Dang nhap.
- Dang xuat.
- Kiem tra session.
- Danh sach phim yeu thich.
- Lich su xem.
- Trang thai tai khoan: active/locked.
- Trang thai membership: free/premium.

### 6.6 Membership mo phong

- Free user: xem phim co quang cao mo phong va co the bi gioi han mot so phim premium.
- Premium user: xem phim premium va khong hien quang cao mo phong.
- Admin thay doi membership trong trang quan tri.
- Khong co thanh toan that trong pham vi 1 thang.

### 6.7 Trang quan tri

- Dashboard tong quan.
- CRUD phim.
- CRUD tap phim.
- CRUD the loai.
- CRUD quoc gia.
- CRUD dien vien.
- CRUD lich chieu.
- Quan ly nguoi dung.
- Khoa/mo khoa tai khoan.
- Doi membership Free/Premium.
- Quan ly binh luan phim.
- Xem thong ke danh gia phim.

### 6.8 Binh luan va danh gia phim

- User dang nhap co the viet binh luan tren trang chi tiet phim.
- Binh luan chi ho tro mot cap, khong co chuc nang reply trong pham vi 1 thang.
- User co the xoa binh luan cua chinh minh neu can.
- Admin co the an hoac xoa binh luan khong phu hop.
- User dang nhap co the danh gia phim tu 1 den 5 sao.
- Moi user chi co mot danh gia cho moi phim, co the cap nhat lai diem da danh gia.
- Trang chi tiet phim hien thi diem trung binh va tong so luot danh gia.

### 6.9 Dark Mode / Light Mode

- Website co nut chuyen doi Dark Mode va Light Mode.
- Lua chon giao dien duoc luu bang `localStorage`.
- Theme khong can luu vao database vi day la tuy chon rieng tren trinh duyet cua tung nguoi dung.
- Giao dien mac dinh uu tien dark mode de phu hop voi website xem phim.
- Cac thanh phan chinh nhu header, card phim, form, admin table va watch page can ho tro ca hai che do.

## 7. Thiet ke database

### users

- id
- username
- email
- password_hash
- role: user/admin
- membership: free/premium
- status: active/locked
- created_at
- updated_at

### movies

- id
- title
- description
- poster
- backdrop
- release_year
- type: movie/series
- quality
- country_id
- status
- is_premium
- views
- rating_average
- rating_count
- created_at
- updated_at

### episodes

- id
- movie_id
- episode_number
- title

- youtube_url

- created_at
- updated_at

### genres

- id
- name

### countries

- id
- code
- name

### actors

- id
- name
- avatar
- biography

### movie_genres

- movie_id
- genre_id

### movie_actors

- movie_id
- actor_id

### schedules

- id
- movie_id
- release_date
- note

### favorites

- id
- user_id
- movie_id
- created_at

Ghi chu: can tao unique constraint cho cap `user_id` va `movie_id` de tranh them trung phim yeu thich.

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

Ghi chu: bang `comments` khong co `parent_id` vi du an khong ho tro reply binh luan trong pham vi 1 thang.

### ratings

- id
- user_id
- movie_id
- rating: 1-5
- created_at
- updated_at

Ghi chu: can tao unique constraint cho cap `user_id` va `movie_id` de moi user chi co mot danh gia cho moi phim. Khi user danh gia lai, he thong cap nhat record cu thay vi tao record moi.

## 8. Quan he database

- Mot phim thuoc mot quoc gia.
- Mot phim co nhieu tap.
- Mot phim co nhieu the loai thong qua bang `movie_genres`.
- Mot phim co nhieu dien vien thong qua bang `movie_actors`.
- Mot user co nhieu phim yeu thich.
- Mot user co nhieu lich su xem.
- Mot user co nhieu binh luan.
- Mot user co nhieu danh gia phim.
- Mot phim co nhieu binh luan va nhieu danh gia.
- Mot admin co quyen quan ly du lieu he thong.

## 9. Cau truc thu muc du kien

```text
thauphim-movie-website/
|-- admin/
|   |-- dashboard.php
|   |-- movies/
|   |-- episodes/
|   |-- genres/
|   |-- countries/
|   |-- actors/
|   |-- schedules/
|   |-- users/
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
|   |-- auth.php
|   |-- header.php
|   |-- footer.php
|   |-- functions.php
|
|-- pages/
|   |-- browse.php
|   |-- movie-detail.php
|   |-- watch.php
|   |-- favorites.php
|   |-- history.php
|   |-- country.php
|   |-- actor.php
|   |-- schedule.php
|
|-- uploads/
|   |-- posters/
|   |-- backdrops/
|   |-- actors/
|
|-- index.php
|-- login.php
|-- register.php
|-- logout.php
|-- README.md
```

## 10. 5 Main Task cua du an

Day la 5 nhom cong viec chinh cua du an. Khi trien khai, nhom co the gan moi main task cho mot thanh vien hoac chia nho tuy theo tien do thuc te.

### Main Task 1 - UI Layout & Homepage

- Xay dung layout dung chung.
- Header, navigation, footer.
- Trang chu.
- Banner phim noi bat.
- Responsive desktop/mobile.
- Dark Mode / Light Mode Toggle.
- Chuan hoa CSS component cho card phim, button, form.

### Main Task 2 - Browse, Search & Filter

- Trang danh sach phim.
- Tim kiem theo ten phim.
- Loc theo the loai, quoc gia, nam, loai phim.
- Sap xep va phan trang.
- Toi uu query MySQL cho danh sach phim.

### Main Task 3 - Movie Detail & Watch Page

- Trang chi tiet phim.
- Danh sach tap phim.
- Trang xem phim.
- YouTube iframe player.
- Phim lien quan.
- Xu ly luu lich su xem khi user dang nhap.

### Main Task 4 - User Features & Metadata Pages

- Dang ky, dang nhap, dang xuat.
- Session user.
- Phim yeu thich.
- Lich su xem.
- Binh luan phim.
- Danh gia phim.
- Trang quoc gia.
- Trang dien vien.
- Trang lich chieu.

### Main Task 5 - Backend, Database, Admin & Deployment

- Thiet ke database.
- Tao `schema.sql` va `seed.sql`.
- Ket noi database.
- Admin dashboard.
- CRUD phim, tap phim, the loai, quoc gia, dien vien, lich chieu.
- Quan ly user va membership.
- Quan ly binh luan va thong ke danh gia.
- Deploy len hosting va cau hinh ten mien.

## 11. Ke hoach 1 thang

| Tuan | Muc tieu | Ket qua can co |
| --- | --- | --- |
| Tuan 1 | Chot yeu cau, thiet ke UI, thiet ke database | Wireframe, schema SQL, layout co ban, seed data mau |
| Tuan 2 | Xay dung frontend va cac trang user chinh | Trang chu, dark/light toggle, danh sach phim, tim kiem, loc, dang ky/dang nhap |
| Tuan 3 | Xay dung trang phim va admin | Chi tiet phim, xem phim, yeu thich, lich su xem, binh luan, danh gia, admin CRUD |
| Tuan 4 | Hoan thien, test, deploy va bao cao | Fix bug, responsive, import data, deploy hosting, demo domain, bao cao |

## 12. Yeu cau bao mat

- Mat khau phai duoc hash bang `password_hash()`.
- Dang nhap kiem tra bang `password_verify()`.
- Truy van database dung PDO hoac MySQLi prepared statements.
- Validate du lieu dau vao tu form.
- Kiem tra quyen admin truoc khi vao trang quan tri.
- Kiem tra user bi khoa truoc khi cho dang nhap.
- Gioi han va kiem tra file upload poster/backdrop/avatar.
- Escape noi dung binh luan khi hien thi de tranh XSS.
- Chi cho user dang nhap binh luan va danh gia.
- Gioi han moi user mot danh gia cho moi phim.
- Dung session de quan ly trang thai dang nhap.
- Them CSRF token cho cac form admin neu kip trong tien do.

## 13. Ke hoach deploy

### Moi truong hosting

- Shared hosting hoac cloud hosting co ho tro PHP/MySQL
- cPanel hoac DirectAdmin
- PHP 8+
- MySQL 8+
- Custom domain
- SSL certificate co san tu hosting hoac Let's Encrypt

### Checklist deploy

- Dang ky goi hosting co ho tro PHP 8+ va MySQL.
- Tro DNS domain ve hosting theo nameserver hoac record A/CNAME.
- Tao database va user MySQL trong cPanel/DirectAdmin.
- Import `database/schema.sql`.
- Import `database/seed.sql`.
- Upload source code len thu muc public cua hosting, vi du `public_html/`.
- Cau hinh file ket noi database trong `includes/config.php`.
- Cap quyen ghi cho thu muc `uploads/` neu hosting yeu cau.
- Bat SSL/HTTPS neu hosting ho tro.
- Kiem tra trang user.
- Kiem tra trang admin.
- Kiem tra chuc nang dang nhap, tim kiem, xem phim va CRUD.

## 14. Tai khoan demo du kien

```text
Admin:
Email: admin@thauphim.local
Password: admin123

User Free:
Email: user@thauphim.local
Password: user123

User Premium:
Email: premium@thauphim.local
Password: premium123
```

Ghi chu: mat khau demo chi dung cho moi truong demo do an, khong dung cho production thuc te.

## 15. Checklist kiem thu

- Dang ky tai khoan moi thanh cong.
- Dang nhap, dang xuat thanh cong.
- User bi khoa khong the dang nhap.
- Tim kiem phim theo ten.
- Loc phim theo the loai, quoc gia, nam va loai phim.
- Phan trang hoat dong dung.
- Xem trang chi tiet phim.
- Xem tap phim bang YouTube iframe.
- Them va xoa phim yeu thich.
- Lich su xem duoc ghi lai.
- User dang nhap co the binh luan phim.
- Admin co the an hoac xoa binh luan.
- User dang nhap co the danh gia phim 1-5 sao.
- Diem danh gia trung binh hien thi dung.
- Moi user chi co mot danh gia tren moi phim.
- Dark Mode / Light Mode Toggle hoat dong va duoc luu sau khi reload.
- Admin tao, sua, xoa phim.
- Admin tao, sua, xoa tap phim.
- Admin tao, sua, xoa the loai, quoc gia, dien vien.
- Admin doi membership user.
- Website hien thi tot tren desktop va mobile.
- Website chay duoc tren domain deploy.

## 16. San pham ban giao

- Source code day du.
- File `database/schema.sql`.
- File `database/seed.sql`.
- README.md.
- Demo website tren domain that.
- Tai khoan demo admin/user.
- Bao cao do an.
- Anh chup man hinh cac chuc nang chinh.
- Bang phan cong va dong gop cua tung thanh vien.

## 17. Huong phat trien sau do an

- Reply binh luan.
- Like/dislike binh luan.
- Bao cao binh luan vi pham.
- Goi y phim theo lich su xem.
- Tich hop TMDB API.
- Xac thuc email.
- Thanh toan online that cho Premium.
- PWA.
- Da ngon ngu.
- Toi uu SEO.
