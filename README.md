# GuardPal 🛡️

<div align="center">
  
![GuardPal Logo](https://github.com/user-attachments/assets/logo-placeholder)

**The Professional Network for Security Industry Professionals**

[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-orange)](https://www.mysql.com/)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-purple)](https://getbootstrap.com/)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

[Features](#features) • [Demo](#demo) • [Installation](#installation) • [Screenshots](#screenshots) • [Tech Stack](#tech-stack)

</div>

---

## 📋 Table of Contents

- [About](#about)
- [Features](#features)
- [Demo](#demo)
- [Screenshots](#screenshots)
- [Tech Stack](#tech-stack)
- [Installation](#installation)
- [Database Setup](#database-setup)
- [Usage](#usage)
- [Test Accounts](#test-accounts)
- [API Integration](#api-integration)
- [Contributing](#contributing)
- [License](#license)
- [Contact](#contact)

---

## 🎯 About

GuardPal is a specialized job portal and professional networking platform designed exclusively for private security professionals in the UK. The platform connects security officers, supervisors, and managers with employment opportunities while facilitating professional networking within the security industry.

### Key Highlights

- **SIA License Verification**: Integrated support for displaying and verifying Security Industry Authority (SIA) licenses
- **Industry-Specific Job Search**: Powered by JSearch API with filters for security-related positions
- **Professional Networking**: Connect with other security professionals, exchange messages, and build your network
- **Comprehensive Profiles**: Showcase skills, certifications, work experience, and SIA credentials
- **Real-Time Messaging**: Built-in chat system for connected professionals

---

## ✨ Features

### 🔐 User Authentication & Profile Management
- Secure user registration and login with bcrypt password hashing
- Comprehensive profile creation with:
  - Personal information and professional bio
  - SIA license details (16-digit number, type, expiry date)
  - Skills with proficiency levels
  - Professional certifications
  - Work experience timeline
  - Profile image upload

### 💼 Job Search & Management
- Integration with JSearch API for real-time job listings
- Advanced filtering by:
  - Job type (Security Officer, Door Supervisor, CCTV Operator, etc.)
  - Location
  - Experience level
- Job bookmarking functionality
- Detailed job descriptions and application links

### 🤝 Professional Networking
- Connect with other security professionals
- Connection request system (send, accept, reject)
- View connection profiles
- Discover professionals based on:
  - Similar skills
  - Geographic location
  - SIA license type
  
### 💬 Messaging System
- Real-time chat functionality between connected users
- Message history preservation
- Chat room management
- Auto-refresh for new messages

### 🎨 User Interface
- Clean, modern design with Bootstrap 5
- Responsive layout for mobile and desktop
- Smooth animations with Anime.js
- Professional branding with custom logo
- White navbar and footer with black text
- Roboto font for professional typography

---

## 🎥 Demo

### Live Demo
*[Add your live demo link here]*

### Video Walkthrough
*[Add your video demo link here]*

---

## 📸 Screenshots

### Landing Page
The welcoming homepage showcasing GuardPal's value proposition for security professionals.

<img width="1209" alt="Landing Page" src="https://github.com/user-attachments/assets/7c044ee0-2288-4008-8688-ef265fbfa653" />

---

### User Registration
Simple and secure registration process for new security professionals.

<img width="980" alt="Registration Page" src="https://github.com/user-attachments/assets/b382508b-8dbf-4123-bae3-ad53ef1c6b36" />

---

### User Dashboard
Personalized dashboard showing profile completeness, connections, and quick access to key features.

<img width="952" alt="Dashboard" src="https://github.com/user-attachments/assets/df5012be-4009-46fb-9506-4bcdc5b288d2" />

---

### Profile Management
Comprehensive profile page displaying SIA license, skills, certifications, and work experience.

<img width="939" alt="Profile Page" src="https://github.com/user-attachments/assets/c60f93f9-dcf6-4452-8973-d8f1c04f074f" />

---

### Edit Profile
Easy-to-use form for updating professional information including SIA license details.

<img width="914" alt="Edit Profile" src="https://github.com/user-attachments/assets/1b975a78-b32a-452b-b803-d3814e620169" />

---

### Skills Management
Add and manage professional skills with proficiency levels.

<img width="925" alt="Skills Management" src="https://github.com/user-attachments/assets/4f1a395d-4860-432e-815e-3ea9cc7d636a" />

---

### Job Search
Advanced job search interface with filters for security-specific positions.

<img width="983" alt="Job Search" src="https://github.com/user-attachments/assets/5fcb71a6-fe8c-4cf3-98ac-8ba3c00d7a49" />

---

### Job Details
Detailed job information with application links and bookmark functionality.

<img width="925" alt="Job Details" src="https://github.com/user-attachments/assets/821461a1-e621-45cb-8635-6117e8d41321" />

---

### Professional Networking
Discover and connect with other security professionals in your area or with similar skills.

<img width="939" alt="Find Professionals" src="https://github.com/user-attachments/assets/d6f2c177-5bed-494a-a551-b617aa31a5d5" />

---

### My Connections
Manage your professional network with accepted connections, pending requests, and sent invitations.

<img width="920" alt="My Connections" src="https://github.com/user-attachments/assets/4ce666f8-915d-4126-8171-7280b735b399" />

---

### Messaging System
Real-time chat functionality for communicating with your professional connections.

<img width="932" alt="Chat Interface" src="https://github.com/user-attachments/assets/fc957629-d529-414c-ab0e-298a60d6bc6a" />

---

### Navigation Menu
Clean navigation with dropdown menus for easy access to all features.

<img width="888" alt="Navigation Menu" src="https://github.com/user-attachments/assets/15b6807f-3394-4bed-b0f1-ec5ac99d1ccc" />

---

### Bookmarked Jobs
Save and manage your favorite job listings for easy access.

<img width="885" alt="Bookmarked Jobs" src="https://github.com/user-attachments/assets/441f2e7a-0e3b-4a90-a169-7bae1945f793" />

---

## 🛠️ Tech Stack

### Frontend
- **HTML5** - Semantic markup
- **CSS3** - Custom styling
- **Bootstrap 5.3** - Responsive framework
- **JavaScript (ES6+)** - Interactive functionality
- **Anime.js 3.2.1** - Smooth animations
- **Font Awesome 6.0** - Icon library
- **Roboto Font** - Professional typography

### Backend
- **PHP 7.4+** - Server-side logic
- **MySQL 8.0+** - Database management
- **PDO** - Database abstraction layer

### APIs & Services
- **JSearch API (RapidAPI)** - Job search functionality
- **Google Fonts** - Custom typography

### Development Environment
- **XAMPP** - Local development server
- **phpMyAdmin** - Database management

### Security
- **Bcrypt** - Password hashing
- **Prepared Statements** - SQL injection prevention
- **Input Sanitization** - XSS protection
- **Session Management** - Secure authentication

---

## 📦 Installation

### Prerequisites
- XAMPP (or similar LAMP/WAMP stack)
- PHP 7.4 or higher
- MySQL 8.0 or higher
- Web browser (Chrome, Firefox, Safari, Edge)

### Step 1: Clone the Repository
```bash
git clone https://github.com/yourusername/guardpal.git
cd guardpal
```

### Step 2: Move to XAMPP Directory
```bash
# Windows
move guardpal C:\xampp\htdocs\

# macOS/Linux
mv guardpal /Applications/XAMPP/htdocs/
```

### Step 3: Start XAMPP Services
- Start Apache server
- Start MySQL server

### Step 4: Database Configuration
1. Open phpMyAdmin at `http://localhost/phpmyadmin`
2. Create a new database named `guardpal`
3. Import the database schema:
   ```sql
   -- Run the SQL file located at:
   database/database.sql
   ```

### Step 5: Configure Database Connection
Edit `includes/config.php` and update if necessary:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'guardpal');
```

### Step 6: API Configuration
Add your JSearch API key in `includes/config.php`:
```php
define('JSEARCH_API_KEY', 'your_api_key_here');
define('JSEARCH_API_HOST', 'jsearch.p.rapidapi.com');
```

### Step 7: Access the Application
Open your browser and navigate to:
```
http://localhost/guardpal/
```

---

## 🗄️ Database Setup

### Schema Overview
The application uses the following main tables:
- `users` - User accounts and profile information
- `skills` - User skills and proficiency levels
- `certifications` - Professional certifications
- `work_experience` - Employment history
- `connections` - Professional network relationships
- `messages` - Chat messages between users
- `chat_rooms` - Message conversation threads
- `bookmarks` - Saved job listings

### Running Migrations
Execute the SQL files in order:
1. `database/database.sql` - Create tables and schema
2. `database/profile_updates.sql` - Add profile-related fields
3. `test_users_fixed_formatted.sql` - Add demo users (optional)

### Adding Test Data
To populate the database with sample users for testing:
```bash
# Run in phpMyAdmin or MySQL command line
source test_users_fixed_formatted.sql
```

---

## 🚀 Usage

### Creating an Account
1. Navigate to the registration page
2. Fill in your details:
   - Full name
   - Username
   - Email address
   - Password
3. Click "Register" to create your account

### Completing Your Profile
1. Log in with your credentials
2. Click "Edit Profile" in the navigation menu
3. Add your information:
   - Professional details
   - SIA license number (16 digits)
   - Location and availability
   - Professional bio
4. Navigate to "Update Skills" to add your competencies
5. Add certifications and work experience

### Searching for Jobs
1. Click "Find Jobs" in the navigation
2. Enter keywords (e.g., "Security Officer", "Door Supervisor")
3. Apply filters:
   - Job type
   - Location
   - Experience level
4. Browse results and click "View Details" for more information
5. Bookmark jobs by clicking the bookmark icon

### Networking
1. Go to "Network" → "Find Professionals"
2. Search using:
   - Keywords
   - Skills
   - Location
   - SIA license type
3. Click "View Profile" to see detailed profiles
4. Click "Connect" to send a connection request
5. Manage connections in "My Connections"

### Messaging
1. Navigate to "Network" → "Messages"
2. Select a connection from your chat list
3. Type your message and click "Send"
4. Messages auto-refresh every 5 seconds

---

## 👥 Test Accounts

For demonstration purposes, the following test accounts are available:

| Name | Email | Password | Role |
|------|-------|----------|------|
| James Wilson | test1@guardpal.com | TestUser1 | Security Operations Manager |
| Emma Bailey | test2@guardpal.com | TestUser1 | Door Supervisor |
| Rashid Mahmood | test3@guardpal.com | TestUser1 | CCTV Operator |
| Olivia Singh | test4@guardpal.com | TestUser1 | Close Protection Officer |

These accounts include:
- Complete professional profiles
- SIA license information
- Skills and certifications
- Work experience
- Pre-established connections
- Sample chat conversations

---

## 🔌 API Integration

### JSearch API
GuardPal integrates with the JSearch API via RapidAPI to provide real-time job listings.

#### Setup
1. Sign up at [RapidAPI](https://rapidapi.com/)
2. Subscribe to [JSearch API](https://rapidapi.com/letscrape-6bRBa3QguO5/api/jsearch)
3. Get your API key
4. Add to `includes/config.php`

#### Endpoints Used
- `GET /search` - Search for security jobs
- `GET /job-details` - Get detailed job information

#### Rate Limits
- Free tier: 100 requests/month
- Basic tier: 1000 requests/month

---

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

### Code Style Guidelines
- Follow PSR-12 coding standards for PHP
- Use meaningful variable and function names
- Comment complex logic
- Test all features before submitting

---

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 📧 Contact

**Your Name** - [@yourtwitter](https://twitter.com/yourtwitter) - your.email@example.com

**Project Link**: [https://github.com/yourusername/guardpal](https://github.com/yourusername/guardpal)

---

## 🙏 Acknowledgments

- [JSearch API](https://rapidapi.com/letscrape-6bRBa3QguO5/api/jsearch) for job search functionality
- [Bootstrap](https://getbootstrap.com/) for the responsive framework
- [Font Awesome](https://fontawesome.com/) for icons
- [Anime.js](https://animejs.com/) for animations
- [Google Fonts](https://fonts.google.com/) for Roboto typeface

---

## 🎓 Academic Project

This project was developed as part of a web development course at [Your University/College Name]. It demonstrates:
- Full-stack web development skills
- Database design and management
- API integration
- User authentication and security
- Responsive web design
- Professional software development practices

---

<div align="center">
  
**Made with ❤️ for the Security Industry**

⭐ Star this repo if you find it helpful!

</div>

