CREATE DATABASE IF NOT EXISTS thauphim
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE thauphim;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS ratings;
DROP TABLE IF EXISTS comments;
DROP TABLE IF EXISTS watch_history;
DROP TABLE IF EXISTS favorites;
DROP TABLE IF EXISTS schedules;
DROP TABLE IF EXISTS movie_actors;
DROP TABLE IF EXISTS movie_genres;
DROP TABLE IF EXISTS episodes;
DROP TABLE IF EXISTS actors;
DROP TABLE IF EXISTS movies;
DROP TABLE IF EXISTS countries;
DROP TABLE IF EXISTS genres;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL,
  email VARCHAR(150) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
  membership ENUM('free', 'premium') NOT NULL DEFAULT 'free',
  status ENUM('active', 'locked') NOT NULL DEFAULT 'active',
  last_login_at DATETIME DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_users_username (username),
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE genres (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tmdb_genre_id INT UNSIGNED DEFAULT NULL,
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(120) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_genres_tmdb (tmdb_genre_id),
  UNIQUE KEY uq_genres_slug (slug),
  KEY idx_genres_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE countries (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code CHAR(2) NOT NULL,
  name VARCHAR(100) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_countries_code (code),
  KEY idx_countries_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE actors (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tmdb_actor_id INT UNSIGNED DEFAULT NULL,
  name VARCHAR(150) NOT NULL,
  avatar VARCHAR(255) DEFAULT NULL,
  profile_path VARCHAR(255) DEFAULT NULL,
  biography TEXT DEFAULT NULL,
  known_for_department VARCHAR(80) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_actors_tmdb (tmdb_actor_id),
  KEY idx_actors_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE movies (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tmdb_id INT UNSIGNED DEFAULT NULL,
  tmdb_type ENUM('movie', 'tv') DEFAULT NULL,
  title VARCHAR(200) NOT NULL,
  original_title VARCHAR(200) DEFAULT NULL,
  overview TEXT DEFAULT NULL,
  description TEXT DEFAULT NULL,
  poster VARCHAR(255) DEFAULT NULL,
  backdrop VARCHAR(255) DEFAULT NULL,
  poster_path VARCHAR(255) DEFAULT NULL,
  backdrop_path VARCHAR(255) DEFAULT NULL,
  release_date DATE DEFAULT NULL,
  release_year SMALLINT UNSIGNED DEFAULT NULL,
  runtime SMALLINT UNSIGNED DEFAULT NULL,
  type ENUM('movie', 'series') NOT NULL DEFAULT 'movie',
  quality VARCHAR(30) DEFAULT 'HD',
  country_id INT UNSIGNED DEFAULT NULL,
  status ENUM('coming_soon', 'ongoing', 'completed') NOT NULL DEFAULT 'completed',
  is_premium TINYINT(1) NOT NULL DEFAULT 0,
  views INT UNSIGNED NOT NULL DEFAULT 0,
  rating_average DECIMAL(3,2) NOT NULL DEFAULT 0.00,
  rating_count INT UNSIGNED NOT NULL DEFAULT 0,
  vote_average DECIMAL(4,2) NOT NULL DEFAULT 0.00,
  vote_count INT UNSIGNED NOT NULL DEFAULT 0,
  popularity DECIMAL(10,3) NOT NULL DEFAULT 0.000,
  original_language VARCHAR(10) DEFAULT NULL,
  imported_at TIMESTAMP NULL DEFAULT NULL,
  last_synced_at TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_movies_tmdb (tmdb_id, tmdb_type),
  KEY idx_movies_country_id (country_id),
  KEY idx_movies_type (type),
  KEY idx_movies_tmdb_type (tmdb_type),
  KEY idx_movies_release_year (release_year),
  KEY idx_movies_release_date (release_date),
  KEY idx_movies_status (status),
  KEY idx_movies_is_premium (is_premium),
  KEY idx_movies_popularity (popularity),
  KEY idx_movies_vote_average (vote_average),
  KEY idx_movies_created_at (created_at),
  FULLTEXT KEY ft_movies_title_description (title, original_title, description, overview),
  CONSTRAINT fk_movies_country
    FOREIGN KEY (country_id) REFERENCES countries(id)
    ON UPDATE CASCADE
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE episodes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  movie_id INT UNSIGNED NOT NULL,
  episode_number INT UNSIGNED NOT NULL DEFAULT 1,
  title VARCHAR(200) NOT NULL,
  youtube_url VARCHAR(255) DEFAULT NULL,
  duration_seconds INT UNSIGNED DEFAULT NULL,
  is_published TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_episodes_movie_number (movie_id, episode_number),
  KEY idx_episodes_movie_published (movie_id, is_published),
  CONSTRAINT fk_episodes_movie
    FOREIGN KEY (movie_id) REFERENCES movies(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE movie_genres (
  movie_id INT UNSIGNED NOT NULL,
  genre_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (movie_id, genre_id),
  KEY idx_movie_genres_genre_id (genre_id),
  CONSTRAINT fk_movie_genres_movie
    FOREIGN KEY (movie_id) REFERENCES movies(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT fk_movie_genres_genre
    FOREIGN KEY (genre_id) REFERENCES genres(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE movie_actors (
  movie_id INT UNSIGNED NOT NULL,
  actor_id INT UNSIGNED NOT NULL,
  character_name VARCHAR(150) DEFAULT NULL,
  cast_order SMALLINT UNSIGNED DEFAULT NULL,
  PRIMARY KEY (movie_id, actor_id),
  KEY idx_movie_actors_actor_id (actor_id),
  KEY idx_movie_actors_cast_order (cast_order),
  CONSTRAINT fk_movie_actors_movie
    FOREIGN KEY (movie_id) REFERENCES movies(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT fk_movie_actors_actor
    FOREIGN KEY (actor_id) REFERENCES actors(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

CREATE TABLE favorites (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  movie_id INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_favorites_user_movie (user_id, movie_id),
  KEY idx_favorites_movie_id (movie_id),
  CONSTRAINT fk_favorites_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT fk_favorites_movie
    FOREIGN KEY (movie_id) REFERENCES movies(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE watch_history (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  movie_id INT UNSIGNED NOT NULL,
  episode_id INT UNSIGNED NOT NULL,
  progress_seconds INT UNSIGNED NOT NULL DEFAULT 0,
  watched_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_watch_history_user_episode (user_id, episode_id),
  KEY idx_watch_history_user_watched (user_id, watched_at),
  KEY idx_watch_history_movie_id (movie_id),
  CONSTRAINT fk_watch_history_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT fk_watch_history_movie
    FOREIGN KEY (movie_id) REFERENCES movies(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT fk_watch_history_episode
    FOREIGN KEY (episode_id) REFERENCES episodes(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE comments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  movie_id INT UNSIGNED NOT NULL,
  content TEXT NOT NULL,
  status ENUM('visible', 'hidden') NOT NULL DEFAULT 'visible',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_comments_movie_status_created (movie_id, status, created_at),
  KEY idx_comments_user_id (user_id),
  CONSTRAINT fk_comments_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT fk_comments_movie
    FOREIGN KEY (movie_id) REFERENCES movies(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ratings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  movie_id INT UNSIGNED NOT NULL,
  rating TINYINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_ratings_user_movie (user_id, movie_id),
  KEY idx_ratings_movie_id (movie_id),
  CONSTRAINT chk_ratings_rating CHECK (rating BETWEEN 1 AND 5),
  CONSTRAINT fk_ratings_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT fk_ratings_movie
    FOREIGN KEY (movie_id) REFERENCES movies(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
