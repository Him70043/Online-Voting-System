# Online Voting System - Complete Setup Guide

## System Overview
This is a PHP-based online voting system that allows users to:
- Register and login
- Vote for their favorite programming language
- View voting statistics
- Manage their profile

## Prerequisites
You need a local web server environment with:
- Apache Web Server
- PHP (version 5.6 or higher)
- MySQL Database
- phpMyAdmin (for database management)

## Installation Options

### Option 1: XAMPP (Recommended)
1. Download XAMPP from: https://www.apachefriends.org/download.html
2. Install XAMPP to C:\xampp
3. Start Apache and MySQL services from XAMPP Control Panel

### Option 2: WAMP
1. Download WAMP from: http://www.wampserver.com/
2. Install and start services

### Option 3: Using Docker (Alternative)
If you prefer Docker, we can set up a containerized environment.

## Setup Steps

### Step 1: Install XAMPP
1. Download and install XAMPP
2. Start Apache and MySQL from XAMPP Control Panel
3. Verify installation by visiting http://localhost

### Step 2: Setup Project Files
1. Copy the Online-Voting-System folder to C:\xampp\htdocs\
2. The project should be accessible at http://localhost/Online-Voting-System/

### Step 3: Database Setup
1. Open phpMyAdmin: http://localhost/phpmyadmin/
2. Create a new database named 'polltest'
3. Import the database file: db/polltest.sql

### Step 4: Configuration
The system is pre-configured to connect to:
- Host: localhost
- Username: root
- Password: (empty)
- Database: polltest

### Step 5: Access the System
- Main page: http://localhost/Online-Voting-System/
- Login page: http://localhost/Online-Voting-System/login.php
- Registration: http://localhost/Online-Voting-System/register.php

## Default Test Users
The database comes with pre-loaded test users. You can also register new users.

## Features
- User Registration and Authentication
- Secure Login System
- Voting for Programming Languages (Java, Python, C++, PHP, .NET)
- Real-time Vote Counting
- User Profile Management
- Vote Statistics Display
- Responsive Bootstrap Design

## Troubleshooting
- Ensure Apache and MySQL are running
- Check database connection in connection.php
- Verify file permissions
- Check PHP error logs in XAMPP

## Security Notes
- This is a demo system - implement additional security for production use
- Passwords are MD5 hashed (consider upgrading to bcrypt)
- Add input validation and sanitization for production