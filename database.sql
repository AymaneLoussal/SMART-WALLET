CREATE DATABASE smartWallet;
SHOW DATABASES;

USE smartWallet;
SELECT * FROM otp_codes;

SHOW TABLES;

CREATE TABLE users(
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE otp_codes(
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    otp_code VARCHAR(6),
    expires_at DATETIME,
    Foreign Key (user_id) REFERENCES users(id)
);

SELECT * FROM otp_codes;


ALTER TABLE users 
RENAME COLUMN name TO full_name; 

DESCRIBE users;

ALTER TABLE users
MODIFY full_name VARCHAR(100) NOT NULL,
MODIFY email VARCHAR(100) NOT NULL,
MODIFY password VARCHAR(255) NOT NULL;

CREATE TABLE cards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    card_name VARCHAR(50),
    balance DECIMAL(10,2) DEFAULT 0,
    is_primary TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

USE smartWallet;

CREATE TABLE incomes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    card_id INT,
    amount DECIMAL(10,2),
    Description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Foreign Key (user_id) REFERENCES users(id),
    Foreign Key (card_id) REFERENCES cards(id)
);
CREATE TABLE categories(
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50)
);

INSERT INTO categories (name) VALUES
('Food'), ('Rent'), ('Transport'), ('Internet'), ('other');

CREATE TABLE limits(
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    category_id INT,
    monthly_limit DECIMAL(10,2),
    Foreign Key (user_id) REFERENCES users(id),
    Foreign Key (category_id) REFERENCES categories(id)
);
CREATE Table expenses(
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    card_id INT,
    category_id INT,
    amount DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Foreign Key (user_id) REFERENCES users(id),
    Foreign Key (card_id) REFERENCES cards(id),
    Foreign Key (category_id) REFERENCES categories(id)
);

ALTER TABLE limits
ADD UNIQUE KEY uniq_user_cat (user_id, category_id);