# 📊 SQL Database Analysis & Optimization

## Database Overview

**Database Name**: `quwaejeq_chaussure_manage_db`
**Total Tables**: 18
**Database Size**: ~1046 lines of SQL
**Engine**: InnoDB (MyISAM for views)

## 📋 Table Structure Analysis

### 1. Products & Variants
```
products (id, name, description, reference, created_at)
  └── variants (id, product_id, size, color, sku, unit_price_dzd, unit_price_eur, created_at)
```

**Purpose**: Core product catalog with variant support
**Relationship**: 1 Product → Many Variants
**Indexes**: 
- PRIMARY KEY on id
- FOREIGN KEY variants.product_id → products.id (CASCADE DELETE)

**Optimization Opportunities**:
- ✅ Proper indexing on foreign keys
- ✅ Cascade delete ensures orphan prevention
- 💡 Consider adding index on `products.reference` if used for lookups
- 💡 Add index on `variants.sku` for faster SKU searches

### 2. Suppliers & Orders
```
suppliers (id, name, contact, address, phone, email, created_at)
  └── orders (id, supplier_id, order_date, status, total_amount_dzd, total_amount_eur, notes)
       └── order_items (id, order_id, variant_id, quantity, unit_price_dzd, unit_price_eur)
```

**Purpose**: Supplier relationship and purchase order management
**Relationships**:
- 1 Supplier → Many Orders
- 1 Order → Many Order Items
- Each Order Item links to a Variant

**Optimization Opportunities**:
- ✅ Good normalization
- 💡 Add composite index: `(supplier_id, order_date)` for supplier order history
- 💡 Add index on `orders.status` for filtering by status
- 💡 Consider adding `order_number` field with unique index for better tracking

### 3. Shipments
```
orders
  └── shipments (id, order_id, shipment_date, transport_id, status, tracking_number, arrival_date)
       └── shipment_items (id, shipment_id, order_item_id, quantity_shipped)
```

**Purpose**: Track partial shipments for orders
**Relationships**:
- 1 Order → Many Shipments (partial delivery support)
- 1 Shipment → Many Shipment Items

**Optimization Opportunities**:
- ✅ Good structure for partial shipments
- 💡 Add index on `tracking_number` for quick lookups
- 💡 Add index on `shipment_date` for date-range queries
- 💡 Add composite index `(order_id, status)` for order shipment status

### 4. Payments
```
suppliers
  └── payments (id, supplier_id, payment_date, amount_dzd, amount_eur, payment_method, reference)
       └── payment_allocations (id, payment_id, order_id, allocated_amount_dzd, allocated_amount_eur)
```

**Purpose**: Payment tracking with order allocation
**Relationships**:
- 1 Supplier → Many Payments
- 1 Payment → Many Payment Allocations (split across orders)
- Each Allocation links to an Order

**Optimization Opportunities**:
- ✅ Flexible payment allocation system
- 💡 Add index on `payment_date` for date filtering
- 💡 Add check constraint: SUM(allocations) <= payment.amount
- 💡 Consider trigger to auto-update order paid amounts

### 5. Stock Management
```
variants
  └── stocks (id, variant_id, quantity, location)
  └── country_stocks (id, variant_id, country_id, quantity)
  └── stock_adjustments (id, variant_id, country_id, adjustment_date, quantity_change, reason, user_id)
```

**Purpose**: Multi-location inventory tracking
**Relationships**:
- Each Variant has stock in warehouse and per country
- Stock adjustments create audit trail

**Optimization Opportunities**:
- ✅ Good separation of warehouse vs. distributed stock
- 💡 Add index on `stocks.variant_id` for faster lookups
- 💡 Add composite index `(variant_id, country_id)` on country_stocks
- 💡 Consider adding triggers to log all stock changes
- ⚠️ **Critical**: Implement stock consistency checks

### 6. Client Sales
```
countries
  └── client_sales (id, sale_date, country_id, customer_name, notes, proof_file)
       └── client_sale_items (id, sale_id, variant_id, quantity_sold)
```

**Purpose**: Record customer sales by country
**Relationships**:
- Sales tied to specific countries
- Each sale has multiple line items

**Optimization Opportunities**:
- ✅ Good structure for sales tracking
- 💡 Add index on `sale_date` for date-range queries
- 💡 Add composite index `(country_id, sale_date)` for country reports
- 💡 Add trigger to auto-update country_stocks on sale

### 7. Supporting Tables

**countries**
```sql
CREATE TABLE countries (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  code VARCHAR(10) NOT NULL
);
```
- 💡 Add UNIQUE constraint on `code`
- 💡 Consider adding `is_active` flag

**transports**
```sql
CREATE TABLE transports (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  contact VARCHAR(100),
  phone VARCHAR(20)
);
```
- ✅ Simple, effective structure
- 💡 Add `is_active` flag for archived transporters

**users**
```sql
CREATE TABLE users (
  id INT PRIMARY KEY AUTO_INCREMENT,
  username VARCHAR(50) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  email VARCHAR(100),
  role VARCHAR(50),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```
- ✅ UNIQUE constraint on username
- ⚠️ Ensure passwords are properly hashed (bcrypt/argon2)
- 💡 Add UNIQUE constraint on email
- 💡 Add index on role for permission checks

## 🔍 View Analysis

### real_stock_view
```sql
CREATE ALGORITHM=UNDEFINED VIEW real_stock_view AS
  -- Combines stocks and country_stocks to show available inventory
```

**Purpose**: Calculate real-time available stock
**Optimization**:
- ⚠️ Views can be slow on large datasets
- 💡 Consider materializing this view (create table, update via triggers)
- 💡 Add covering indexes on base tables

## 🎯 Performance Recommendations

### High Priority

1. **Add Missing Indexes**
   ```sql
   -- Product lookups
   CREATE INDEX idx_product_reference ON products(reference);
   CREATE INDEX idx_variant_sku ON variants(sku);
   
   -- Order queries
   CREATE INDEX idx_order_status ON orders(status);
   CREATE INDEX idx_order_supplier_date ON orders(supplier_id, order_date);
   
   -- Shipment tracking
   CREATE INDEX idx_shipment_tracking ON shipments(tracking_number);
   CREATE INDEX idx_shipment_date ON shipments(shipment_date);
   
   -- Payment queries
   CREATE INDEX idx_payment_date ON payments(payment_date);
   
   -- Sales queries
   CREATE INDEX idx_sale_date ON client_sales(sale_date);
   CREATE INDEX idx_sale_country_date ON client_sales(country_id, sale_date);
   
   -- Stock lookups
   CREATE INDEX idx_country_stock ON country_stocks(variant_id, country_id);
   ```

2. **Add Data Integrity Constraints**
   ```sql
   -- Ensure non-negative quantities
   ALTER TABLE stocks ADD CONSTRAINT chk_stock_quantity CHECK (quantity >= 0);
   ALTER TABLE country_stocks ADD CONSTRAINT chk_country_stock CHECK (quantity >= 0);
   ALTER TABLE order_items ADD CONSTRAINT chk_order_quantity CHECK (quantity > 0);
   
   -- Ensure valid statuses
   ALTER TABLE orders ADD CONSTRAINT chk_order_status 
     CHECK (status IN ('pending', 'delivered', 'cancelled'));
   ALTER TABLE shipments ADD CONSTRAINT chk_shipment_status 
     CHECK (status IN ('pending', 'in_transit', 'delivered', 'cancelled'));
   ```

3. **Add Audit Triggers**
   ```sql
   -- Log all stock changes
   CREATE TRIGGER trg_stock_audit AFTER UPDATE ON stocks
   FOR EACH ROW
   BEGIN
     IF NEW.quantity != OLD.quantity THEN
       INSERT INTO stock_adjustments (variant_id, quantity_change, reason)
       VALUES (NEW.variant_id, NEW.quantity - OLD.quantity, 'System Update');
     END IF;
   END;
   ```

### Medium Priority

4. **Optimize Real Stock View**
   ```sql
   -- Create materialized view (table updated by triggers)
   CREATE TABLE real_stock_materialized AS
   SELECT variant_id, 
          SUM(warehouse_stock) - SUM(allocated_stock) as available
   FROM stocks
   GROUP BY variant_id;
   
   -- Add trigger to refresh on stock changes
   ```

5. **Add Computed Columns**
   ```sql
   -- Add total_paid to orders (updated by triggers)
   ALTER TABLE orders ADD COLUMN total_paid_dzd DECIMAL(10,2) DEFAULT 0;
   ALTER TABLE orders ADD COLUMN balance_dzd DECIMAL(10,2) AS 
     (total_amount_dzd - total_paid_dzd) STORED;
   ```

6. **Partition Large Tables**
   ```sql
   -- If client_sales grows large, partition by year
   ALTER TABLE client_sales 
   PARTITION BY RANGE (YEAR(sale_date)) (
     PARTITION p2024 VALUES LESS THAN (2025),
     PARTITION p2025 VALUES LESS THAN (2026),
     PARTITION p2026 VALUES LESS THAN (2027)
   );
   ```

### Low Priority

7. **Archive Old Data**
   ```sql
   -- Create archive tables for old sales
   CREATE TABLE client_sales_archive LIKE client_sales;
   
   -- Move data older than 2 years
   INSERT INTO client_sales_archive 
   SELECT * FROM client_sales 
   WHERE sale_date < DATE_SUB(NOW(), INTERVAL 2 YEAR);
   ```

8. **Add Full-Text Search**
   ```sql
   -- For product searches
   ALTER TABLE products ADD FULLTEXT(name, description);
   
   -- Usage:
   -- SELECT * FROM products WHERE MATCH(name, description) AGAINST('search term');
   ```

## 📈 Query Optimization Examples

### Slow Query: Find Low Stock Products
**Before**:
```sql
SELECT p.name, v.size, v.color, s.quantity
FROM products p
JOIN variants v ON v.product_id = p.id
JOIN stocks s ON s.variant_id = v.id
WHERE s.quantity < 10;
```

**After** (with indexes):
```sql
-- Add index first
CREATE INDEX idx_stock_low ON stocks(quantity);

-- Query is now much faster
SELECT p.name, v.size, v.color, s.quantity
FROM products p
JOIN variants v ON v.product_id = p.id
JOIN stocks s ON s.variant_id = v.id
WHERE s.quantity < 10;
```

### Slow Query: Supplier Balance
**Before**:
```sql
SELECT s.name,
       SUM(o.total_amount_dzd) as total_orders,
       SUM(p.amount_dzd) as total_paid,
       SUM(o.total_amount_dzd) - SUM(p.amount_dzd) as balance
FROM suppliers s
LEFT JOIN orders o ON o.supplier_id = s.id
LEFT JOIN payments p ON p.supplier_id = s.id
GROUP BY s.id;
```

**Problem**: Incorrect calculation (double counting)

**After** (corrected):
```sql
SELECT s.name,
       COALESCE(order_totals.total, 0) as total_orders,
       COALESCE(payment_totals.total, 0) as total_paid,
       COALESCE(order_totals.total, 0) - COALESCE(payment_totals.total, 0) as balance
FROM suppliers s
LEFT JOIN (
  SELECT supplier_id, SUM(total_amount_dzd) as total
  FROM orders GROUP BY supplier_id
) order_totals ON order_totals.supplier_id = s.id
LEFT JOIN (
  SELECT supplier_id, SUM(amount_dzd) as total
  FROM payments GROUP BY supplier_id
) payment_totals ON payment_totals.supplier_id = s.id;
```

## 🔒 Security Considerations

### 1. SQL Injection Prevention
✅ Application uses PDO with prepared statements
- Ensure ALL queries use parameterized queries
- Never concatenate user input into SQL

### 2. Data Validation
Add application-level validation for:
- Email formats
- Phone numbers
- Price amounts (positive values)
- Quantities (positive integers)
- Date ranges (start < end)

### 3. Sensitive Data
⚠️ **Critical Security Issues**:

1. **Hardcoded credentials in `config/db.php`**
   - Move to environment variables immediately
   - Never commit credentials to version control

2. **Password storage**
   - Ensure using `password_hash()` with bcrypt
   - Never store plain text passwords

3. **File uploads**
   - Validate file types
   - Limit file sizes
   - Store outside web root if possible

## 📊 Database Maintenance

### Regular Tasks

**Daily**:
- Monitor slow query log
- Check error log

**Weekly**:
- Analyze table statistics: `ANALYZE TABLE table_name;`
- Check table integrity: `CHECK TABLE table_name;`

**Monthly**:
- Optimize tables: `OPTIMIZE TABLE table_name;`
- Review and archive old data
- Update index statistics

**Quarterly**:
- Review and update indexes based on query patterns
- Audit security settings
- Backup and test restore procedures

### Monitoring Queries

```sql
-- Find slow queries
SELECT * FROM mysql.slow_log ORDER BY query_time DESC LIMIT 10;

-- Check table sizes
SELECT 
  table_name,
  ROUND(((data_length + index_length) / 1024 / 1024), 2) AS "Size (MB)"
FROM information_schema.TABLES
WHERE table_schema = 'quwaejeq_chaussure_manage_db'
ORDER BY (data_length + index_length) DESC;

-- Check index usage
SELECT * FROM sys.schema_unused_indexes;
```

## 🎯 Migration Scripts

### Adding Recommended Indexes

```sql
-- Run this script to add all recommended indexes
START TRANSACTION;

-- Product indexes
CREATE INDEX idx_product_reference ON products(reference);
CREATE INDEX idx_variant_sku ON variants(sku);

-- Order indexes
CREATE INDEX idx_order_status ON orders(status);
CREATE INDEX idx_order_supplier_date ON orders(supplier_id, order_date);

-- Shipment indexes
CREATE INDEX idx_shipment_tracking ON shipments(tracking_number);
CREATE INDEX idx_shipment_date ON shipments(shipment_date);

-- Payment indexes
CREATE INDEX idx_payment_date ON payments(payment_date);

-- Sales indexes
CREATE INDEX idx_sale_date ON client_sales(sale_date);
CREATE INDEX idx_sale_country_date ON client_sales(country_id, sale_date);

-- Stock indexes
CREATE INDEX idx_country_stock ON country_stocks(variant_id, country_id);

COMMIT;
```

### Adding Data Integrity Constraints

```sql
-- Add check constraints for data integrity
START TRANSACTION;

ALTER TABLE stocks ADD CONSTRAINT chk_stock_quantity CHECK (quantity >= 0);
ALTER TABLE country_stocks ADD CONSTRAINT chk_country_stock CHECK (quantity >= 0);
ALTER TABLE order_items ADD CONSTRAINT chk_order_quantity CHECK (quantity > 0);

COMMIT;
```

## 📚 Best Practices Summary

1. ✅ **Use Indexes Wisely**
   - Add indexes on foreign keys (already done)
   - Add indexes on columns used in WHERE, ORDER BY, GROUP BY
   - Don't over-index (slows down writes)

2. ✅ **Maintain Data Integrity**
   - Use foreign keys (already done)
   - Add check constraints for valid values
   - Use triggers for complex validations

3. ✅ **Optimize Queries**
   - Use EXPLAIN to analyze query performance
   - Avoid SELECT * when not needed
   - Use appropriate JOINs

4. ✅ **Regular Maintenance**
   - Monitor and optimize slow queries
   - Archive old data
   - Keep statistics updated

5. ✅ **Security First**
   - Never store credentials in code
   - Use prepared statements
   - Validate all user input
   - Hash passwords properly

---

**Next Steps**: 
1. Apply high-priority optimizations
2. Set up monitoring
3. Establish backup procedures
4. Create data retention policy
