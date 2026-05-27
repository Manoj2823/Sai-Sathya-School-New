-- ══════════════════════════════════════════════════════════════
--  Run this in phpMyAdmin → SQL tab (or your MySQL client)
-- ══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS school_videos (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    school      ENUM('samacheer','cbse') NOT NULL,
    title       VARCHAR(255)  NOT NULL,
    youtube_url VARCHAR(500)  NOT NULL,          -- paste any YouTube URL here
    description TEXT          DEFAULT NULL,
    sort_order  INT           DEFAULT 0,
    is_active   TINYINT(1)    DEFAULT 1,
    created_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample rows (optional — delete after testing)
INSERT INTO school_videos (school, title, youtube_url, description, sort_order) VALUES
('samacheer', 'Annual Day 2024',        'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'Annual day celebration highlights', 1),
('samacheer', 'Sports Day Highlights',  'https://youtu.be/dQw4w9WgXcQ',               'Sports meet 2024',                  2),
('cbse',      'Science Exhibition',     'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'CBSE Science fair 2024',            1),
('cbse',      'Cultural Programme',     'https://youtu.be/dQw4w9WgXcQ',                'Cultural event highlights',         2);