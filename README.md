# 👟 Chaussure Management System

A comprehensive inventory, order, and sales management system for shoe businesses.

> 📚 **New Here?** Check the [Documentation Index](DOCUMENTATION_INDEX.md) for a guided tour of all available documentation.

## 🚀 Quick Links

- **[Quick Start Guide](QUICK_START.md)** - Install and run the system
- **[Business Adaptation Guide](BUSINESS_ADAPTATION_GUIDE.md)** - Customize for your business
- **[Database Schema](DATABASE_SCHEMA.md)** - Understand the data structure
- **[Security Guide](SECURITY_MIGRATION.md)** - Secure your installation
- **[Customization Checklist](CUSTOMIZATION_CHECKLIST.md)** - Track your progress

## 📋 Overview

This system manages the complete lifecycle of shoe inventory including:
- **Product Management**: Track shoes with multiple variants (sizes, colors)
- **Supplier Management**: Manage supplier relationships and orders
- **Order Tracking**: Handle purchase orders with partial shipments
- **Payment Management**: Track payments with order allocations
- **Stock Control**: Real-time inventory across multiple countries
- **Client Sales**: Record and track customer sales by country
- **Transport Management**: Manage shipping and logistics

## 🗄️ Database Structure

The system uses **18 tables** organized as follows:

### Core Tables
- `products` - Main product catalog
- `variants` - Product variations (size, color, etc.)
- `suppliers` - Supplier information
- `users` - System users and authentication

### Order & Purchase Management
- `orders` - Purchase orders from suppliers
- `order_items` - Line items in each order
- `shipments` - Partial shipments for orders
- `shipment_items` - Items in each shipment

### Payment Tracking
- `payments` - Payment records
- `payment_allocations` - Allocate payments to specific orders

### Inventory Management
- `stocks` - Warehouse stock levels
- `country_stocks` - Stock distributed to countries
- `stock_adjustments` - Manual stock adjustments
- `real_stock_view` - Calculated real-time stock view

### Sales & Distribution
- `client_sales` - Customer sales transactions
- `client_sale_items` - Items sold in each transaction
- `countries` - Country/location master data
- `transports` - Shipping carriers/methods

## 🏗️ Architecture

### MVC Structure
```
/config
  └── db.php                 # Database configuration
/controllers
  ├── AuthController.php     # Authentication
  ├── ProductController.php  # Product CRUD
  ├── SupplierController.php # Supplier management
  ├── OrderController.php    # Order processing
  ├── PaymentController.php  # Payment handling
  ├── ShipmentController.php # Shipment tracking
  ├── StockController.php    # Inventory control
  ├── ClientSaleController.php # Sales recording
  └── TransportController.php  # Logistics
/models
  └── [18 model files]       # Data access layer
/views
  ├── /auth/                 # Login/Register
  ├── /products/             # Product views
  ├── /suppliers/            # Supplier views
  ├── /orders/               # Order views
  ├── /payments/             # Payment views
  ├── /shipments/            # Shipment views
  ├── /stocks/               # Inventory views
  ├── /client_sales/         # Sales views
  └── /dashboard/            # Main dashboard
/sql
  └── quwaejeq_chaussure_manage_db.sql # Database schema & data
```

## 🚀 Installation

1. **Import Database**
   ```bash
   mysql -u username -p database_name < sql/quwaejeq_chaussure_manage_db.sql
   ```

2. **Configure Database Connection**
   Edit `config/db.php` with your database credentials:
   ```php
   $host = 'your_host';
   $db   = 'your_database';
   $user = 'your_username';
   $pass = 'your_password';
   ```

3. **Set Up Web Server**
   - Point your web server to the project root
   - Ensure PHP 7.4+ is installed
   - Enable required PHP extensions: PDO, PDO_MySQL

4. **Access Application**
   - Navigate to your web server URL
   - Default route redirects to login or dashboard

## 🔧 Configuration for Your Business

### 1. Database Credentials
**File**: `config/db.php`
- Update host, database name, username, and password
- **⚠️ IMPORTANT**: Never commit real credentials to version control

### 2. Business Name & Branding
Update the following areas:
- View headers and titles
- Dashboard labels
- Email templates (if applicable)

### 3. Country Setup
Add your business locations in the `countries` table:
```sql
INSERT INTO countries (name, code) VALUES ('Your Country', 'CC');
```

### 4. Product Categories
Customize product attributes in `products` and `variants` tables based on your inventory needs.

### 5. Supplier Information
Add your suppliers through the web interface or directly in the `suppliers` table.

## 🔐 Security Recommendations

### Critical Issues to Address:
1. **Hardcoded Credentials**: Remove credentials from `config/db.php`
   - Use environment variables instead
   - Create a `.env` file (and add to `.gitignore`)
   
2. **Session Security**: Review `session_start()` configuration
   - Add session timeout
   - Use secure session cookies

3. **Input Validation**: Ensure all user inputs are validated and sanitized

4. **File Upload Security**: 
   - Verify file types in uploads directory
   - Restrict upload permissions

## 📊 Key Features

### Stock Management
- Real-time stock tracking across warehouses and countries
- Automatic stock updates on sales and shipments
- Manual stock adjustments with audit trail

### Order Processing
- Create purchase orders with multiple items
- Track partial shipments
- Link payments to specific orders
- Monitor order status (pending, delivered, etc.)

### Payment Tracking
- Record supplier payments
- Allocate payments across multiple orders
- View payment history and balances

### Sales Recording
- Record customer sales by country
- Track sales with proof documents
- Automatic inventory deduction

## 🛣️ Routes

### Authentication
- `/login` - User login
- `/register` - User registration
- `/logout` - Logout

### Products
- `/products` - List all products
- `/products/create` - Add new product
- `/products/edit/:id` - Edit product

### Suppliers
- `/suppliers` - List suppliers
- `/suppliers/create` - Add supplier
- `/suppliers/dashboard` - Supplier overview

### Orders
- `/orders` - List orders
- `/orders/create` - Create order
- `/orders/show/:id` - Order details

### Payments
- `/payments` - List payments
- `/payments/create` - Record payment

### Stocks
- `/stocks` - View inventory
- `/stocks/country/:id` - Country-specific stock

### Client Sales
- `/client_sales` - Sales history
- `/client_sales/create` - Record sale

## 📈 Customization Guide

### Adding New Product Attributes
1. Add column to `variants` table
2. Update `Variant.php` model
3. Modify product forms in `/views/products/`
4. Update `ProductController.php` logic

### Adding New Reports
1. Create view in `/views/dashboard/`
2. Add route in `routes.php`
3. Create controller method for data

### Multi-Currency Support
1. Add `currency` column to relevant tables
2. Update payment and order models
3. Add currency conversion logic

## 🔍 Common Customizations

### Change Business Type
This system is built for shoes but can be adapted for any variant-based products:
1. Rename product-specific labels
2. Update variant attributes (size/color → your attributes)
3. Modify dashboard to show relevant KPIs

### Add More Countries/Locations
```sql
INSERT INTO countries (name, code) VALUES ('New Location', 'NL');
```

### Customize Stock Alerts
Edit stock threshold logic in `StockController.php`

## 📝 Database Schema Details

### Key Relationships
- Products → Variants (1:Many)
- Orders → Order Items → Variants (Many:Many through OrderItem)
- Orders → Shipments → Shipment Items (1:Many:Many)
- Suppliers → Orders (1:Many)
- Suppliers → Payments (1:Many)
- Payments → Payment Allocations → Orders (Many:Many through PaymentAllocation)

### View: real_stock_view
Automatically calculates available stock considering:
- Warehouse stock
- Allocated country stock
- Pending sales

## 🐛 Troubleshooting

### Database Connection Issues
- Verify credentials in `config/db.php`
- Check MySQL server is running
- Ensure database exists

### Permission Errors
- Check file permissions on uploads directory
- Verify web server has write access

### Session Issues
- Ensure session directory is writable
- Check PHP session configuration

## 📞 Support

For issues or customization help:
1. Review this documentation
2. Check the `/conception.txt` file for route details
3. Examine the SQL schema in `/sql/` directory

## 🔄 Version History

- **Current**: Full-featured inventory and sales management
- Includes: Products, Orders, Payments, Shipments, Sales tracking

## 📄 License

[Add your license information here]
