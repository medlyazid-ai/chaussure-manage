# 📊 Database Schema Visual Reference

## Entity Relationship Overview

```
┌─────────────────────────────────────────────────────────────────────────┐
│                         PRODUCT MANAGEMENT                               │
└─────────────────────────────────────────────────────────────────────────┘

    ┌──────────────┐
    │   products   │
    ├──────────────┤
    │ id (PK)      │
    │ name         │
    │ description  │
    │ reference    │
    │ created_at   │
    └──────┬───────┘
           │
           │ 1:N (CASCADE DELETE)
           │
           ▼
    ┌──────────────┐
    │   variants   │
    ├──────────────┤
    │ id (PK)      │
    │ product_id   │───────┐
    │ size         │       │
    │ color        │       │
    │ sku          │       │ Referenced by
    │ unit_price_* │       │ Multiple Tables
    │ created_at   │       │
    └──────────────┘       │
                          │
                          │
┌─────────────────────────────────────────────────────────────────────────┐
│                       SUPPLIER & PURCHASING                              │
└─────────────────────────────────────────────────────────────────────────┘

    ┌──────────────┐
    │  suppliers   │
    ├──────────────┤
    │ id (PK)      │
    │ name         │
    │ contact      │
    │ address      │
    │ phone        │
    │ email        │
    │ created_at   │
    └──────┬───────┘
           │
           │ 1:N
           ├──────────────┬──────────────┐
           │              │              │
           ▼              ▼              ▼
    ┌──────────┐   ┌──────────┐   ┌──────────────┐
    │  orders  │   │ payments │   │  transports  │
    ├──────────┤   ├──────────┤   ├──────────────┤
    │ id (PK)  │   │ id (PK)  │   │ id (PK)      │
    │supplier_id   │supplier_id   │ name         │
    │order_date│   │payment_dt│   │ contact      │
    │ status   │   │ amount_* │   │ phone        │
    │total_amt*│   │ method   │   └──────────────┘
    │ notes    │   │reference │
    └────┬─────┘   └────┬─────┘
         │              │
         │ 1:N          │ 1:N
         │              │
         ▼              ▼
    ┌────────────┐  ┌─────────────────┐
    │order_items │  │payment_allocat's│
    ├────────────┤  ├─────────────────┤
    │ id (PK)    │  │ id (PK)         │
    │ order_id   │◄─┤ order_id        │
    │ variant_id │◄─┤ payment_id      │
    │ quantity   │  │ allocated_amt_* │
    │unit_price*│  └─────────────────┘
    └────┬───────┘
         │
         │ Referenced by
         │ shipment_items
         │
┌─────────────────────────────────────────────────────────────────────────┐
│                         SHIPMENT TRACKING                                │
└─────────────────────────────────────────────────────────────────────────┘

    ┌─────────────┐
    │  shipments  │
    ├─────────────┤
    │ id (PK)     │
    │ order_id    │◄────── Links to orders
    │transport_id │◄────── Links to transports
    │shipment_date│
    │ status      │
    │tracking_no  │
    │arrival_date │
    └──────┬──────┘
           │
           │ 1:N
           │
           ▼
    ┌──────────────┐
    │shipment_items│
    ├──────────────┤
    │ id (PK)      │
    │ shipment_id  │
    │order_item_id │◄──── Links to order_items
    │quantity_ship │
    └──────────────┘

┌─────────────────────────────────────────────────────────────────────────┐
│                       INVENTORY MANAGEMENT                               │
└─────────────────────────────────────────────────────────────────────────┘

                    ┌───────────────┐
                    │   countries   │
                    ├───────────────┤
                    │ id (PK)       │
                    │ name          │
                    │ code          │
                    └───┬───────────┘
                        │
           ┌────────────┼────────────┐
           │            │            │
           ▼            ▼            ▼
    ┌──────────┐ ┌─────────────┐ ┌─────────────┐
    │  stocks  │ │country_stks │ │stock_adjust │
    ├──────────┤ ├─────────────┤ ├─────────────┤
    │ id (PK)  │ │ id (PK)     │ │ id (PK)     │
    │variant_id│ │ variant_id  │ │ variant_id  │
    │ quantity │ │ country_id  │ │ country_id  │
    │ location │ │ quantity    │ │ adjust_date │
    └──────────┘ └─────────────┘ │ qty_change  │
                                  │ reason      │
                                  │ user_id     │
                                  └─────────────┘

    ┌────────────────┐
    │real_stock_view │ ◄── Computed View
    ├────────────────┤     (warehouse - allocated)
    │ variant_id     │
    │ available_qty  │
    └────────────────┘

┌─────────────────────────────────────────────────────────────────────────┐
│                         CLIENT SALES                                     │
└─────────────────────────────────────────────────────────────────────────┘

    ┌──────────────┐
    │client_sales  │
    ├──────────────┤
    │ id (PK)      │
    │ sale_date    │
    │ country_id   │◄──── Links to countries
    │customer_name │
    │ notes        │
    │ proof_file   │
    │ created_at   │
    └──────┬───────┘
           │
           │ 1:N
           │
           ▼
    ┌─────────────────┐
    │client_sale_items│
    ├─────────────────┤
    │ id (PK)         │
    │ sale_id         │
    │ variant_id      │◄──── Links to variants
    │ quantity_sold   │
    └─────────────────┘

┌─────────────────────────────────────────────────────────────────────────┐
│                      USER MANAGEMENT                                     │
└─────────────────────────────────────────────────────────────────────────┘

    ┌──────────────┐
    │    users     │
    ├──────────────┤
    │ id (PK)      │
    │ username     │ (UNIQUE)
    │ password     │ (hashed)
    │ email        │
    │ role         │
    │ created_at   │
    └──────────────┘
```

## Key Relationships Summary

### 1. Product Hierarchy
- **products** → **variants** (1:N, CASCADE DELETE)
- Each product can have multiple variants (sizes, colors)
- Deleting a product removes all its variants

### 2. Order Processing Flow
```
Supplier → Order → Order Items (Variants) → Shipments → Shipment Items
```

### 3. Payment Allocation
```
Supplier → Payment → Payment Allocations → Orders
```
- One payment can be split across multiple orders
- Tracks partial payments and supplier balances

### 4. Inventory Tracking
```
Variants ← Stocks (Warehouse)
         ← Country Stocks (Distributed)
         ← Stock Adjustments (Audit Trail)
```

### 5. Sales Flow
```
Country → Client Sales → Client Sale Items (Variants)
```
- Sales recorded per country
- Automatically deducts from country stock

## Data Flow Examples

### Example 1: Creating an Order
```
1. Select Supplier
2. Create Order record
3. Add Order Items (variants + quantities)
4. Record Payment(s)
5. Create Payment Allocation(s)
6. Receive Shipment(s)
7. Update Stock
```

### Example 2: Recording a Sale
```
1. Select Country
2. Create Client Sale record
3. Add Sale Items (variants + quantities)
4. Upload Proof Document
5. System updates Country Stock
```

### Example 3: Stock Movement
```
1. Shipment arrives → Updates Warehouse Stock
2. Stock distributed → Country Stocks updated
3. Manual adjustment → Stock Adjustments logged
4. Sale recorded → Country Stock reduced
```

## Table Sizes & Growth Estimates

| Table | Growth Rate | Notes |
|-------|------------|-------|
| products | Low | Static catalog |
| variants | Low-Medium | Grows with new product lines |
| orders | Medium | ~100-500/month typical |
| order_items | High | N × orders |
| shipments | Medium | ~50-200/month |
| shipment_items | High | N × shipments |
| payments | Medium | ~50-200/month |
| payment_allocations | High | N × payments |
| client_sales | High | Daily transactions |
| client_sale_items | Very High | N × sales |
| stocks | Medium | One per variant |
| country_stocks | Medium | Variants × Countries |
| stock_adjustments | Medium | Manual adjustments |

## Index Strategy

### Primary Keys (Auto-indexed)
All tables have `id` as PRIMARY KEY

### Foreign Keys (Auto-indexed by InnoDB)
- variants.product_id
- order_items.order_id
- order_items.variant_id
- shipment_items.shipment_id
- shipment_items.order_item_id
- And all other FK relationships

### Recommended Additional Indexes
See SQL_ANALYSIS.md for complete list

### Composite Indexes (for common queries)
```sql
-- Order history by supplier
INDEX idx_orders_supplier_date (supplier_id, order_date)

-- Sales reports by country and date
INDEX idx_sales_country_date (country_id, sale_date)

-- Stock lookups by variant and country
INDEX idx_country_stock (variant_id, country_id)
```

## Currency Handling

The system supports dual currency (DZD and EUR):

### Tables with Dual Currency
- **variants**: unit_price_dzd, unit_price_eur
- **orders**: total_amount_dzd, total_amount_eur
- **order_items**: unit_price_dzd, unit_price_eur
- **payments**: amount_dzd, amount_eur
- **payment_allocations**: allocated_amount_dzd, allocated_amount_eur

### Adding More Currencies
See BUSINESS_ADAPTATION_GUIDE.md for multi-currency setup

## Constraints & Rules

### Foreign Key Constraints
- **CASCADE DELETE**: products → variants
- **RESTRICT**: Most other relationships (prevent orphans)

### Recommended Check Constraints
```sql
-- Ensure positive quantities
CHECK (quantity >= 0)

-- Ensure valid statuses
CHECK (status IN ('pending', 'delivered', 'cancelled'))

-- Ensure prices are positive
CHECK (unit_price_dzd >= 0 AND unit_price_eur >= 0)
```

## Performance Considerations

### Fast Queries
- Lookups by ID (primary keys)
- Foreign key joins (indexed)
- Status filtering (if indexed)

### Slow Queries (without optimization)
- Full text search in descriptions
- Complex aggregations without indexes
- Date range queries (need date indexes)
- Stock availability calculations (use view)

### Optimization Strategies
1. Add indexes on filter columns
2. Use the real_stock_view
3. Denormalize frequently calculated values
4. Partition large tables by date
5. Archive old data

## Backup Strategy

### What to Backup
- **Critical**: products, variants, suppliers, users
- **Important**: orders, payments, stocks
- **Transactional**: sales, shipments (can be archived)
- **Files**: uploads/ directory (proof documents)

### Backup Schedule
- **Full backup**: Daily (off-peak hours)
- **Incremental**: Hourly (transactional tables)
- **Files**: Daily sync to backup location

### Retention
- Last 7 days: Daily backups
- Last 4 weeks: Weekly backups
- Last 12 months: Monthly backups
- Older: Yearly backups (or archive)

## Data Retention Policy

### Active Data (in main tables)
- Current year + previous 2 years

### Archival Candidates
- Sales older than 2 years
- Completed orders older than 3 years
- Delivered shipments older than 1 year

### Permanent Retention
- Products and variants (catalog history)
- Suppliers
- Users
- Stock adjustments (audit trail)

---

## Visual Tools

To generate actual ER diagrams from this database:

### Using MySQL Workbench
1. File → Create EER Model From Database
2. Select your database
3. Choose tables
4. Auto-generate diagram

### Using phpMyAdmin
1. Select database
2. Click "Designer" tab
3. View relationship diagram

### Using Online Tools
- dbdiagram.io
- draw.io
- QuickDBD

## Next Steps

1. Review this schema with your team
2. Identify customization needs
3. Plan database optimizations
4. Set up backup procedures
5. Configure monitoring

For detailed optimization recommendations, see **SQL_ANALYSIS.md**
