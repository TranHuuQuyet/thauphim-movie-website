USE thauphim;

SET NAMES utf8mb4;

-- Temporary demo password for all seeded accounts: password
-- Replace these hashes with password_hash('admin123'), password_hash('user123'),
-- and password_hash('premium123') after PHP is available on the machine.
INSERT INTO users (id, username, email, password_hash, role, membership, status) VALUES
(1, 'admin', 'admin@thauphim.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi', 'admin', 'premium', 'active'),
(2, 'userfree', 'user@thauphim.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi', 'user', 'free', 'active'),
(3, 'userpremium', 'premium@thauphim.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi', 'user', 'premium', 'active'),
(4, 'lockeduser', 'locked@thauphim.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi', 'user', 'free', 'locked');

INSERT INTO countries (id, code, name) VALUES
(1, 'VN', 'Việt Nam'),
(2, 'KR', 'Hàn Quốc'),
(3, 'CN', 'Trung Quốc'),
(4, 'JP', 'Nhật Bản'),
(5, 'TH', 'Thái Lan'),
(6, 'US', 'Hoa Kỳ'),
(7, 'GB', 'Anh'),
(8, 'FR', 'Pháp'),
(9, 'IN', 'Ấn Độ'),
(10, 'TW', 'Đài Loan'),
(11, 'HK', 'Hồng Kông'),
(12, 'CA', 'Canada'),
(13, 'ES', 'Tây Ban Nha'),
(14, 'DE', 'Đức'),
(15, 'IT', 'Ý'),
(16, 'AU', 'Úc'),
(17, 'PH', 'Philippines'),
(18, 'ID', 'Indonesia'),
(19, 'BR', 'Brazil'),
(20, 'MX', 'Mexico');

INSERT INTO genres (id, name) VALUES
(1, 'Action'),
(2, 'Adventure'),
(3, 'Romance'),
(4, 'Comedy'),
(5, 'Horror'),
(6, 'Animation'),
(7, 'Drama'),
(8, 'Science Fiction');

INSERT INTO actors (id, name, avatar, biography) VALUES
(1, 'Tom Holland', 'uploads/actors/tom-holland.jpg', 'Known for superhero and adventure movies.'),
(2, 'Zendaya', 'uploads/actors/zendaya.jpg', 'Known for drama and superhero movies.'),
(3, 'Song Kang', 'uploads/actors/song-kang.jpg', 'Popular Korean actor.'),
(4, 'Ma Dong-seok', 'uploads/actors/ma-dong-seok.jpg', 'Known for action movies.'),
(5, 'Tran Thanh', 'uploads/actors/tran-thanh.jpg', 'Vietnamese actor and director.'),
(6, 'Minh Hang', 'uploads/actors/minh-hang.jpg', 'Vietnamese singer and actor.');

INSERT INTO movies (
  id, title, description, poster, backdrop, release_year,
  type, quality, country_id, status, is_premium, views, rating_average, rating_count
) VALUES
(1, 'Spider Man: Brand New Day', 'A new Spider Man adventure used as a demo featured movie.', 'uploads/posters/spider-man-brand-new-day.jpg', 'uploads/backdrops/spider-man-brand-new-day.jpg', 2026, 'movie', '4K', 6, 'coming_soon', 1, 2450, 4.50, 2),
(2, 'Saigon Night Run', 'A Vietnamese action movie about a dangerous night in the city.', 'uploads/posters/saigon-night-run.jpg', 'uploads/backdrops/saigon-night-run.jpg', 2025, 'movie', 'HD', 1, 'completed', 0, 1880, 4.00, 2),
(3, 'K-Drama School', 'A romantic school series about friendship, ambition, and first love.', 'uploads/posters/k-drama-school.jpg', 'uploads/backdrops/k-drama-school.jpg', 2024, 'series', 'HD', 2, 'ongoing', 0, 3200, 5.00, 1),
(4, 'Tokyo Future City', 'A science fiction anime movie set in a future megacity.', 'uploads/posters/tokyo-future-city.jpg', 'uploads/backdrops/tokyo-future-city.jpg', 2023, 'movie', 'Full HD', 4, 'completed', 1, 1510, 3.00, 1),
(5, 'Haunted Hotel Bangkok', 'A group of friends face strange events in an old Bangkok hotel.', 'uploads/posters/haunted-hotel-bangkok.jpg', 'uploads/backdrops/haunted-hotel-bangkok.jpg', 2022, 'movie', 'HD', 5, 'completed', 0, 960, 0.00, 0),
(6, 'Dragon Gate Legend', 'A fantasy adventure series about warriors protecting an ancient gate.', 'uploads/posters/dragon-gate-legend.jpg', 'uploads/backdrops/dragon-gate-legend.jpg', 2024, 'series', 'HD', 3, 'ongoing', 1, 2780, 0.00, 0);

INSERT INTO movie_genres (movie_id, genre_id) VALUES
(1, 1), (1, 2), (1, 8),
(2, 1), (2, 7),
(3, 3), (3, 4), (3, 7),
(4, 6), (4, 8),
(5, 5), (5, 7),
(6, 1), (6, 2), (6, 8);

INSERT INTO movie_actors (movie_id, actor_id) VALUES
(1, 1), (1, 2),
(2, 5), (2, 6),
(3, 3),
(4, 1),
(5, 4),
(6, 4), (6, 6);

INSERT INTO episodes (id, movie_id, episode_number, title, youtube_url) VALUES
(1, 1, 1, 'Trailer', 'https://www.youtube.com/embed/62bIsvRcPv0'),
(2, 2, 1, 'Full Movie', 'https://www.youtube.com/embed/dQw4w9WgXcQ'),
(3, 3, 1, 'Episode 1', 'https://www.youtube.com/embed/dQw4w9WgXcQ'),
(4, 3, 2, 'Episode 2', 'https://www.youtube.com/embed/dQw4w9WgXcQ'),
(5, 4, 1, 'Full Movie', 'https://www.youtube.com/embed/dQw4w9WgXcQ'),
(6, 5, 1, 'Full Movie', 'https://www.youtube.com/embed/dQw4w9WgXcQ'),
(7, 6, 1, 'Episode 1', 'https://www.youtube.com/embed/dQw4w9WgXcQ'),
(8, 6, 2, 'Episode 2', 'https://www.youtube.com/embed/dQw4w9WgXcQ');

INSERT INTO schedules (movie_id, release_date, note) VALUES
(1, '2026-07-31', 'Demo coming soon movie'),
(3, '2026-06-28', 'New episode every Friday'),
(6, '2026-06-30', 'New episode every Sunday');

INSERT INTO favorites (user_id, movie_id) VALUES
(2, 1),
(2, 3),
(3, 4),
(3, 6);

INSERT INTO watch_history (user_id, movie_id, episode_id, progress_seconds, watched_at) VALUES
(2, 3, 3, 1420, '2026-06-20 20:15:00'),
(2, 2, 2, 3300, '2026-06-21 21:05:00'),
(3, 4, 5, 2100, '2026-06-22 19:30:00'),
(3, 6, 7, 900, '2026-06-23 18:00:00');

INSERT INTO comments (user_id, movie_id, content, status, created_at) VALUES
(2, 1, 'Trailer looks exciting. Waiting for release day.', 'visible', '2026-06-20 18:30:00'),
(3, 1, 'This should be a premium highlight for the demo.', 'visible', '2026-06-21 10:20:00'),
(2, 3, 'Episode 1 is easy to follow and good for testing history.', 'visible', '2026-06-22 21:00:00'),
(4, 2, 'Hidden comment example for admin moderation.', 'hidden', '2026-06-22 22:10:00');

INSERT INTO ratings (user_id, movie_id, rating) VALUES
(2, 1, 4),
(3, 1, 5),
(2, 2, 4),
(3, 2, 4),
(2, 3, 5),
(3, 4, 3);
