
ALTER TABLE admin_users 
  ADD COLUMN IF NOT EXISTS role ENUM('admin','user') DEFAULT 'user';

UPDATE admin_users SET role = 'admin';

ALTER TABLE gallery_images
  ADD COLUMN IF NOT EXISTS uploaded_by VARCHAR(100) DEFAULT NULL;


ALTER TABLE hero_slides
  ADD COLUMN IF NOT EXISTS uploaded_by VARCHAR(100) DEFAULT NULL;