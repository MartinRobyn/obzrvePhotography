# Step 1: Set up the database (Hostinger) — Step by step

Do these in order. When you’re done, you’ll have a database, a user, and the tables the client gallery needs.

---

## Part A — Create the database and user

1. **Log in to Hostinger**  
   Go to [hostinger.com](https://www.hostinger.com) and sign in. Open your **hPanel** (hosting control panel).

2. **Open Databases Management**  
   - Go to **Websites** → **Manage** (click **Manage** for the site you’re using).  
   - In the **left sidebar**, use the search box and type **Databases Management**, then click it.  
   - (Or look under a “Databases” or “Advanced” section for **Databases Management**.)

3. **Create the database and user in one go**  
   Hostinger uses one form that creates both the database and its user together:  
   - Find the section **“Create a New MySQL Database And Database User”**.  
   - Select the **website** (domain) in the dropdown at the top if asked.  
   - **Database name:** e.g. `photono_gallery` (Hostinger will add a prefix like `u123456789_` — you’ll see the full name after creation).  
   - **Username:** e.g. `photono_user` (same prefix is added).  
   - **Password:** choose a strong password (Hostinger requires at least one number, one uppercase and one lowercase letter, 8+ characters). Use **Generate** if available, then **copy and save it**.  
   - Click **Create**.  
   - The new database is **automatically assigned** to the selected domain, and that **one user is automatically assigned to that database with full access**.  
   - **Write down** the full **database name**, **username** (with prefix), and **password** for Step 2 (config).

4. **Where to assign a user or set “All privileges” (if you created DB and user separately)**  
   If your screen has separate “create database” and “create user” steps instead of the single form above:  
   - On the same **Databases Management** page, find **“List of Current MySQL Databases And Users”**.  
   - Find your database in the list.  
   - Click the **⋮** (three dots) button **next to that database**.  
   - Choose **“Change Permissions”** (or “Assign user” / “Manage user” if you see it).  
   - By default, the user already has **all permissions** (all checkboxes checked). To give “All privileges”, leave all boxes checked, or check every permission.  
   - Click **Update** (or **Save**) to apply.

**Checkpoint:** You have one database and one user that can use that database. You’ve saved: **database name**, **username**, **password**.

---

## Part B — Create the tables (phpMyAdmin)

6. **Open phpMyAdmin**  
   - In hPanel, find **Databases** → **phpMyAdmin** (or a “phpMyAdmin” link in the MySQL section).  
   - Click it. phpMyAdmin opens in a new tab or window.

7. **Select your database**  
   - In the left sidebar, click the name of your database (e.g. `u123_photono_gallery`).  
   - The main area will show “No tables” or an empty list. That’s expected.

8. **Open the SQL tab**  
   - At the top of the main area, click the **“SQL”** tab.  
   - You’ll see a big text box where you can type or paste SQL.

9. **Paste the schema**  
   - Open the file **`database/schema.sql`** from your Photography site folder in a text editor.  
   - Select all the text (Ctrl+A / Cmd+A) and copy it (Ctrl+C / Cmd+C).  
   - Go back to phpMyAdmin and paste into the SQL box (Ctrl+V / Cmd+V).

10. **Run the SQL**  
    - Click **“Go”** (bottom right of the SQL box).  
    - You should see a green success message like “X queries executed successfully.”

11. **Check the tables**  
    - In the left sidebar, under your database name, you should now see:  
      - **clients**  
      - **selections**  
    - Click **clients**. You should see one row: `smith` / `X7kP92` / `client_smith`.

**Checkpoint:** Database has **clients** and **selections** tables, and **clients** has one example row (smith).

---

## What to do next

- Use the **database name**, **username**, and **password** in **Step 2** when you edit **`private/config.php`** (DB_NAME, DB_USER, DB_PASS).
- Then continue with **Step 2** in **CLIENT-GALLERY-SETUP.md** (config and upload).

---

## If something goes wrong

- **“Access denied” in phpMyAdmin**  
  Make sure you assigned the user to the database and gave privileges (Part A, step 5).

- **“Database doesn’t exist”**  
  Double-check the exact database name (including prefix) in hPanel → MySQL Databases.

- **SQL error when clicking “Go”**  
  Make sure you pasted the *entire* contents of `database/schema.sql` and didn’t add extra characters. Try running it again; “CREATE TABLE IF NOT EXISTS” is safe to run more than once.

- **Can’t find phpMyAdmin**  
  In hPanel, use the search box and type “phpMyAdmin”, or look under **Databases** or **Advanced**.
