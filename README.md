# NotesApp

A modern, intuitive note-taking application built with PHP and MySQL. Create, organize, and share your notes seamlessly.

## ✨ Features

- **Secure Authentication** - Sign up, login, password reset functionality
- **Public Sharing** - Share your thoughts through your notes anytime and anywhere.
- **Note Management** - Create, edit, delete, and organize your notes
- **Rich Text Editor** - Format your notes with a powerful WYSIWYG editor
- **Note Sharing** - Share notes publicly with anyone via secure links
- **Tags & Search** - Organize notes with tags and find them quickly
- **Responsive Design** - Works perfectly on desktop and mobile devices
- **Dark/Light Mode** - Choose your preferred visual theme

##  Quick Start

### Prerequisites
- PHP 7.4+
- MySQL 5.7+
- Apache/XAMPP/WAMP server
- Composer

### Installation

1. Clone the repository:
```bash
git clone 
cd NotesApp
```

2. Install dependencies:
```bash
composer install
```

3. Configure database:
   - Open `config/config.php`
   - Update database settings:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'notes_app');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

4. Initialize database:
```bash
php config/init_db.php
```

5. Set up email configuration (for password reset):
   - Update SMTP settings in `config/config.php`

6. Launch the app:
   - Place in your web server directory (e.g., XAMPP htdocs)
   - Visit `http://localhost/NotesApp`

## 🔒 Security

- Password hashing with bcrypt
- PDO prepared statements to prevent SQL injection
- CSRF protection
- XSS prevention
- Rate limiting for login attempts
- Secure session management

## 🛠️ Technologies

- **Backend**: PHP, MySQL
- **Frontend**: HTML5, CSS3, JavaScript
- **Libraries**: TinyMCE (rich text editor), Bootstrap (responsive design)
- **Tools**: Composer (dependency management)

## 📁 Project Structure

```
NotesApp/
├── assets/        # CSS, JS, images
├── config/        # Configuration files
├── includes/      # PHP utilities
├── models/        # Database models
│   ├── User.php   # User authentication
│   └── Note.php   # Note management
├── vendor/        # Composer dependencies
└── views/         # Frontend templates
```

## 👥 Contributors

- [@TechyCSR](https://techycsr.me)
- [@NileshRana7500](https://github.com/NileshRana7500)

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details. 
