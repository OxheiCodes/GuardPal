-- GuardPal Database Schema
-- Security Professionals Networking and Job Platform

-- Drop existing tables if they exist to avoid conflicts during setup
DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS chat_rooms;
DROP TABLE IF EXISTS connections;
DROP TABLE IF EXISTS agency_connections;
DROP TABLE IF EXISTS bookmarks;
DROP TABLE IF EXISTS work_experience;
DROP TABLE IF EXISTS skills;
DROP TABLE IF EXISTS certifications;
DROP TABLE IF EXISTS job_listings;
DROP TABLE IF EXISTS agencies;
DROP TABLE IF EXISTS users;

-- Create Users table
CREATE TABLE users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) DEFAULT NULL,
    profile_image VARCHAR(255) DEFAULT NULL,
    job_title VARCHAR(100) DEFAULT NULL,
    years_experience INT(11) DEFAULT 0,
    bio TEXT DEFAULT NULL,
    location VARCHAR(100) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    website VARCHAR(255) DEFAULT NULL,
    availability ENUM('Available', 'Not Available', 'Open to Opportunities') DEFAULT NULL,
    sia_license_number VARCHAR(16) DEFAULT NULL,
    sia_license_type ENUM('Door Supervision', 'Security Guarding', 'CCTV', 'Close Protection', 'Cash and Valuables in Transit', 'Public Space Surveillance', 'Key Holding') DEFAULT NULL,
    sia_expiry_date DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Agencies table
CREATE TABLE agencies (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    logo_image VARCHAR(255) DEFAULT NULL,
    website VARCHAR(255) DEFAULT NULL,
    phone VARCHAR(50) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    registration_number VARCHAR(100) DEFAULT NULL,
    verified TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create Agency-User Connections table
CREATE TABLE agency_connections (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    agency_id INT(11) NOT NULL,
    user_id INT(11) NOT NULL,
    status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (agency_id) REFERENCES agencies(id) ON UPDATE RESTRICT ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE RESTRICT ON DELETE CASCADE
);

-- Create Job Listings table
CREATE TABLE job_listings (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    agency_id INT(11) NOT NULL,
    job_title VARCHAR(255) NOT NULL,
    job_description TEXT NOT NULL,
    job_location VARCHAR(255) DEFAULT NULL,
    job_type VARCHAR(100) DEFAULT NULL,
    salary_range VARCHAR(100) DEFAULT NULL,
    requirements TEXT DEFAULT NULL,
    application_url VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (agency_id) REFERENCES agencies(id) ON UPDATE RESTRICT ON DELETE CASCADE
);

-- Create Certifications table
CREATE TABLE certifications (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    name VARCHAR(255) NOT NULL,
    issuing_organization VARCHAR(255) NOT NULL,
    issue_date DATE NOT NULL,
    expiry_date DATE DEFAULT NULL,
    credential_id VARCHAR(100) DEFAULT NULL,
    credential_url VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE RESTRICT ON DELETE CASCADE
);

-- Create Skills table
CREATE TABLE skills (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    name VARCHAR(100) NOT NULL,
    proficiency ENUM('Beginner', 'Intermediate', 'Advanced', 'Expert') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE RESTRICT ON DELETE CASCADE
);

-- Create Work Experience table
CREATE TABLE work_experience (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    company_name VARCHAR(255) NOT NULL,
    job_title VARCHAR(255) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE DEFAULT NULL,
    is_current TINYINT(1) DEFAULT 0,
    location VARCHAR(255) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE RESTRICT ON DELETE CASCADE
);

-- Create Connections table for professional networking
CREATE TABLE connections (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    requester_id INT(11) NOT NULL,
    receiver_id INT(11) NOT NULL,
    status ENUM('pending', 'accepted', 'rejected') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (requester_id) REFERENCES users(id) ON UPDATE RESTRICT ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON UPDATE RESTRICT ON DELETE CASCADE
);

-- Create Chat Rooms table
CREATE TABLE chat_rooms (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user1_id INT(11) NOT NULL,
    user2_id INT(11) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user1_id) REFERENCES users(id) ON UPDATE RESTRICT ON DELETE RESTRICT,
    FOREIGN KEY (user2_id) REFERENCES users(id) ON UPDATE RESTRICT ON DELETE RESTRICT
);

-- Create Messages table
CREATE TABLE messages (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    sender_id INT(11) NOT NULL,
    receiver_id INT(11) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON UPDATE RESTRICT ON DELETE RESTRICT,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON UPDATE RESTRICT ON DELETE RESTRICT
);

-- Create Bookmarks table for saving job listings
CREATE TABLE bookmarks (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    job_id VARCHAR(100) NOT NULL,
    job_title VARCHAR(255) DEFAULT NULL,
    company_name VARCHAR(100) DEFAULT NULL,
    job_description TEXT DEFAULT NULL,
    job_listing_id INT(11) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE RESTRICT ON DELETE RESTRICT,
    FOREIGN KEY (job_listing_id) REFERENCES job_listings(id) ON UPDATE RESTRICT ON DELETE CASCADE
);

-- Create indexes for better performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_location ON users(location);
CREATE INDEX idx_users_sia_license ON users(sia_license_number);
CREATE INDEX idx_skills_name ON skills(name);
CREATE INDEX idx_skills_user ON skills(user_id);
CREATE INDEX idx_certifications_user ON certifications(user_id);
CREATE INDEX idx_certifications_name ON certifications(name);
CREATE INDEX idx_work_experience_user ON work_experience(user_id);
CREATE INDEX idx_connections_requester ON connections(requester_id);
CREATE INDEX idx_connections_receiver ON connections(receiver_id);
CREATE INDEX idx_connections_status ON connections(status);
CREATE INDEX idx_messages_sender ON messages(sender_id);
CREATE INDEX idx_messages_receiver ON messages(receiver_id);
CREATE INDEX idx_bookmarks_user ON bookmarks(user_id);
CREATE INDEX idx_bookmarks_job_listing ON bookmarks(job_listing_id);
CREATE INDEX idx_job_listings_agency ON job_listings(agency_id);
CREATE INDEX idx_job_listings_location ON job_listings(job_location);
CREATE INDEX idx_job_listings_type ON job_listings(job_type);
CREATE INDEX idx_agency_connections_agency ON agency_connections(agency_id);
CREATE INDEX idx_agency_connections_user ON agency_connections(user_id);

-- Add unique constraints
ALTER TABLE connections ADD CONSTRAINT unique_connection UNIQUE (requester_id, receiver_id);
ALTER TABLE chat_rooms ADD CONSTRAINT unique_chat_room UNIQUE (user1_id, user2_id);
ALTER TABLE bookmarks ADD CONSTRAINT unique_bookmark UNIQUE (user_id, job_id);
ALTER TABLE agency_connections ADD CONSTRAINT unique_agency_connection UNIQUE (agency_id, user_id);