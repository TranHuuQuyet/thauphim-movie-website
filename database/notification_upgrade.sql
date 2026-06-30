SET NAMES utf8mb4;

SET @show_time_exists = (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'schedules'
    AND COLUMN_NAME = 'show_time'
);

SET @show_time_sql = IF(
  @show_time_exists = 0,
  'ALTER TABLE schedules ADD COLUMN show_time TIME NULL AFTER release_date',
  'SELECT ''schedules.show_time already exists'' AS message'
);

PREPARE show_time_stmt FROM @show_time_sql;
EXECUTE show_time_stmt;
DEALLOCATE PREPARE show_time_stmt;

CREATE TABLE IF NOT EXISTS notification_reads (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  schedule_id INT UNSIGNED NOT NULL,
  read_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_notification_reads_user_schedule (user_id, schedule_id),
  KEY idx_notification_reads_schedule_id (schedule_id),
  CONSTRAINT fk_notification_reads_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT fk_notification_reads_schedule
    FOREIGN KEY (schedule_id) REFERENCES schedules(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
