<?php
/**
 * Client Gallery - Configuration (example)
 * Copy this file to config.php and update the values below.
 * Keep config.php out of version control (it is in .gitignore).
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'photono_gallery');   // your database name
define('DB_USER', 'your_db_user');      // your MySQL username
define('DB_PASS', 'your_db_password');  // your MySQL password

// Path to upload root (no trailing slash). Default: private/uploads next to this config.
define('UPLOADS_ROOT', __DIR__ . '/uploads');

// Admin password for the upload panel (change this!)
define('ADMIN_PASSWORD', 'change-me-secure-password');
