# 🎯 Business Adaptation Guide

This guide helps you customize the Chaussure Management System for your specific business needs.

## 📝 Before You Begin

### Understanding Your Business
Answer these questions to guide your adaptation:

1. **What products do you sell?**
   - If not shoes, what are the product variants? (sizes, colors, models, etc.)
   
2. **What are your locations?**
   - List all countries/warehouses where you store inventory
   
3. **Who are your suppliers?**
   - Prepare a list of supplier names and contact information
   
4. **What's your workflow?**
   - Map your current order → payment → shipment → sales process
   
5. **What reports do you need?**
   - Sales by period, inventory levels, supplier balances, etc.

## 🔧 Step-by-Step Adaptation

### Step 1: Configure Database (CRITICAL)

**⚠️ Security First: Remove Hardcoded Credentials**

1. Create an environment configuration file:

```bash
# Create .env file
touch .env
```

2. Add to `.gitignore`:
```bash
echo ".env" >> .gitignore
```

3. Create `config/.env.example`:
```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=your_database_name
DB_USER=your_username
DB_PASSWORD=your_password
```

4. Update `config/db.php` to use environment variables:
```php
<?php

class Database
{
    public static function getInstance()
    {
        static $pdo = null;

        if ($pdo === null) {
            // Load environment variables
            if (file_exists(__DIR__ . '/../.env')) {
                $env = parse_ini_file(__DIR__ . '/../.env');
            } else {
                die("❌ .env file not found");
            }
            
            $host = $env['DB_HOST'] ?? '127.0.0.1';
            $db   = $env['DB_NAME'] ?? 'database';
            $user = $env['DB_USER'] ?? 'root';
            $pass = $env['DB_PASSWORD'] ?? '';
            $port = $env['DB_PORT'] ?? 3306;
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

5. Copy `.env.example` to `.env` and fill in your credentials:
```bash
cp config/.env.example .env
# Edit .env with your actual credentials
```

### Step 2: Customize for Your Product Type

#### If You're Selling Shoes (No Changes Needed)
Skip to Step 3.

#### If You're Selling Other Products

**Example: Adapting for Clothing**

1. **Update Product Terminology**

   Search and replace in views:
   ```bash
   # Find files mentioning "chaussure" (shoe)
   grep -r "chaussure" views/
   
   # Replace with your product type
   # Example: "Vêtement" (clothing)
   ```

2. **Modify Variant Attributes**

   Current schema (shoes):
   - Size (36, 37, 38, etc.)
   - Color
   - SKU

   For clothing, you might want:
   - Size (S, M, L, XL)
   - Color
   - Material
   
   Update `variants` table:
   ```sql
   ALTER TABLE variants ADD COLUMN material VARCHAR(100) AFTER color;
   ```

3. **Update Forms and Views**
   - Edit `/views/products/create.php`
   - Edit `/views/products/edit.php`
   - Update `ProductController.php` to handle new fields

**Example: Adapting for Electronics**

1. Variant attributes might include:
   - Model number
   - Specification (RAM, Storage, etc.)
   - Color
   
2. Update database and forms accordingly

### Step 3: Configure Your Business Locations

1. **Clear Sample Data**
   ```sql
   TRUNCATE TABLE countries;
   ```

2. **Add Your Locations**
   ```sql
   INSERT INTO countries (name, code) VALUES 
   ('Morocco', 'MA'),
   ('France', 'FR'),
   ('Spain', 'ES');
   ```

3. **Or use the web interface**
   - Access `/dashboard`
   - Navigate to locations/countries management
   - Add your business locations

### Step 4: Set Up Suppliers

1. **Via Web Interface** (Recommended)
   - Go to `/suppliers`
   - Click "Add Supplier"
   - Fill in details: name, contact, address, phone, email

2. **Or via SQL**
   ```sql
   INSERT INTO suppliers (name, contact, address, phone, email) VALUES 
   ('ABC Wholesale', 'John Smith', '123 Main St', '+1234567890', 'john@abc.com'),
   ('XYZ Distributors', 'Jane Doe', '456 Oak Ave', '+0987654321', 'jane@xyz.com');
   ```

### Step 5: Customize Business Rules

#### Stock Thresholds

Edit `StockController.php`:
```php
// Find low stock threshold logic
// Default might be:
$lowStockThreshold = 10;

// Change to your needs:
$lowStockThreshold = 50; // Alert when stock below 50 units
```

#### Order Status Workflow

Edit `OrderController.php`:
```php
// Default statuses: pending, delivered, cancelled
// Add custom statuses if needed
$validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
```

#### Payment Terms

Modify payment allocation logic in `PaymentController.php` to match your business terms.

### Step 6: Branding & Labels

1. **Update Page Titles**
   
   Edit each view file header:
   ```php
   // Before
   <title>Gestion Chaussures</title>
   
   // After  
   <title>Your Business Name - Inventory Management</title>
   ```

2. **Update Dashboard Labels**
   
   Edit `/views/dashboard/index.php`:
   - Change "Chaussures" references
   - Update KPI labels
   - Customize reports displayed

3. **Logo & Branding**
   - Add your logo to `/public/images/` (create if needed)
   - Update header in main layout
   - Customize CSS in `/public/css/` (create if needed)

### Step 7: Configure Upload Directories

Ensure proper permissions:
```bash
chmod 755 uploads/
chmod 755 uploads/sales_proofs/
```

Add subdirectories as needed:
```bash
mkdir -p uploads/product_images
mkdir -p uploads/invoices
chmod 755 uploads/product_images
chmod 755 uploads/invoices
```

### Step 8: User Management

1. **Create Admin User**
   ```sql
   INSERT INTO users (username, password, email, role) VALUES 
   ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@yourbusiness.com', 'admin');
   -- Default password: 'password' (CHANGE THIS!)
   ```

2. **Change Default Password**
   - Login with default credentials
   - Navigate to profile settings
   - Change to a secure password

3. **Add Team Members**
   - Use `/register` route (if enabled)
   - Or add directly via SQL with hashed passwords

### Step 9: Customize Reports

#### Add Sales Report by Period

1. Create new view: `/views/dashboard/sales_report.php`
2. Add controller method in `ClientSaleController.php`:
   ```php
   function salesReport($startDate, $endDate) {
       // Query sales between dates
       // Display results
   }
   ```
3. Add route in `routes.php`

#### Add Supplier Balance Report

1. Create view: `/views/suppliers/balance_report.php`
2. Add logic to calculate: Orders - Payments = Balance
3. Display per supplier

### Step 10: Multi-Currency (Optional)

If you deal with multiple currencies:

1. **Add currency table**
   ```sql
   CREATE TABLE currencies (
       id INT AUTO_INCREMENT PRIMARY KEY,
       code VARCHAR(3) NOT NULL,
       name VARCHAR(50),
       exchange_rate DECIMAL(10,4) DEFAULT 1.0000
   );
   ```

2. **Update orders, payments, sales tables**
   ```sql
   ALTER TABLE orders ADD COLUMN currency_id INT;
   ALTER TABLE payments ADD COLUMN currency_id INT;
   ALTER TABLE client_sales ADD COLUMN currency_id INT;
   ```

3. **Update controllers** to handle currency conversion

### Step 11: Language Localization

The system is currently in French. To add English or other languages:

1. **Create language files**
   ```bash
   mkdir -p config/languages
   touch config/languages/en.php
   touch config/languages/fr.php
   ```

2. **Define translations**
   ```php
   // config/languages/en.php
   return [
       'dashboard' => 'Dashboard',
       'products' => 'Products',
       'suppliers' => 'Suppliers',
       // ... more translations
   ];
   ```

3. **Create translation helper**
   ```php
   // utils.php
   function t($key) {
       static $translations = null;
       if ($translations === null) {
           $lang = $_SESSION['language'] ?? 'fr';
           $translations = include "config/languages/$lang.php";
       }
       return $translations[$key] ?? $key;
   }
   ```

4. **Update views**
   ```php
   // Before
   <h1>Tableau de bord</h1>
   
   // After
   <h1><?= t('dashboard') ?></h1>
   ```

## 🎨 Advanced Customizations

### Add New Module

Example: Adding a "Returns" module for product returns

1. **Create database table**
   ```sql
   CREATE TABLE returns (
       id INT AUTO_INCREMENT PRIMARY KEY,
       sale_id INT,
       return_date DATE,
       reason TEXT,
       status VARCHAR(50),
       FOREIGN KEY (sale_id) REFERENCES client_sales(id)
   );
   ```

2. **Create model** `/models/Return.php`

3. **Create controller** `/controllers/ReturnController.php`

4. **Create views** `/views/returns/`

5. **Add routes** in `routes.php`

### Integrate External APIs

Example: Integrating with shipping API

1. Create `/utils/ShippingAPI.php`
2. Add API credentials to `.env`
3. Call from `ShipmentController.php`

### Email Notifications

1. Install PHPMailer or use PHP's mail()
2. Create `/utils/EmailService.php`
3. Send emails on:
   - Order confirmation
   - Payment received
   - Low stock alerts

## 📊 Analytics & Reporting

### Google Analytics Integration

Add to main layout header:
```php
<!-- Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=YOUR-GA-ID"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'YOUR-GA-ID');
</script>
```

### Custom Dashboard Widgets

Edit `/views/dashboard/index.php` to add:
- Monthly sales charts
- Top selling products
- Supplier performance metrics
- Inventory turnover rate

## 🔒 Security Enhancements

### 1. Enable HTTPS
Configure your web server for SSL/TLS

### 2. Add CSRF Protection
```php
// Generate token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Validate in forms
if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('Invalid CSRF token');
}
```

### 3. Input Validation
Always validate and sanitize user input:
```php
$email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
$quantity = filter_var($_POST['quantity'], FILTER_VALIDATE_INT);
```

### 4. Prepared Statements
Already implemented via PDO, ensure all queries use prepared statements.

## ✅ Testing Your Customizations

1. **Create test data** in a development database
2. **Test each module**:
   - Add products
   - Create orders
   - Record payments
   - Process shipments
   - Record sales
3. **Verify calculations**:
   - Stock levels update correctly
   - Payment allocations sum correctly
   - Reports show accurate data

## 🚀 Deployment Checklist

- [ ] Database credentials moved to .env
- [ ] .env added to .gitignore
- [ ] Default passwords changed
- [ ] Business locations configured
- [ ] Suppliers added
- [ ] Product catalog imported
- [ ] User accounts created
- [ ] Upload directories have correct permissions
- [ ] HTTPS enabled
- [ ] Backup strategy configured
- [ ] Error logging enabled
- [ ] Session security configured

## 📚 Additional Resources

- **PHP PDO Documentation**: https://www.php.net/manual/en/book.pdo.php
- **MySQL Documentation**: https://dev.mysql.com/doc/
- **Security Best Practices**: https://owasp.org/www-project-top-ten/

## 🆘 Need Help?

Common issues and solutions:

1. **Can't connect to database**
   - Check .env file exists and has correct credentials
   - Verify MySQL server is running
   - Ensure database exists

2. **Pages not loading**
   - Check .htaccess for rewrite rules
   - Verify index.php is being loaded
   - Check PHP error logs

3. **Calculations are wrong**
   - Review stock adjustment logic
   - Check payment allocation totals
   - Verify foreign key relationships

---

**Remember**: Test all changes in a development environment before deploying to production!
