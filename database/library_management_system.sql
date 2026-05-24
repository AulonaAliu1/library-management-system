CREATE DATABASE IF NOT EXISTS library_management_system
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE library_management_system;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS borrowings;
DROP TABLE IF EXISTS requests;
DROP TABLE IF EXISTS password_resets;
DROP TABLE IF EXISTS contact_messages;
DROP TABLE IF EXISTS books;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    role ENUM('admin', 'member') NOT NULL DEFAULT 'member',
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE password_resets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    token_hash CHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_password_resets_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE books (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    author VARCHAR(150) NOT NULL,
    category VARCHAR(100) DEFAULT '',
    description TEXT,
    isbn VARCHAR(40) DEFAULT '',
    total_quantity INT UNSIGNED NOT NULL DEFAULT 0,
    available_quantity INT UNSIGNED NOT NULL DEFAULT 0,
    borrowed_quantity INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE requests (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    book_id INT UNSIGNED NOT NULL,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    request_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_requests_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_requests_book FOREIGN KEY (book_id) REFERENCES books (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE borrowings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    book_id INT UNSIGNED NOT NULL,
    borrow_date DATE NOT NULL,
    return_date DATE NOT NULL,
    status ENUM('active', 'returned') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_borrowings_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_borrowings_book FOREIGN KEY (book_id) REFERENCES books (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE contact_messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    subject VARCHAR(200) DEFAULT '',
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO users (id, name, username, email, role, password) VALUES
(1, 'Admin', 'Admin', 'admin@library.local', 'admin', '$2y$10$jPa19oLU5BgzissD0qzv4uu/AYTcntkv3IIAhWvQ3zk9onsRT3axi'),
(2, 'Aulona', 'Aulona', 'aulona@library.local', 'member', '$2y$10$2LGXWJPdVdW5WgI4fryjaed0s73lHrd/QqRyjlg7WrPJo6V.xlhP2'),
(3, 'Eliza', 'Eliza', 'eliza@library.local', 'member', '$2y$10$2LGXWJPdVdW5WgI4fryjaed0s73lHrd/QqRyjlg7WrPJo6V.xlhP2'),
(4, 'Erdoart', 'Erdoart', 'erdoart@library.local', 'member', '$2y$10$2LGXWJPdVdW5WgI4fryjaed0s73lHrd/QqRyjlg7WrPJo6V.xlhP2'),
(5, 'Lindrit', 'Lindrit', 'lindrit@library.local', 'member', '$2y$10$2LGXWJPdVdW5WgI4fryjaed0s73lHrd/QqRyjlg7WrPJo6V.xlhP2');

INSERT INTO books (id, title, author, category, description, isbn, total_quantity, available_quantity, borrowed_quantity) VALUES
(1, 'Clean Code', 'Robert C. Martin', 'Software Engineering', 'A practical guide to writing readable, maintainable, and disciplined code.', '978-0132350884', 5, 2, 3),
(2, 'Introduction to Algorithms', 'Thomas H. Cormen', 'Algorithms', 'A comprehensive textbook covering core algorithm design and analysis techniques.', '978-0262033848', 8, 6, 2),
(3, 'The Pragmatic Programmer', 'Andrew Hunt and David Thomas', 'Programming', 'Timeless advice for improving craft, teamwork, and practical software delivery.', '978-0135957059', 6, 0, 6),
(4, 'Deep Work', 'Cal Newport', 'Self-help', 'Strategies for focused work and better productivity in a distracted environment.', '978-1455586691', 6, 2, 4),
(5, 'Computer Networks', 'Andrew S. Tanenbaum', 'Networking', 'A foundation in network architectures, protocols, and real-world communication systems.', '978-0132126953', 4, 0, 4),
(6, 'Database System Concepts', 'Abraham Silberschatz', 'Database', 'A broad introduction to database design, SQL, transactions, and data management.', '978-0073523323', 9, 7, 2),
(7, 'Design Patterns', 'Erich Gamma et al.', 'Software Engineering', 'Classic reusable object-oriented design solutions for common software problems.', '978-0201633610', 4, 0, 4),
(8, 'Refactoring', 'Martin Fowler', 'Programming', 'Improving existing code through small, safe structural changes.', '978-0134757599', 7, 4, 3),
(9, 'Code Complete', 'Steve McConnell', 'Software Engineering', 'A comprehensive guide to software construction and best coding practices.', '978-0735619678', 6, 4, 2),
(10, 'Algorithms Unlocked', 'Thomas H. Cormen', 'Algorithms', 'An accessible introduction to algorithms for beginners.', '978-0262518802', 6, 1, 5),
(11, 'You Don''t Know JS', 'Kyle Simpson', 'Programming', 'A deep dive into the core mechanisms of JavaScript.', '978-1491904244', 7, 5, 2),
(12, 'Atomic Habits', 'James Clear', 'Self-help', 'A guide to building good habits and breaking bad ones.', '978-0735211292', 6, 0, 6),
(13, 'Computer Networking: A Top-Down Approach', 'James F. Kurose', 'Networking', 'A modern approach to understanding networking principles.', '978-0133594140', 5, 1, 4),
(14, 'SQL Fundamentals', 'John J. Patrick', 'Database', 'An introduction to SQL and relational database concepts.', '978-0131407336', 8, 6, 2),
(15, 'Working Effectively with Legacy Code', 'Michael Feathers', 'Software Engineering', 'Techniques for safely modifying and improving legacy systems.', '978-0131177055', 4, 0, 4);

INSERT INTO requests (id, user_id, book_id, status, request_date) VALUES
(1, 2, 1, 'rejected', '2026-03-15'),
(2, 4, 3, 'approved', '2026-03-18'),
(3, 3, 2, 'rejected', '2026-03-20'),
(4, 5, 1, 'approved', '2026-03-22'),
(5, 2, 10, 'approved', '2026-04-27'),
(6, 2, 13, 'rejected', '2026-04-27'),
(7, 2, 10, 'approved', '2026-04-29'),
(8, 2, 5, 'approved', '2026-04-29'),
(9, 3, 4, 'pending', '2026-04-29'),
(10, 3, 5, 'rejected', '2026-04-29'),
(11, 5, 13, 'pending', '2026-04-29'),
(12, 5, 10, 'pending', '2026-04-29'),
(13, 4, 5, 'pending', '2026-04-29'),
(14, 4, 10, 'pending', '2026-04-29'),
(15, 2, 10, 'rejected', '2026-04-30'),
(16, 2, 10, 'approved', '2026-05-06'),
(17, 2, 1, 'rejected', '2026-05-06');

INSERT INTO borrowings (id, user_id, book_id, borrow_date, return_date, status) VALUES
(1, 2, 1, '2026-03-10', '2026-03-24', 'returned'),
(2, 4, 3, '2026-02-01', '2026-02-15', 'returned'),
(3, 3, 2, '2026-03-05', '2026-03-19', 'returned'),
(4, 5, 3, '2026-01-12', '2026-01-26', 'returned'),
(5, 2, 10, '2026-04-27', '2026-05-11', 'returned'),
(6, 2, 12, '2026-04-27', '2026-05-11', 'returned'),
(7, 2, 6, '2026-04-27', '2026-05-11', 'returned'),
(8, 5, 1, '2026-04-29', '2026-05-13', 'active'),
(9, 2, 10, '2026-04-29', '2026-05-13', 'active'),
(10, 3, 12, '2026-04-29', '2026-05-13', 'active'),
(11, 3, 1, '2026-04-29', '2026-05-13', 'active'),
(12, 3, 12, '2026-04-29', '2026-05-13', 'active'),
(13, 2, 10, '2026-04-30', '2026-05-14', 'active'),
(14, 2, 10, '2026-05-06', '2026-05-20', 'active'),
(15, 2, 5, '2026-05-06', '2026-05-20', 'active');

ALTER TABLE users AUTO_INCREMENT = 6;
ALTER TABLE books AUTO_INCREMENT = 16;
ALTER TABLE requests AUTO_INCREMENT = 18;
ALTER TABLE borrowings AUTO_INCREMENT = 16;
ALTER TABLE contact_messages AUTO_INCREMENT = 1;
