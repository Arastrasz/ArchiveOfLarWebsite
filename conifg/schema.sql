-- ============================================================
-- THE ARCHIVES OF CLAN LAR — Database Schema
-- Run this in phpMyAdmin
-- ============================================================

CREATE DATABASE IF NOT EXISTS clanlar_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE clanlar_db;

-- Users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    display_name VARCHAR(100) DEFAULT NULL,
    avatar VARCHAR(20) DEFAULT 'default',
    bio TEXT DEFAULT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    verified TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME DEFAULT NULL,
    INDEX idx_email (email),
    INDEX idx_username (username)
) ENGINE=InnoDB;

-- Email verification codes
CREATE TABLE verification_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    code VARCHAR(6) NOT NULL,
    expires_at DATETIME NOT NULL,
    used TINYINT(1) DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Password reset tokens
CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used TINYINT(1) DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- News posts
CREATE TABLE news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    author_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    excerpt TEXT DEFAULT NULL,
    content LONGTEXT NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    published TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id),
    INDEX idx_slug (slug),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- Comments on news
CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    news_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (news_id) REFERENCES news(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_news (news_id)
) ENGINE=InnoDB;

-- Contact messages
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    name VARCHAR(100) DEFAULT NULL,
    email VARCHAR(255) DEFAULT NULL,
    category ENUM('review', 'problem', 'visual_creation', 'collaboration', 'other') NOT NULL DEFAULT 'other',
    subject VARCHAR(255) NOT NULL,
    content LONGTEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_read (is_read),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- Sessions table (optional, for DB-backed sessions)
CREATE TABLE sessions (
    id VARCHAR(128) PRIMARY KEY,
    user_id INT DEFAULT NULL,
    data TEXT,
    last_activity INT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- SEED DATA: First admin user and first news post
-- Password: change_this_immediately (bcrypt hash)
-- ============================================================

INSERT INTO users (username, email, password_hash, display_name, role, verified) VALUES
('Vaelarn', 'support@andreykuznetcoveso.com', '$2y$12$placeholder_change_this_hash_after_first_login', 'Andrey Kuznetsov', 'admin', 1);

INSERT INTO news (author_id, title, slug, excerpt, content, image) VALUES
(1, 'The Archives Are Open', 'the-archives-are-open',
'Clan Lar''s housing archive is now public. Three houses documented, one under construction. Walk through the halls — the doors are open.',
'<p>After months of building, documenting, and rebuilding again — the Archives of Clan Lar are open.</p>

<p>Three houses. Three histories. Each one built from the ground up with a single question: <em>who lives here, and what happened to them?</em></p>

<p><strong>Bastion Sanguinaris</strong> — Vampire stronghold in Blackreach. Silk banners, undying flame, and a feast hall that hasn''t emptied since the fortress was built. The Scarlet Archive contains books that don''t want to be read.</p>

<p><strong>Abagarlas</strong> — Ayleid ruin above the Bastion, reached through a chimney passage in the library and a forgotten road cut through stone that predates the clan by ages. 21 pages of illustrated lore.</p>

<p><strong>Creature-From-Beyond</strong> — This one got built before the story did. The house is open. The written record hasn''t caught up yet.</p>

<p><strong>New-Sheoth Palace</strong> — Sheogorath''s palace in the Shivering Isles. Under construction.</p>

<p>Every build starts with a wall and ends with a story. The archives hold what comes between.</p>

<p>All houses are open on <strong>PC–EU</strong>. Visit @Vaelarn in-game.</p>',
'img/crows-wood.jpg');
