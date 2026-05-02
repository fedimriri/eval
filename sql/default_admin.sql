-- Insert default admin user
-- Password: admin123
INSERT INTO users (name, email, password, role, created_at)
VALUES ('Admin User', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NOW());

-- Insert default manager user
-- Password: manager123
INSERT INTO users (name, email, password, role, created_at)
VALUES ('Manager User', 'manager@example.com', '$2y$10$hP3GR9xaJPPLLAkPrQI.Oe.owMYRkIHYZRHGNtkp9fzLNfqUDYSAe', 'manager', NOW());
