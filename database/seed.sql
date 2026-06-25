USE thauphim;

SET NAMES utf8mb4;

-- Default local admin account:
-- username: admin
-- email: admin@thauphim.local
-- password: admin123
INSERT INTO users (id, username, email, password_hash, role, membership, status) VALUES
(1, 'admin', 'admin@thauphim.local', '$2y$10$A8396LoZIOhpUhnbNx7JfeQwG1Er0COUpCNgknpxLs0AaeDzMT2B6', 'admin', 'premium', 'active');
