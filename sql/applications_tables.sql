-- Run in phpMyAdmin → school_website database → SQL tab

CREATE TABLE IF NOT EXISTS admission_applications (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    school        ENUM('samacheer','cbse') NOT NULL,
    student_name  VARCHAR(150) NOT NULL,
    date_of_birth DATE         NOT NULL,
    class_applied VARCHAR(50)  NOT NULL,
    gender        VARCHAR(20)  NOT NULL,
    parent_name   VARCHAR(150) NOT NULL,
    phone         VARCHAR(15)  NOT NULL,
    email         VARCHAR(150) NOT NULL,
    address       TEXT         NOT NULL,
    created_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS teacher_applications (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    school           ENUM('samacheer','cbse') NOT NULL,
    full_name        VARCHAR(150) NOT NULL,
    date_of_birth    DATE         NOT NULL,
    qualification    VARCHAR(150) NOT NULL,
    years_experience INT          NOT NULL DEFAULT 0,
    subjects         VARCHAR(200) NOT NULL,
    gender           VARCHAR(20)  NOT NULL,
    phone            VARCHAR(15)  NOT NULL,
    email            VARCHAR(150) NOT NULL,
    resume_path      VARCHAR(300) DEFAULT NULL,
    address          TEXT         NOT NULL,
    created_at       TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
