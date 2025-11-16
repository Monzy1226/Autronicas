# XAMPP Setup Guide for Autronicas Inventory Management System

## Step 1: Start XAMPP Services

1. Open **XAMPP Control Panel** (search for "XAMPP Control Panel" in Windows Start menu)
2. Click **Start** button next to **Apache**
3. Click **Start** button next to **MySQL**
4. Both should show green "Running" status

## Step 2: Move Project to XAMPP htdocs Folder

1. Locate your XAMPP installation folder (usually `C:\xampp\`)
2. Navigate to `C:\xampp\htdocs\`
3. Copy the entire **Autronicas** project folder to `C:\xampp\htdocs\`
   - Final path should be: `C:\xampp\htdocs\Autronicas\`

## Step 3: Create the Database

1. Open your web browser
2. Go to: `http://localhost/phpmyadmin`
3. Click on **"SQL"** tab at the top
4. Click **"Choose File"** button
5. Navigate to your project folder and select `database.sql`
6. Click **"Go"** button
7. You should see a success message confirming the database and tables were created

**Alternative Method (Manual):**
1. In phpMyAdmin, click **"New"** in the left sidebar
2. Enter database name: `autronicas_db`
3. Select collation: `utf8mb4_general_ci`
4. Click **"Create"**
5. Select the `autronicas_db` database
6. Click **"Import"** tab
7. Choose `database.sql` file and click **"Go"**

## Step 4: Verify Database Configuration

1. Open `config.php` in your project folder
2. Verify these settings match your XAMPP setup:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'autronicas_db');
   define('DB_USER', 'root');
   define('DB_PASS', '');  // Empty by default in XAMPP
   ```
3. If you changed the MySQL root password, update `DB_PASS` accordingly

## Step 5: Access Your Application

1. Open your web browser
2. Navigate to: `http://localhost/Autronicas/index.php`
3. You should see the login page
4. Click **"Sign up"** to create your first user account
5. After registration, you can log in and start using the system!

## Troubleshooting

### Apache won't start
- Check if port 80 is already in use (common with Skype, IIS, etc.)
- In XAMPP Control Panel, click **"Config"** next to Apache → **"httpd.conf"**
- Change `Listen 80` to `Listen 8080` (or another available port)
- Access via: `http://localhost:8080/Autronicas/index.php`

### MySQL won't start
- Check if port 3306 is already in use
- In XAMPP Control Panel, click **"Config"** next to MySQL → **"my.ini"**
- Change the port if needed
- Update `DB_HOST` in `config.php` to `localhost:3307` (or your chosen port)

### "Database connection failed" error
- Verify MySQL is running in XAMPP Control Panel
- Check that database `autronicas_db` exists in phpMyAdmin
- Verify credentials in `config.php` match your MySQL setup
- Make sure you imported `database.sql` successfully

### Page shows "404 Not Found"
- Verify the project folder is in `C:\xampp\htdocs\Autronicas\`
- Check the URL path matches your folder name
- Make sure Apache is running

### PHP errors appear
- Check `config.php` - make sure `error_reporting` and `display_errors` are set appropriately
- Check XAMPP error logs: `C:\xampp\apache\logs\error.log`

## Quick Test

After setup, test these features:
1. ✅ Register a new user
2. ✅ Login with your credentials
3. ✅ Add an inventory item
4. ✅ View dashboard statistics
5. ✅ Create a job order
6. ✅ Add a sale

## File Structure

Your project should look like this in `htdocs`:
```
C:\xampp\htdocs\Autronicas\
├── api\
│   ├── dashboard.php
│   ├── inventory.php
│   ├── job_orders.php
│   ├── login.php
│   ├── logout.php
│   ├── register.php
│   └── sales.php
├── config.php
├── db.php
├── auth.php
├── database.sql
├── index.php
├── register.php
├── home.php
├── dashboard.php
├── inventory.php
├── sales.php
├── job-order.php
└── (CSS files, images, etc.)
```

## Need Help?

If you encounter any issues:
1. Check XAMPP Control Panel for error messages
2. Check browser console (F12) for JavaScript errors
3. Check Apache error logs
4. Verify all files are in the correct location

