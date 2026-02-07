# Testing Guide: Transport ID Column Compatibility Fix

## What Was Fixed

The application now gracefully handles databases that don't have the `transport_id` column yet. All SQL queries dynamically adapt based on whether the migration has been run.

## How to Test

### 1. Test WITHOUT Migration (Current State)

If you haven't run `sql/migration_country_to_transport.sql` yet:

1. **Navigate to Orders Page**
   ```
   http://localhost:8888/chaussure-manage/?route=orders
   ```
   - Should load without errors
   - Should display all orders with country information
   - No more "Unknown column 'o.transport_id'" error

2. **Navigate to Shipments Page**
   ```
   http://localhost:8888/chaussure-manage/?route=shipments
   ```
   - Should load without errors
   - Should display orders with remaining quantities
   - No more SQL errors

3. **Check Other Order Pages**
   - Order details: `?route=orders/show/[id]`
   - Create shipment: `?route=shipments/create`
   - Payments page: `?route=payments`

### 2. Test AFTER Migration (Future State)

After running the migration script:

1. **Run the Migration**
   ```sql
   SOURCE /path/to/sql/migration_country_to_transport.sql;
   ```

2. **Verify Same Pages Work**
   - All pages should continue working
   - Now with additional transport company information displayed
   - Auto-detection switches to use transport-based queries

### 3. Expected Behavior

**Before Migration:**
- Queries use `countries` table only
- Display shows country flags and names
- LEFT JOIN on transports is skipped

**After Migration:**
- Queries use both `transports` and `countries` tables
- Display shows transport company info + country info
- LEFT JOIN on transports is active
- COALESCE prioritizes transport name over country name

## Technical Details

### Column Detection Method

The system checks if `transport_id` exists using:
```php
SELECT COUNT(*) 
FROM information_schema.columns 
WHERE table_schema = DATABASE() 
AND table_name = 'orders'
AND column_name = 'transport_id'
```

Result is cached in a static variable to avoid repeated database calls.

### Affected Models

1. **Order.php** - 8 methods updated
   - `allWithCountry()`
   - `allWithTransport()`
   - `allWithSupplier()`
   - `findWithSupplier()`
   - `getUnpaidBySupplier()`
   - `findBySupplier()`
   - `filterWithSupplier()`
   - `withRemainingQuantities()`

2. **ClientSale.php** - 3 methods updated
   - `getAllWithCountry()`
   - `findWithCountry()`
   - `createWithTransport()`

### SQL Query Patterns

**With Transport Column:**
```sql
SELECT o.*, 
       COALESCE(t.name, c.name) AS destination_name,
       t.name AS transport_name,
       c.name AS destination_country
FROM orders o
LEFT JOIN transports t ON o.transport_id = t.id
LEFT JOIN countries c ON o.country_id = c.id
```

**Without Transport Column:**
```sql
SELECT o.*, 
       c.name AS destination_country,
       c.flag
FROM orders o
JOIN countries c ON o.country_id = c.id
```

## Troubleshooting

### If pages still show errors:

1. **Clear PHP opcache** (if enabled)
   ```php
   opcache_reset();
   ```

2. **Verify file permissions**
   ```bash
   chmod 644 models/Order.php
   chmod 644 models/ClientSale.php
   ```

3. **Check PHP error log**
   - Look for any syntax errors
   - Verify database connection works

4. **Test database connection**
   ```php
   // In config/db.php
   $pdo->query("SELECT 1");
   ```

### If you want to force migration detection:

Clear the static cache by restarting PHP-FPM or Apache:
```bash
# Mac MAMP
sudo /Applications/MAMP/bin/stop.sh
sudo /Applications/MAMP/bin/start.sh

# Or restart Apache
sudo apachectl restart
```

## Success Indicators

✅ **Orders page loads** without SQL errors
✅ **Shipments page loads** and shows orders
✅ **Order details** display correctly
✅ **No "Unknown column" errors** in any page
✅ **Country information** displays for all orders
✅ **After migration**: Transport info also displays

## Migration Path

When you're ready to use the transport-based system:

1. Backup your database
2. Run `sql/migration_country_to_transport.sql`
3. Refresh any page - detection is automatic
4. Start using transport-based features

No code changes needed - the system adapts automatically!
