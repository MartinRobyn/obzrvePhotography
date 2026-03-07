# Run the client gallery locally

Use this to open the admin upload page and the rest of the gallery on your computer (e.g. `http://localhost:8000/admin_upload.php`).

You need **PHP** and **MySQL** running locally. Two ways to do it:

---

## Option A — XAMPP (easiest on Windows)

XAMPP gives you Apache + PHP + MySQL in one install.

### 1. Install XAMPP

- Download from [apachefriends.org](https://www.apachefriends.org/download.html) (pick PHP 8.x).
- Run the installer. Install to something like `C:\xampp`.
- Open **XAMPP Control Panel**. Start **Apache** and **MySQL** (click **Start** for each).

### 2. Put your site where Apache can see it

- Copy your **entire Photography site folder** (with `admin_upload.php`, `private/`, `database/`, etc.) into the web root:
  - **XAMPP:** `C:\xampp\htdocs\`
  - Create a folder there, e.g. `C:\xampp\htdocs\photography` and put all your site files inside it.

So you should have:
`C:\xampp\htdocs\photography\admin_upload.php`  
`C:\xampp\htdocs\photography\private\config.php`  
etc.

### 3. Create the database (MySQL in XAMPP)

- In XAMPP, MySQL is usually **root** with **no password**.
- Open **http://localhost/phpmyadmin** in your browser.
- Click **New** (or “Databases”) → create a database named `photono_gallery`.
- Click that database in the left sidebar → **SQL** tab.
- Paste the contents of your **`database/schema.sql`** file and click **Go**.

### 4. Config for local

- In `C:\xampp\htdocs\photography\private\` copy **config.example.php** to **config.php** (if you don’t have one yet).
- Edit **config.php** and set:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'photono_gallery');
define('DB_USER', 'root');
define('DB_PASS', '');   // empty for default XAMPP
define('UPLOADS_ROOT', __DIR__ . '/uploads');
define('ADMIN_PASSWORD', 'change-me-secure-password');  // pick any password
```

Save the file.

### 5. Open the admin upload page

In your browser go to:

**http://localhost/photography/admin_upload.php**

(If you put the site in a different folder under `htdocs`, use that folder name instead of `photography`.)

- Enter the **ADMIN_PASSWORD** you set in config.
- You should see the upload form. Choose client folder **client_smith** and upload a test image.

Other URLs:

- Client login: **http://localhost/photography/client-login.html**
- After login (smith / X7kP92): **http://localhost/photography/gallery.php**

---

## Option B — PHP built-in server + MySQL

Use this if you already have PHP and MySQL installed (e.g. from [php.net](https://windows.php.net/download/) and MySQL Installer or similar).

### 1. MySQL running and database created

- Start MySQL (service or `mysql` in terminal).
- Create a database, e.g. `photono_gallery`, and a user that can access it.
- In that database, run **`database/schema.sql`** (via MySQL command line or a GUI like phpMyAdmin / HeidiSQL).

### 2. Config

- In **`private/`** ensure you have **config.php** (copy from **config.example.php** if needed).
- Set **DB_HOST**, **DB_NAME**, **DB_USER**, **DB_PASS** to your local MySQL details, and **ADMIN_PASSWORD** to whatever you want.

### 3. Start PHP’s built-in server

- Open a terminal (PowerShell or Command Prompt).
- Go to your Photography site folder, e.g.:

```bash
cd "C:\Users\Robyn\Documents\Photography site"
```

- Run:

```bash
php -S localhost:8000
```

- Leave this window open. You should see something like: `Development Server (http://localhost:8000) started`.

### 4. Open the admin upload page

In your browser go to:

**http://localhost:8000/admin_upload.php**

- Enter your admin password and use the upload form.

Other URLs:

- **http://localhost:8000/client-login.html** — client login  
- **http://localhost:8000/gallery.php** — gallery (after logging in as smith / X7kP92)

To stop the server: in the terminal press **Ctrl+C**.

---

## Quick reference

| What you want        | URL (XAMPP)                      | URL (PHP server)                |
|----------------------|----------------------------------|---------------------------------|
| Admin upload         | http://localhost/photography/admin_upload.php | http://localhost:8000/admin_upload.php |
| Client login         | http://localhost/photography/client-login.html | http://localhost:8000/client-login.html |
| Gallery (after login)| http://localhost/photography/gallery.php       | http://localhost:8000/gallery.php        |

Use the folder name you chose under `htdocs` (e.g. `photography`) in the XAMPP URLs. For PHP built-in server, the “folder” is just the port: **8000**.
