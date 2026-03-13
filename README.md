# The Curator's Shelf - Setup Instructions

## Quick Start Guide for XAMPP

### Step 1: Move Project to XAMPP
1. Copy the entire `CURATOR` folder to `C:\xampp\htdocs\` so the path is: `C:\xampp\htdocs\CURATOR`

### Step 2: Start XAMPP
1. Open XAMPP Control Panel
2. Click **Start** next to Apache
3. Click **Start** next to MySQL

### Step 3: Create Database
1. Open your browser and go to `http://localhost/phpmyadmin`
2. Click **New** (or the + button) to create a new database
3. Name it **`curator_shelf`** (exactly as written)
4. Click **Create**
5. Select the `curator_shelf` database
6. Go to **Import** tab
7. Click **Choose File** and select `sql/schema.sql` from your project folder
8. Click **Import**

The database will now be set up with all tables and sample products!

### Step 4: Run the Application
1. Open your browser and go to: `http://localhost/CURATOR/`
2. You should see the homepage with the hero section and featured products!

---

## Project Structure

```
CURATOR/
├── index.php                    (Homepage)
├── config/
│   └── database.php            (DB connection)
├── includes/
│   ├── header.php              (Navigation)
│   ├── footer.php              (Footer)
│   └── functions.php           (Helper functions)
├── products/
│   ├── list.php                (All products page)
│   └── detail.php              (Single product page)
├── cart/
│   ├── view.php                (Cart display)
│   ├── add.php                 (Add to cart - AJAX)
│   ├── remove.php              (Remove from cart - AJAX)
│   ├── update.php              (Update quantity - AJAX)
│   └── get-count.php           (Get cart count - AJAX)
├── checkout/
│   └── index.php               (Checkout & order confirmation)
├── auth/
│   ├── login.php               (Login page)
│   ├── register.php            (Signup page)
│   └── logout.php              (Logout handler)
├── assets/
│   ├── css/
│   │   └── style.css           (All styling)
│   └── js/
│       └── main.js             (Cart functionality, AJAX)
├── sql/
│   └── schema.sql              (Database schema + sample data)
└── README.md                   (This file)
```

---

## Testing Checklist (Phase 1)

- [ ] **Homepage**: Hero section visible, featured products displayed
- [ ] **Product Listing**: Click "Shop" → see all 10 products
- [ ] **Product Detail**: Click product → see full details, stock info
- [ ] **Add to Cart**: Click "Add to Cart" → success message, cart count updates
- [ ] **Cart Page**: View cart items, update quantities, remove items
- [ ] **Signup**: Create new account with valid email/password
- [ ] **Login**: Login with created account, see greeting in header
- [ ] **Checkout**: Fill shipping form → see order confirmation with Order ID
- [ ] **Database**: Check `orders` and `order_items` tables in phpmyadmin to verify order was saved

---

## Sample Accounts for Testing

Once the database is imported, you can immediately start by:
1. Creating a new account (click "Sign Up")
2. Or implementing admin accounts in Phase 2

---

## Database Details

### Tables Created:
- **users** — Stores customer/admin accounts
- **products** — 10 sample Spider-Man action figures
- **cart_items** — Session-based cart (currently stored in PHP session)
- **orders** — Completed orders
- **order_items** — Items in each order

### Sample Data:
- 10 Spider-Man Mafex Edition figures (various prices and stock levels)
- All ready to browse, add to cart, and checkout

---

## Phase 2 (Next Steps)

After Day 3 delivery, we'll add:
- Product search/filter
- Admin panel for managing products
- Admin login with separate dashboard
- Persistent shopping cart (save to database instead of session)

---

## Troubleshooting

### **"Database connection failed"**
- Check XAMPP MySQL is running
- Verify database name is `curator_shelf` (exact spelling)
- Check username is `root` and password is empty (default XAMPP)

### **"Page not found" (404 errors)**
- Make sure project folder is at `C:\xampp\htdocs\CURATOR`
- Access via `http://localhost/CURATOR/` (not just `http://localhost`)

### **Products not showing**
- Make sure you imported `sql/schema.sql` to the database
- Check phpmyadmin → curator_shelf → products table has 10 rows

### **Cart not working**
- Press F12 to open Developer Console
- Check for JavaScript errors
- Make sure `assets/js/main.js` is loading

---

## Questions?

If you run into any issues, let me know and we'll debug together!
