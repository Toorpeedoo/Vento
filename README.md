# VENTO Inventory Management System

A modern PHP web application for managing store inventory.

## Features

- ✅ Add Products
- ✅ View Products
- ✅ Update Products
- ✅ Delete Products
- ✅ Add/Subtract Quantity
- ✅ Modern, responsive design
- ✅ File-based storage (no database required)

## Requirements

- PHP 7.0 or higher
- Web server (Apache, Nginx, or PHP built-in server)

## Installation

1. Place all files in your web server directory
2. Ensure the `data` directory is writable:
   ```bash
   chmod 777 data
   ```

## Running the Application

### Using PHP Built-in Server (Development)
```bash
cd web
php -S localhost:8000
```

Then open your browser and navigate to:
```
http://localhost:8000
```

### Using Apache/Nginx
1. Place the `web` folder in your web server root
2. Access via: `http://localhost/web/`

## File Structure

```
web/
├── index.php              # Main splash page
├── main_menu.php          # Main menu
├── add_product.php        # Add product form
├── view_products.php      # View all products
├── update_product.php     # Update product form
├── update_menu.php        # Update menu
├── add_quantity.php       # Add quantity form
├── subtract_quantity.php # Subtract quantity form
├── delete_product.php     # Delete product form
├── classes/
│   ├── Product.php        # Product class (OOP)
│   └── FileDatabaseUtil.php # Database utility
├── css/
│   └── style.css          # Modern CSS styles
├── data/
│   └── products.txt       # Data storage (auto-created)
└── .htaccess              # Apache configuration
```

## Data Storage

Products are stored in `data/products.txt` in the format:
```
ID|ProductName|Price|Quantity
```

Example:
```
1|Laptop|999.99|10
2|Mouse|29.99|50
```

## Security Notes

- This is a development application
- For production, add:
  - Input validation
  - CSRF protection
  - Authentication/Authorization
  - SQL injection prevention (if using database)
  - XSS protection

