# Entity Relationship Diagram (ERD)

## Database Overview

The Boutique Store Management System uses a relational MySQL database with 11 core tables and 3 SQL views for reporting. The database is designed to manage multi-branch boutique operations with role-based access control, inventory management, sales transactions, and audit logging.

---

## Core Tables & Relationships

### 1. **ROLES** (Role Management)
```
roles
├── id (PK): INT, Auto-Increment
├── name: VARCHAR(100), UNIQUE
├── description: TEXT
└── created_at: TIMESTAMP
```
**Purpose**: Define system user roles (Manager, Store Keeper, Seller)  
**Relationships**: 1:N with `permissions`, 1:N with `users`

---

### 2. **PERMISSIONS** (Role-Based Access Control)
```
permissions
├── id (PK): INT, Auto-Increment
├── role_id (FK): INT → roles(id) [CASCADE]
├── permission: VARCHAR(100)
├── created_at: TIMESTAMP
└── UNIQUE(role_id, permission)
```
**Purpose**: Define granular permissions per role  
**Relationships**: N:1 with `roles`  
**Indexes**: role_id

---

### 3. **BRANCHES** (Store Locations)
```
branches
├── id (PK): INT, Auto-Increment
├── name: VARCHAR(100)
├── address: TEXT
├── phone: VARCHAR(20)
├── email: VARCHAR(150)
├── manager_id (FK): INT → users(id)
├── is_active: BOOLEAN
├── created_at: TIMESTAMP
└── updated_at: TIMESTAMP
```
**Purpose**: Represent individual store locations  
**Relationships**: 1:N with `users`, 1:N with `stock`, 1:N with `sales`, 1:N with `transfers`  
**Indexes**: is_active, manager_id

---

### 4. **USERS** (System Users)
```
users
├── id (PK): INT, Auto-Increment
├── username: VARCHAR(100), UNIQUE
├── email: VARCHAR(150), UNIQUE
├── password: VARCHAR(255)
├── first_name: VARCHAR(100)
├── last_name: VARCHAR(100)
├── phone: VARCHAR(20)
├── role_id (FK): INT → roles(id) [RESTRICT]
├── branch_id (FK): INT → branches(id) [SET NULL]
├── is_active: BOOLEAN
├── last_login: DATETIME
├── login_attempts: INT
├── locked_until: DATETIME
├── created_at: TIMESTAMP
└── updated_at: TIMESTAMP
```
**Purpose**: Store user accounts with authentication and authorization data  
**Relationships**: N:1 with `roles`, N:1 with `branches`, 1:N with `sales`, 1:N with `stock_history`, 1:N with `transfers`, 1:N with `audit_logs`  
**Indexes**: role_id, branch_id, is_active

---

### 5. **CATEGORIES** (Item Classifications)
```
categories
├── id (PK): INT, Auto-Increment
├── name: VARCHAR(100)
├── description: TEXT
├── created_at: TIMESTAMP
└── updated_at: TIMESTAMP
```
**Purpose**: Classify items (Clothing, Accessories, Footwear)  
**Relationships**: 1:N with `items`

---

### 6. **ITEMS** (Product Inventory Master)
```
items
├── id (PK): INT, Auto-Increment
├── name: VARCHAR(150)
├── sku (UNIQUE): VARCHAR(50)
├── category_id (FK): INT → categories(id) [RESTRICT]
├── description: TEXT
├── cost_price: DECIMAL(10,2)
├── selling_price: DECIMAL(10,2)
├── reorder_level: INT
├── is_active: BOOLEAN
├── created_at: TIMESTAMP
└── updated_at: TIMESTAMP
```
**Purpose**: Master product catalog with pricing and reorder logic  
**Relationships**: N:1 with `categories`, 1:N with `stock`, 1:N with `sales_items`, 1:N with `stock_history`, 1:N with `transfers`  
**Indexes**: sku, category_id, is_active

---

### 7. **STOCK** (Branch Inventory Levels)
```
stock
├── id (PK): INT, Auto-Increment
├── item_id (FK): INT → items(id) [CASCADE]
├── branch_id (FK): INT → branches(id) [CASCADE]
├── quantity: INT
├── reserved_quantity: INT
├── damaged_quantity: INT
├── last_restock_date: DATE
├── updated_at: TIMESTAMP
└── UNIQUE(item_id, branch_id)
```
**Purpose**: Track real-time inventory levels per item per branch  
**Relationships**: N:1 with `items`, N:1 with `branches`, 1:N with `stock_history`  
**Indexes**: item_id, branch_id, (branch_id, quantity)

---

### 8. **STOCK_HISTORY** (Inventory Audit Trail)
```
stock_history
├── id (PK): INT, Auto-Increment
├── item_id (FK): INT → items(id) [CASCADE]
├── branch_id (FK): INT → branches(id) [CASCADE]
├── type: ENUM('in', 'out', 'damage', 'transfer', 'adjustment')
├── quantity_change: INT
├── reference_type: VARCHAR(50)
├── reference_id: INT
├── notes: TEXT
├── user_id (FK): INT → users(id) [RESTRICT]
├── created_at: TIMESTAMP
└── Indexes: created_at, (reference_type, reference_id)
```
**Purpose**: Maintain complete audit trail of all inventory movements  
**Relationships**: N:1 with `items`, N:1 with `branches`, N:1 with `users`  
**Indexes**: created_at, (reference_type, reference_id)

---

### 9. **TRANSFERS** (Inter-Branch Stock Transfers)
```
transfers
├── id (PK): INT, Auto-Increment
├── item_id (FK): INT → items(id) [CASCADE]
├── from_branch_id (FK): INT → branches(id) [CASCADE]
├── to_branch_id (FK): INT → branches(id) [CASCADE]
├── quantity: INT
├── status: ENUM('pending', 'in_transit', 'completed', 'cancelled')
├── initiated_by (FK): INT → users(id) [RESTRICT]
├── approved_by (FK): INT → users(id) [SET NULL]
├── transferred_at: DATETIME
├── created_at: TIMESTAMP
├── updated_at: TIMESTAMP
└── Index: status
```
**Purpose**: Manage stock transfers between branches with approval workflow  
**Relationships**: N:1 with `items`, N:1 with `branches` (from), N:1 with `branches` (to), N:1 with `users`  
**Indexes**: status

---

### 10. **SALES** (Sales Transactions)
```
sales
├── id (PK): INT, Auto-Increment
├── transaction_number (UNIQUE): VARCHAR(50)
├── branch_id (FK): INT → branches(id) [CASCADE]
├── user_id (FK): INT → users(id) [RESTRICT]
├── total_amount: DECIMAL(12,2)
├── discount_amount: DECIMAL(10,2)
├── final_amount: DECIMAL(12,2)
├── payment_method: ENUM('cash', 'card', 'check', 'other')
├── notes: TEXT
├── created_at: TIMESTAMP
└── updated_at: TIMESTAMP
```
**Purpose**: Record each sales transaction  
**Relationships**: N:1 with `branches`, N:1 with `users`, 1:N with `sales_items`  
**Indexes**: (branch_id, created_at), (user_id, created_at)

---

### 11. **SALES_ITEMS** (Sales Line Items)
```
sales_items
├── id (PK): INT, Auto-Increment
├── sales_id (FK): INT → sales(id) [CASCADE]
├── item_id (FK): INT → items(id) [RESTRICT]
├── quantity: INT
├── unit_price: DECIMAL(10,2)
├── subtotal: DECIMAL(12,2)
├── discount: DECIMAL(10,2)
├── created_at: TIMESTAMP
└── Index: sales_id
```
**Purpose**: Store line items within each sales transaction  
**Relationships**: N:1 with `sales`, N:1 with `items`  
**Indexes**: sales_id

---

### 12. **AUDIT_LOGS** (System Action Tracking)
```
audit_logs
├── id (PK): INT, Auto-Increment
├── user_id (FK): INT → users(id) [SET NULL]
├── action: VARCHAR(100)
├── entity_type: VARCHAR(100)
├── entity_id: INT
├── old_values: JSON
├── new_values: JSON
├── ip_address: VARCHAR(45)
├── user_agent: VARCHAR(255)
├── created_at: TIMESTAMP
└── Indexes: created_at, (user_id, action)
```
**Purpose**: Track all system actions for compliance and security  
**Relationships**: N:1 with `users`  
**Indexes**: created_at, (user_id, action)

---

## Relationship Summary

```
┌─────────────┐
│    ROLES    │
└─────────────┘
     ▲ │
     │ └─────────────┐
     │               ▼
     │        ┌──────────────┐
     │        │ PERMISSIONS  │
     │        └──────────────┘
     │
     └───────┐
             ▼
      ┌─────────────┐     ┌──────────────┐
      │    USERS    │────▶│   BRANCHES   │
      └─────────────┘     └──────────────┘
             │                    │
             │                    ▼
             │            ┌──────────────┐
             │            │    STOCK     │
             │            └──────────────┘
             │                    │
             ├────────────────────┘
             │
             ├────────────────┐
             │                ▼
             │         ┌─────────────────┐
             │         │  STOCK_HISTORY  │
             │         └─────────────────┘
             │
             ├──────────────────────────┐
             │                          ▼
             │                   ┌─────────────┐
             │                   │ AUDIT_LOGS  │
             │                   └─────────────┘
             │
             ├────────────────┐
             │                ▼
             │          ┌─────────────┐
             │          │  TRANSFERS  │
             │          └─────────────┘
             │                △
             │                │
             ▼                │
      ┌────────────┐          │
      │   SALES    │          │
      └────────────┘          │
             │                │
             ▼         ┌──────────────┐
      ┌────────────────┤   CATEGORIES │
      │                └──────────────┘
      │                       △
      ▼                       │
┌─────────────────┐           │
│  SALES_ITEMS    │─────▶┌─────────┐
└─────────────────┘      │  ITEMS  │
                         └─────────┘
```

---

## Key Design Features

### **Referential Integrity**
- **CASCADE Delete**: Deleting items/branches cascades to related stock and sales records
- **RESTRICT Delete**: Prevents deletion of roles/users with active relationships
- **SET NULL**: Allows optional deletion of manager assignments

### **Data Types**
- **DECIMAL(10,2)** for prices to avoid floating-point precision issues
- **DECIMAL(12,2)** for totals to handle large amounts
- **JSON** for audit logs to store dynamic old/new values
- **ENUM** for fixed option sets (payment methods, transfer status)

### **Indexes for Performance**
- **Primary Keys**: Automatic on all id columns
- **Foreign Keys**: Automatically indexed for join performance
- **Date Indexes**: created_at columns for time-range queries
- **Composite Indexes**: (branch_id, quantity) for stock queries, (user_id, action) for audit trails

### **Views for Reporting**
1. **daily_sales_summary** - Sales metrics by date, branch, and user
2. **low_stock_alerts** - Items below reorder level
3. **fast_moving_items** - Top-selling products in last 30 days

---

## Database Statistics

| Table | Records | Purpose |
|-------|---------|---------|
| roles | 3 | System role definitions |
| permissions | Variable | Role-based permissions |
| branches | Variable | Store locations |
| users | Variable | System users |
| categories | 3+ | Item classifications |
| items | Variable | Product master data |
| stock | Variable | Inventory per item/branch |
| stock_history | Variable | Inventory audit trail |
| transfers | Variable | Inter-branch transfers |
| sales | Variable | Transaction records |
| sales_items | Variable | Line items per transaction |
| audit_logs | Variable | System action tracking |

---

## Constraints & Rules

### **Unique Constraints**
- `roles.name` - One role name per system
- `users.username` - One username per system
- `users.email` - One email per system
- `items.sku` - One SKU per item
- `stock(item_id, branch_id)` - One inventory record per item per branch
- `permissions(role_id, permission)` - One permission per role
- `sales.transaction_number` - Unique transaction identifier

### **Foreign Key Constraints**
- Users must belong to a role
- Items must belong to a category
- Sales must link to a branch and user
- Stock records must link to item and branch

---

## Usage Example

### **Creating a New Sale**
1. Insert record into `sales` table
2. Insert line items into `sales_items` (references sales.id)
3. Update `stock` quantities for each item
4. Insert entries into `stock_history` for audit trail
5. Auto-logged to `audit_logs` by application trigger

### **Transferring Stock**
1. Create `transfers` record (pending status)
2. Manager reviews and updates status to 'approved'
3. Stock keeper initiates transfer (in_transit)
4. Upon receipt, mark as 'completed'
5. `stock_history` records the transfer automatically

---

## Performance Considerations

- **Partitioning**: Consider DATE-based partitioning on `sales` and `stock_history` for large datasets
- **Archive Strategy**: Implement archival for sales older than 2 years
- **Query Optimization**: All FK columns are indexed; frequently queried columns have dedicated indexes
- **Transaction Safety**: All modifications use transactions to maintain data consistency
