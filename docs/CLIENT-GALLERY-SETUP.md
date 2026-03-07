# Client Gallery — Setup (Hostinger)

Quick setup so the client gallery works on your server.

## 1. Database

1. In **Hostinger hPanel** → **Databases** → **MySQL Databases**, create a database (e.g. `photono_gallery`) and a user with access to it.
2. Open **phpMyAdmin**, select that database, and run the SQL in **`database/schema.sql`** (creates `clients` and `selections` tables and one example client).

## 2. Config

1. Copy **`private/config.example.php`** to **`private/config.php`** (or create `config.php` from the example).
2. In **`private/config.php`** set **DB_NAME**, **DB_USER**, **DB_PASS** to your database name, username, and password.
3. Set **ADMIN_PASSWORD** to a strong password for the upload panel.

## 3. Upload to server

Upload the whole site (including the **`private/`** folder) into your **`public_html`** (or your web root). The **`private/.htaccess`** file blocks direct access to `private/`, so config and uploads are not reachable by URL.

## 4. Test

- **Client login:** `https://yoursite.com/client-login.html`  
  Use the example client: ID **smith**, code **X7kP92** (change these in the DB for production).
- **Gallery:** After login you’ll see the gallery. Add test images via the admin panel.
- **Admin upload:** `https://yoursite.com/admin_upload.php`  
  Enter the password you set in **ADMIN_PASSWORD**, then choose client folder (e.g. **client_smith**) and upload images.

## 5. Add more clients

In phpMyAdmin, add rows to the **`clients`** table:

| client_id | access_code | folder        |
|-----------|-------------|---------------|
| jones     | Ab3Xy9      | client_jones  |

Create a folder under **`private/uploads/`** with the same name as **folder** (e.g. **client_jones**), or let the admin upload panel create it when you first upload.

## Files overview

| File / folder        | Purpose                          |
|----------------------|----------------------------------|
| `client-login.html`  | Client login form                |
| `login.php`          | Validates code, starts session   |
| `gallery.php`        | Protected gallery + favorites    |
| `image.php`          | Serves images (no direct URLs)   |
| `logout.php`         | Logs out client                  |
| `selection.php`      | API for favorite toggle         |
| `admin_upload.php`   | Password-protected upload panel |
| `private/config.php` | DB credentials + settings       |
| `private/uploads/`   | Client image folders            |
| `database/schema.sql`| DB tables + example client      |

## Security checklist

- [ ] Use **HTTPS** (enable SSL in Hostinger).
- [ ] Change the example client’s **access_code** and add your real clients.
- [ ] Use a strong **ADMIN_PASSWORD**.
- [ ] Do not remove **`private/.htaccess`** (keeps config and uploads private).
