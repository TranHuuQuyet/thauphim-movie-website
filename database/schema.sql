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


DROP TABLE IF EXISTS movies;

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL,
  email VARCHAR(150) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
  membership ENUM('free', 'premium') NOT NULL DEFAULT 'free',
  status ENUM('active', 'locked') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_users_username (username),
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE genres (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE countries (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code CHAR(2) NOT NULL,
  name VARCHAR(100) NOT NULL,
  UNIQUE KEY uq_countries_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE actors (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  avatar VARCHAR(255) DEFAULT NULL,
  biography TEXT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE movies (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  description TEXT DEFAULT NULL,
  poster VARCHAR(255) DEFAULT NULL,
  backdrop VARCHAR(255) DEFAULT NULL,
  release_year SMALLINT UNSIGNED DEFAULT NULL,
  type ENUM('movie', 'series') NOT NULL DEFAULT 'movie',
  quality VARCHAR(30) DEFAULT 'HD',
  country_id INT UNSIGNED DEFAULT NULL,
  status ENUM('coming_soon', 'ongoing', 'completed') NOT NULL DEFAULT 'completed',
  is_premium TINYINT(1) NOT NULL DEFAULT 0,
  views INT UNSIGNED NOT NULL DEFAULT 0,
  rating_average DECIMAL(3,2) NOT NULL DEFAULT 0.00,
  rating_count INT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_movies_country_id (country_id),
  KEY idx_movies_type (type),
  KEY idx_movies_release_year (release_year),
  KEY idx_movies_status (status),
  KEY idx_movies_is_premium (is_premium),
  KEY idx_movies_created_at (created_at),
  FULLTEXT KEY ft_movies_title_description (title, description),
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
  youtube_url VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_episodes_movie_number (movie_id, episode_number),
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
  PRIMARY KEY (movie_id, actor_id),
  KEY idx_movie_actors_actor_id (actor_id),
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
  KEY idx_schedules_release_date (release_date),
  KEY idx_schedules_movie_id (movie_id),
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
