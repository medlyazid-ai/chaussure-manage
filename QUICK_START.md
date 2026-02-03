# 🚀 Quick Start Guide

Get your Chaussure Management System up and running in minutes!

## ⚡ Prerequisites

- **PHP**: Version 7.4 or higher
- **MySQL/MariaDB**: Version 5.7+ / 10.2+
- **Web Server**: Apache or Nginx
- **Extensions**: PDO, PDO_MySQL

## 📦 Installation Steps

### 1. Clone or Download the Repository

```bash
# If using Git
git clone https://github.com/medlyazid-ai/chaussure-manage.git
cd chaussure-manage

# Or download and extract the ZIP file
```

### 2. Set Up Database

**Option A: Using phpMyAdmin**
1. Open phpMyAdmin
2. Create a new database (e.g., `chaussure_db`)
3. Select the database
4. Go to "Import" tab
5. Choose file: `sql/quwaejeq_chaussure_manage_db.sql`
6. Click "Go"

**Option B: Using Command Line**
```bash
# Create database
mysql -u root -p -e "CREATE DATABASE chaussure_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"

# Import SQL file
mysql -u root -p chaussure_db < sql/quwaejeq_chaussure_manage_db.sql
```

### 3. Configure Database Connection

**Copy the environment template:**
```bash
cp .env.example .env
```

**Edit `.env` file with your credentials:**
```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=chaussure_db
DB_USER=your_mysql_username
DB_PASSWORD=your_mysql_password
```

**Update `config/db.php` to use environment variables:**

The file is already set up to use hardcoded credentials. You should update it to read from `.env`:

```php
<?php

class Database
{
    public static function getInstance()
    {
        static $pdo = null;

        if ($pdo === null) {
            // Load .env file
            $envFile = __DIR__ . '/../.env';
            if (file_exists($envFile)) {
                $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    if (strpos(trim($line), '#') === 0) continue;
                    list($name, $value) = explode('=', $line, 2);
                    $_ENV[trim($name)] = trim($value);
                }
            }
            
            $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
            $db   = $_ENV['DB_NAME'] ?? 'database';
            $user = $_ENV['DB_USER'] ?? 'root';
            $pass = $_ENV['DB_PASSWORD'] ?? '';
            $port = $_ENV['DB_PORT'] ?? 3306;
            $charset = 'utf8mb4';

            $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];

            try {
                $pdo = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                die("❌ Database connection error: " . $e->getMessage());
            }
        }

        return $pdo;
    }
}
```

### 4. Set Up Web Server

**Option A: Using PHP Built-in Server (Development Only)**
```bash
cd /path/to/chaussure-manage
php -S localhost:8000
```
Then open: http://localhost:8000

**Option B: Using Apache**

Create a virtual host configuration:

```apache
<VirtualHost *:80>
    ServerName chaussure.local
    DocumentRoot /path/to/chaussure-manage
    
    <Directory /path/to/chaussure-manage>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/chaussure-error.log
    CustomLog ${APACHE_LOG_DIR}/chaussure-access.log combined
</VirtualHost>
```

Add to `/etc/hosts`:
```
127.0.0.1  chaussure.local
```

**Option C: Using Nginx**

```nginx
server {
    listen 80;
    server_name chaussure.local;
    root /path/to/chaussure-manage;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 5. Set Directory Permissions

```bash
# Make uploads directory writable
chmod -R 755 uploads/

# If needed, make it writable by web server
sudo chown -R www-data:www-data uploads/  # For Apache/Nginx on Ubuntu
# or
sudo chown -R apache:apache uploads/      # For Apache on CentOS
```

### 6. Access the Application

Open your browser and navigate to:
- Development: `http://localhost:8000`
- Virtual Host: `http://chaussure.local`
- Or your configured URL

You should see the login page!

## 👤 Default Login

**Important**: The database import includes sample data. To create an admin user:

### Option 1: Register Through Interface (if enabled)
Navigate to `/register` and create an account.

### Option 2: Create User via SQL

```sql
-- Create admin user with password 'admin123'
INSERT INTO users (username, password, email, role) 
VALUES (
  'admin', 
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',  -- password: 'password'
  'admin@example.com',
  'admin'
);
```

**Login with**:
- Username: `admin`
- Password: `password`

**⚠️ IMPORTANT**: Change this password immediately after first login!

## ✅ Verify Installation

After logging in, you should see:
1. **Dashboard** with statistics
2. **Products** menu
3. **Suppliers** menu
4. **Orders** menu
5. **Payments** menu
6. **Stocks** menu
7. **Client Sales** menu

## 🔧 Initial Configuration

### 1. Add Your Business Locations

Navigate to the countries management (or add via SQL):

```sql
INSERT INTO countries (name, code) VALUES 
('Morocco', 'MA'),
('France', 'FR'),
('Algeria', 'DZ');
```

### 2. Add Suppliers

Go to: **Suppliers → Add Supplier**

Fill in:
- Name
- Contact Person
- Address
- Phone
- Email

### 3. Add Products

Go to: **Products → Add Product**

1. Enter product details (name, description, reference)
2. Add variants (sizes, colors, SKU, prices)
3. Save

### 4. Add Initial Stock

Go to: **Stocks**

Update stock quantities for each variant.

## 🎯 Next Steps

1. **Review** the [README.md](README.md) for complete documentation
2. **Read** the [BUSINESS_ADAPTATION_GUIDE.md](BUSINESS_ADAPTATION_GUIDE.md) for customization
3. **Check** the [SQL_ANALYSIS.md](SQL_ANALYSIS.md) for database optimization
4. **Test** all features with sample data
5. **Customize** for your business needs

## 🐛 Troubleshooting

### Can't Connect to Database

**Error**: "Database connection error"

**Solutions**:
1. Check `.env` file exists and has correct credentials
2. Verify MySQL is running: `sudo service mysql status`
3. Test connection: `mysql -u username -p database_name`
4. Check database exists: `SHOW DATABASES;`

### Page Not Found (404)

**Error**: Routes not working

**Solutions**:
1. **Apache**: Ensure mod_rewrite is enabled
   ```bash
   sudo a2enmod rewrite
   sudo service apache2 restart
   ```

2. **Check .htaccess** exists in root (create if missing):
   ```apache
   RewriteEngine On
   RewriteCond %{REQUEST_FILENAME} !-f
   RewriteCond %{REQUEST_FILENAME} !-d
   RewriteRule ^(.*)$ index.php?route=$1 [L,QSA]
   ```

### Permission Denied for Uploads

**Error**: Can't upload files

**Solutions**:
```bash
# Make uploads directory writable
chmod -R 755 uploads/
sudo chown -R www-data:www-data uploads/

# Create subdirectories if missing
mkdir -p uploads/sales_proofs
chmod -R 755 uploads/sales_proofs
```

### PHP Extensions Missing

**Error**: "Class 'PDO' not found"

**Solutions**:
```bash
# Ubuntu/Debian
sudo apt-get install php-mysql php-pdo

# CentOS/RHEL
sudo yum install php-mysql php-pdo

# Restart web server
sudo service apache2 restart  # or nginx/php-fpm
```

### Session Errors

**Error**: "Warning: session_start()"

**Solutions**:
1. Check session directory permissions
2. PHP config: Ensure `session.save_path` is writable
3. Create session directory if needed:
   ```bash
   sudo mkdir -p /var/lib/php/sessions
   sudo chmod 1733 /var/lib/php/sessions
   ```

## 📞 Getting Help

If you encounter issues:

1. **Check the error**: Look in:
   - Browser console (F12)
   - PHP error log (location varies by system)
   - Apache/Nginx error log

2. **Common log locations**:
   - Ubuntu Apache: `/var/log/apache2/error.log`
   - Ubuntu Nginx: `/var/log/nginx/error.log`
   - PHP errors: `/var/log/php/error.log` or as configured in php.ini

3. **Enable PHP error reporting** (development only):
   ```php
   // Add to top of index.php temporarily
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   ```

## 🔒 Security Checklist

Before going to production:

- [ ] Change default passwords
- [ ] Move credentials to .env file
- [ ] Set strong database password
- [ ] Disable error display in production
- [ ] Enable HTTPS
- [ ] Set secure session cookies
- [ ] Review file upload restrictions
- [ ] Set up regular backups
- [ ] Update all dependencies
- [ ] Restrict database user privileges

## 🎉 You're Ready!

Your system is now set up and ready to use. Start by:

1. Adding your suppliers
2. Importing your product catalog
3. Recording initial stock
4. Creating your first order

Enjoy managing your business efficiently! 🚀
