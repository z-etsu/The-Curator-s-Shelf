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
├── index.php                    (Homepage with hero section)
├── config/
│   └── database.php            (Database connection configuration)
├── includes/
│   ├── header.php              (Navigation and user menu)
│   ├── footer.php              (Page footer)
│   └── functions.php           (Helper and database functions)
├── products/
│   ├── list.php                (Browse all products)
│   └── detail.php              (Product detail page with image carousel)
├── cart/
│   ├── view.php                (Shopping cart management)
│   ├── add.php                 (Add to cart - AJAX)
│   ├── remove.php              (Remove from cart - AJAX)
│   ├── update.php              (Update quantity - AJAX)
│   └── get-count.php           (Get cart count - AJAX)
├── checkout/
│   └── index.php               (Checkout and order confirmation)
├── orders/
│   └── index.php               (Order history and order details)
├── auth/
│   ├── login.php               (Login page)
│   ├── register.php            (Create account)
│   ├── logout.php              (Logout handler)
│   └── settings.php            (Account settings)
├── assets/
│   ├── css/
│   │   └── style.css           (Responsive styling)
│   ├── images/                 (Product images)
│   └── js/
│       └── main.js             (Cart and modal interactions)
├── sql/
│   ├── schema.sql              (Database schema and initial data)
│   └── update_product_images.sql (Product image migration)
├── PRODUCTS_REFERENCE.txt       (Product list reference)
└── README.md                    (This file)
```

---

## Features

- **Browse Products**: Explore our collection of 18 action figures with detailed product pages, multiple images, and stock information
- **User Accounts**: Create an account and log in to save your shopping history and preferences
- **Shopping Cart**: Add items to your cart, adjust quantities, and manage selections. Your cart persists across sessions
- **Order Management**: View your complete order history and detailed information about past purchases
- **Secure Checkout**: Simple checkout process with automatic shipping address entry and order confirmation
- **Real-time Updates**: Cart totals update instantly as you select items

---

## Getting Started as a Customer

1. **Create an Account** or use the signup feature
2. **Browse Products** using the "Shop" menu to see all available action figures
3. **View Details** by clicking on any product to see images, description, and pricing
4. **Build Your Order** by adding items to your cart
5. **Check Out** when ready, and receive an order confirmation with your order number
6. **Track Orders** by visiting "My Orders" in your profile menu

---

## Database Overview

The application uses a MySQL database with the following structure:

- **users** — Customer account information
- **products** — 18 action figure catalogs with pricing and descriptions
- **cart_items** — Persistent shopping cart data
- **orders** — Customer order records
- **order_items** — Individual items within each order
- **categories** — Product categorization

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
