# 🗳️ Secure Electronic Voting System

A comprehensive, secure online voting system with advanced security features and multi-question voting capabilities.

![Voting System](https://img.shields.io/badge/Status-Production%20Ready-brightgreen)
![Security](https://img.shields.io/badge/Security-14%2F14%20Features-success)
![PHP](https://img.shields.io/badge/PHP-8.2-blue)
![MySQL](https://img.shields.io/badge/MySQL-8.0-orange)

## 🎯 Project Overview

This is a secure, web-based electronic voting system developed with comprehensive security implementations. The system supports multiple voting scenarios including programming language preferences, team member selection, and custom voting questions.

## ✨ Key Features

### 🔒 Security Features (14/14 Implemented)
- ✅ **Password Security** - bcrypt hashing with salt
- ✅ **CSRF Protection** - All forms protected against Cross-Site Request Forgery
- ✅ **XSS Protection** - Input/output sanitization and encoding
- ✅ **Session Security** - Secure session management with regeneration
- ✅ **Brute Force Protection** - Account lockout system
- ✅ **Security Logging** - Comprehensive event logging
- ✅ **HTTP Security Headers** - CSP, X-Frame-Options, HSTS, etc.
- ✅ **Input Validation** - All inputs validated and sanitized
- ✅ **Database Security** - Prepared statements and secure connections
- ✅ **Admin Security** - Enhanced admin panel protections
- ✅ **Vote Integrity** - Audit trails and verification systems
- ✅ **System Configuration Security** - Hardened system settings
- ✅ **File Permissions** - Secure file access management
- ✅ **Environment Configuration** - Secure config management

### 🗳️ Voting Features
- **Multi-Question Voting** - Support for multiple voting scenarios
- **Team Member Selection** - Vote for team members with detailed profiles
- **Programming Language Voting** - Vote for favorite programming languages
- **Real-time Results** - Live vote counting and statistics
- **Vote Verification** - Integrity checks and audit trails
- **One Vote Per User** - Prevents duplicate voting

### 👥 User Management
- **Secure Registration** - User account creation with validation
- **Authentication System** - Secure login/logout functionality
- **User Profiles** - Manage user information and voting history
- **Admin Panel** - Comprehensive administrative controls
- **Role-based Access** - Different access levels for users and admins

## 🛠️ Technology Stack

- **Backend:** PHP 8.2
- **Database:** MySQL 8.0
- **Frontend:** HTML5, CSS3, Bootstrap 4
- **JavaScript:** ES6+ for dynamic interactions
- **Security:** Custom security framework with 14 security layers
- **Server:** Apache 2.4 / PHP Built-in Server

## 📋 System Requirements

### Minimum Requirements
- **PHP:** 7.4 or higher (Recommended: PHP 8.2)
- **MySQL:** 5.7 or higher (Recommended: MySQL 8.0)
- **Web Server:** Apache 2.4 or Nginx
- **Memory:** 512MB RAM minimum
- **Storage:** 100MB free space

### Recommended Environment
- **XAMPP 8.2.12** (includes Apache, MySQL, PHP)
- **phpMyAdmin** for database management
- **Modern web browser** (Chrome, Firefox, Safari, Edge)

## 🚀 Installation & Setup

### Option 1: Automated Setup (Recommended)

1. **Run the PowerShell Setup Script:**
   ```powershell
   .\Setup-VotingSystem.ps1
   ```

2. **Or use the Batch Script:**
   ```batch
   setup_voting_system.bat
   ```

### Option 2: Manual Setup

1. **Install XAMPP:**
   - Download from: https://www.apachefriends.org/download.html
   - Install to default location: `C:\xampp`

2. **Start Services:**
   - Open XAMPP Control Panel
   - Start Apache and MySQL services

3. **Setup Project Files:**
   ```bash
   # Copy project to XAMPP htdocs
   cp -r Online-Voting-System C:\xampp\htdocs\
   ```

4. **Database Setup:**
   - Open phpMyAdmin: http://localhost/phpmyadmin/
   - Create database: `polltest`
   - Import: `db/polltest.sql`

5. **Launch System:**
   ```bash
   # Using PHP built-in server (Recommended)
   php -S localhost:8080 -t Online-Voting-System
   
   # Or access via Apache
   # http://localhost/Online-Voting-System/
   ```

## 🌐 Access URLs

### Main System Access
- **Primary URL:** http://localhost:8080/
- **Apache URL:** http://localhost/Online-Voting-System/

### User Interface
- **🏠 Home Page:** http://localhost:8080/index.php
- **📝 Registration:** http://localhost:8080/register.php
- **🔐 Login:** http://localhost:8080/login.php
- **🗳️ Voting Interface:** http://localhost:8080/voter.php
- **👤 User Profile:** http://localhost:8080/profile.php
- **📊 Results:** http://localhost:8080/lan_view.php

### Administrative Access
- **🔒 Admin Login:** http://localhost:8080/admin_login.php
- **📈 Admin Dashboard:** http://localhost:8080/admin_dashboard.php
- **🛡️ Security Dashboard:** http://localhost:8080/security_dashboard.php
- **🗄️ Database Management:** http://localhost/phpmyadmin/

## 📁 Project Structure

```
Online-Voting-System/
├── 📁 includes/              # Security and utility classes
│   ├── ConfigManager.php     # Configuration management
│   ├── PasswordSecurity.php  # Password hashing and validation
│   ├── CSRFProtection.php    # CSRF token management
│   ├── XSSProtection.php     # XSS prevention utilities
│   ├── SessionSecurity.php   # Secure session handling
│   ├── SecurityLogger.php    # Security event logging
│   └── ...                   # Other security components
├── 📁 css/                   # Stylesheets
├── 📁 js/                    # JavaScript files
├── 📁 images/                # System images and assets
├── 📁 db/                    # Database files and migrations
├── 📁 logs/                  # Security and system logs
├── 📁 config/                # Configuration files
├── 🗳️ index.php             # Main voting page
├── 🔐 login.php              # User login
├── 📝 register.php           # User registration
├── 👤 profile.php            # User profile management
├── 🗳️ voter.php              # Voting interface
├── 📊 lan_view.php           # Results display
├── 🔒 admin_login.php        # Admin authentication
├── 📈 admin_dashboard.php    # Admin control panel
├── 🛡️ security_dashboard.php # Security monitoring
└── 📋 README.md              # This file
```

## 🔧 Configuration

### Database Configuration
Edit `connection.php` or `secure_connection.php`:
```php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'polltest';
```

### Security Configuration
The system includes comprehensive security settings that can be customized in the `includes/` directory.

## 🧪 Testing

### Security Testing
Run the verification scripts to test security implementations:
```bash
php verify_csrf_implementation.php
php verify_xss_implementation.php
php verify_session_security.php
# ... and other verification scripts
```

### Functional Testing
1. **User Registration:** Create new user accounts
2. **Login System:** Test authentication
3. **Voting Process:** Cast votes and verify results
4. **Admin Functions:** Test administrative features

## 🛡️ Security Best Practices

### For Production Deployment
1. **Change Default Passwords:** Update all default credentials
2. **Enable HTTPS:** Use SSL/TLS certificates
3. **Regular Updates:** Keep system components updated
4. **Monitor Logs:** Review security logs regularly
5. **Backup Database:** Regular database backups
6. **Firewall Configuration:** Proper network security

### Security Monitoring
- Check `logs/security.log` for security events
- Monitor failed login attempts
- Review admin access logs
- Verify vote integrity reports

## 🤝 Contributing

Contributions are welcome! Please follow these guidelines:

1. **Fork the repository**
2. **Create a feature branch:** `git checkout -b feature/new-feature`
3. **Commit changes:** `git commit -am 'Add new feature'`
4. **Push to branch:** `git push origin feature/new-feature`
5. **Submit a Pull Request**

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support & Troubleshooting

### Common Issues
1. **Apache not starting:** Check port 80 availability
2. **Database connection failed:** Verify MySQL service is running
3. **Permission denied:** Check file permissions
4. **Security errors:** Review security logs

### Getting Help
- Check the documentation files in the project
- Review security implementation guides
- Examine log files for error details

## 📊 System Statistics

- **Security Features:** 14/14 implemented
- **Code Quality:** Production ready
- **Test Coverage:** Comprehensive security testing
- **Performance:** Optimized for high availability
- **Compatibility:** Cross-browser support

## 🎯 Future Enhancements

- [ ] Multi-language support
- [ ] Mobile application
- [ ] Advanced analytics dashboard
- [ ] Blockchain integration for vote verification
- [ ] API endpoints for third-party integration

## 👨‍💻 Author & Contact

**Developed by:** Himanshu Kumar  
**Project Type:** Secure Electronic Voting System  
**Version:** 2.0 (Production Ready)  
**Last Updated:** October 2025

---

## 🙏 Acknowledgments

Special thanks to all contributors and the open-source community for making this secure voting system possible.

---

**⚡ Ready to vote securely? Start the system and experience the future of electronic voting!**