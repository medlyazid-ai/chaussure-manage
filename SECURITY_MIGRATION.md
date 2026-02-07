# 🔄 Security Migration Guide

## ⚠️ CRITICAL: Database Credentials Security Issue

Your current `config/db.php` file contains **hardcoded database credentials**. This is a significant security risk, especially if your code is in version control.

## 🎯 Required Actions

### Immediate Priority: Secure Your Credentials

Follow these steps to migrate to secure environment-based configuration:

### Step 1: Create .env File

```bash
# Copy the example environment file
cp .env.example .env
```

### Step 2: Configure Your Environment

Edit `.env` with your actual database credentials:

```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=quwaejeq_chaussure_manage_db
DB_USER=quwaejeq_admin
DB_PASSWORD=DJq[*:q5Ia
```

**Important**: Replace these with your actual credentials!

### Step 3: Update Database Configuration

Replace `config/db.php` with the secure version:

```bash
# Backup your current config
cp config/db.php config/db.php.backup

# Use the new secure version
cp config/db.new.php config/db.php
```

Or manually update `config/db.php` to use the environment-based configuration (see `config/db.new.php` for reference).

### Step 4: Verify .gitignore

Ensure `.env` is in your `.gitignore`:

```bash
# Check if .env is ignored
grep -q "^\.env$" .gitignore && echo "✅ .env is ignored" || echo "❌ Add .env to .gitignore"

# If needed, add it:
echo ".env" >> .gitignore
```

### Step 5: Remove Credentials from Git History (if committed)

If you've already committed the file with credentials:

```bash
# WARNING: This rewrites Git history. Coordinate with your team!

# Remove the file from all commits
git filter-branch --force --index-filter \
  "git rm --cached --ignore-unmatch config/db.php" \
  --prune-empty --tag-name-filter cat -- --all

# Force push (if using remote repository)
git push origin --force --all
```

**Alternative** (safer): Rotate your database password:

```sql
-- Connect to MySQL as root
ALTER USER 'quwaejeq_admin'@'localhost' IDENTIFIED BY 'NEW_SECURE_PASSWORD';
FLUSH PRIVILEGES;
```

### Step 6: Test the Configuration

1. **Start your application**:
   ```bash
   php -S localhost:8000
   ```

2. **Access the application**:
   Open http://localhost:8000 in your browser

3. **Verify connection**:
   - You should see the login page
   - No database errors should appear
   - Try logging in

4. **Check error logs**:
   - Look for any connection issues
   - Verify .env is being loaded correctly

## 🔒 Additional Security Measures

### 1. Secure File Permissions

```bash
# .env should not be readable by others
chmod 600 .env

# Ensure only you can read it
ls -la .env
# Should show: -rw------- (600)
```

### 2. Production Environment Setup

For production servers, use these additional steps:

#### Option A: Server Environment Variables (Recommended)

Instead of a `.env` file, set environment variables at the server level:

**Apache (.htaccess or VirtualHost)**:
```apache
SetEnv DB_HOST "127.0.0.1"
SetEnv DB_NAME "your_database"
SetEnv DB_USER "your_user"
SetEnv DB_PASSWORD "your_password"
```

**Nginx (with PHP-FPM)**:
```nginx
location ~ \.php$ {
    fastcgi_param DB_HOST "127.0.0.1";
    fastcgi_param DB_NAME "your_database";
    fastcgi_param DB_USER "your_user";
    fastcgi_param DB_PASSWORD "your_password";
}
```

**System Environment** (Linux):
```bash
# Add to /etc/environment or user's .bashrc
export DB_HOST="127.0.0.1"
export DB_NAME="your_database"
export DB_USER="your_user"
export DB_PASSWORD="your_password"
```

Then update `config/db.php` to use `getenv()`:
```php
$host = getenv('DB_HOST') ?: $_ENV['DB_HOST'] ?? '127.0.0.1';
$db   = getenv('DB_NAME') ?: $_ENV['DB_NAME'] ?? 'database';
$user = getenv('DB_USER') ?: $_ENV['DB_USER'] ?? 'root';
$pass = getenv('DB_PASSWORD') ?: $_ENV['DB_PASSWORD'] ?? '';
```

#### Option B: Use a Secrets Manager

For enterprise deployments:
- **AWS Secrets Manager**
- **Azure Key Vault**
- **Google Cloud Secret Manager**
- **HashiCorp Vault**

### 3. Database User Permissions

Create a dedicated database user with minimal permissions:

```sql
-- Create dedicated user for the application
CREATE USER 'chaussure_app'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD_HERE';

-- Grant only necessary permissions
GRANT SELECT, INSERT, UPDATE, DELETE ON quwaejeq_chaussure_manage_db.* TO 'chaussure_app'@'localhost';

-- Don't grant DROP, CREATE, ALTER unless absolutely necessary
FLUSH PRIVILEGES;
```

Update `.env`:
```env
DB_USER=chaussure_app
DB_PASSWORD=STRONG_PASSWORD_HERE
```

### 4. Regular Password Rotation

Set up a schedule to rotate database passwords:

1. **Monthly or Quarterly**: Change database password
2. **Update .env** with new password
3. **Restart application** to apply changes

```bash
# Script to rotate password (example)
NEW_PASS=$(openssl rand -base64 32)
mysql -u root -p -e "ALTER USER 'chaussure_app'@'localhost' IDENTIFIED BY '$NEW_PASS';"
# Update .env file
sed -i "s/^DB_PASSWORD=.*/DB_PASSWORD=$NEW_PASS/" .env
# Restart services
sudo systemctl restart apache2  # or your web server
```

### 5. Enable SSL/TLS for Database Connections

For remote database connections:

```php
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::MYSQL_ATTR_SSL_CA       => '/path/to/ca-cert.pem',
    PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => true,
];
```

## 📋 Security Checklist

Use this checklist to ensure your application is secure:

### Database Security
- [ ] Credentials moved to .env file
- [ ] .env added to .gitignore
- [ ] .env file permissions set to 600
- [ ] Old credentials removed from Git history (or password rotated)
- [ ] Dedicated database user created with minimal permissions
- [ ] Database password is strong (20+ characters, mixed case, symbols)
- [ ] SSL/TLS enabled for database connections (if remote)

### Application Security
- [ ] Error display disabled in production (`display_errors = Off`)
- [ ] Error logging enabled and monitored
- [ ] All SQL queries use prepared statements (already done)
- [ ] Input validation on all user inputs
- [ ] Output escaping in all views
- [ ] CSRF protection implemented
- [ ] Session security configured (secure, httponly cookies)
- [ ] File upload validation (type, size, location)

### Infrastructure Security
- [ ] HTTPS enabled (SSL/TLS certificate)
- [ ] Web server configured securely
- [ ] PHP configured securely (disable dangerous functions)
- [ ] Directory listing disabled
- [ ] Uploads directory outside web root (or protected)
- [ ] Regular security updates applied
- [ ] Firewall configured (only necessary ports open)
- [ ] Regular backups configured and tested

### Access Control
- [ ] Strong password policy enforced
- [ ] Default passwords changed
- [ ] User roles and permissions reviewed
- [ ] Admin accounts limited and monitored
- [ ] Failed login attempts rate-limited
- [ ] Session timeout configured

## 🔍 Verification

After completing the migration:

### 1. Test Database Connection
```bash
php -r "require 'config/db.php'; \$pdo = Database::getInstance(); echo 'Connection successful!';"
```

### 2. Check for Exposed Credentials
```bash
# Search for any remaining hardcoded passwords
grep -r "password.*=" --include="*.php" . | grep -v ".env"

# Check Git history
git log --all --full-history --source -- config/db.php
```

### 3. Verify .gitignore
```bash
git check-ignore .env
# Should output: .env
```

### 4. Test Application
- Login works
- Database queries work
- No error messages about configuration

## 🆘 Troubleshooting

### ".env file not found" error

**Solution**: Create .env file from template
```bash
cp .env.example .env
# Edit .env with your credentials
```

### "Permission denied" when reading .env

**Solution**: Fix file permissions
```bash
chmod 600 .env
chown www-data:www-data .env  # or your web server user
```

### Application still uses old config

**Solution**: Clear any PHP caches
```bash
# OpCache
sudo service php7.4-fpm restart

# APC/APCu
php -r "apcu_clear_cache();"
```

### Environment variables not loading

**Solution**: Check .env file format
- No spaces around `=`
- No quotes unless needed
- Unix line endings (LF, not CRLF)

```bash
# Convert Windows to Unix line endings
dos2unix .env
```

## 📚 Additional Resources

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [PHP Security Best Practices](https://www.php.net/manual/en/security.php)
- [MySQL Security Guide](https://dev.mysql.com/doc/refman/8.0/en/security-guidelines.html)

## 🔄 Migration Complete!

Once you've completed all steps:

1. ✅ Credentials are secure
2. ✅ Application works correctly
3. ✅ No sensitive data in version control
4. ✅ Security checklist reviewed

You're now following security best practices! 🎉

---

**Remember**: Security is an ongoing process. Regularly review and update your security measures.
