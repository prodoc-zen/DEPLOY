# Laravel Deployment on Vercel

This guide explains how to deploy a **Laravel** application to **Vercel** using a serverless PHP runtime.

Since Laravel normally expects a traditional PHP server (Apache/Nginx), we configure a **serverless entry point** and routing so Vercel can execute the application properly.

---

# Prerequisites

Before starting, make sure you have:

* PHP installed
* Composer installed
* Node.js and npm installed
* A Laravel project ready
* A Vercel account
* Vercel CLI installed globally

Install Vercel CLI if needed:

```bash
npm install -g vercel
```

---

# Step 1 — Create Serverless Entry Point

Inside your Laravel project root, create a folder:

```
api
```

Inside the `api` folder, create a file:

```
lambda.php
```

Paste the following code inside `api/lambda.php`:

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
```

This file acts as the **serverless function entry point** for Laravel.

---

# Step 2 — Force HTTPS in Production

Open:

```
app/Providers/AppServiceProvider.php
```

Import the URL facade at the top:

```php
use Illuminate\Support\Facades\URL;
```

Inside the `boot()` method add:

```php
if (config('app.env') === 'production') {
    URL::forceScheme('https');
}
```

This ensures Laravel generates HTTPS URLs in production.

---

# Step 3 — Create `.vercelignore`

In the **project root**, create:

```
.vercelignore
```

Add the following:

```
public/index.php
```

This prevents Vercel from using Laravel’s default entry point.

---

# Step 4 — Create `vercel.json`

In the **project root**, create:

```
vercel.json
```

Add the following configuration:

```json
{
  "$schema": "https://openapi.vercel.sh/vercel.json",
  "outputDirectory": "public",
  "functions": {
    "api/lambda.php": {
      "runtime": "vercel-php@0.7.4"
    }
  },
  "rewrites": [
    {
      "source": "/(.*)",
      "destination": "/api/lambda.php"
    }
  ],
  "buildCommand": "echo skip..."
}
```

Explanation:

* `functions` tells Vercel to run PHP using a serverless runtime.
* `rewrites` routes all requests to Laravel.
* `outputDirectory` points to the public folder.

---

# Step 5 — Modify `composer.json`

Open `composer.json`.

Locate the `scripts` section and add a **vercel script** below `pre-package-uninstall`.

Example:

```json
"vercel": [
    "npm run build",
    "mkdir -p /vercel/output/static",
    "cp -r public/build /vercel/output/static/"
]
```

This ensures frontend assets are copied during deployment.

---

# Step 6 — Configure Environment Variables

Update your `.env` file for production:

```
APP_NAME=Laravel
APP_ENV=production
APP_KEY=[generate using php artisan key:generate]
APP_DEBUG=true
APP_URL=[your vercel deployment URL]

APP_CONFIG_CACHE=/tmp/config.php
APP_ROUTES_CACHE=/tmp/routes.php
APP_EVENTS_CACHE=/tmp/events.php
APP_PACKAGES_CACHE=/tmp/packages.php
APP_SERVICES_CACHE=/tmp/services.php

VIEW_COMPILED_PATH=/tmp/views

LOG_CHANNEL=stderr
SESSION_DRIVER=cookie
ASSET_URL=/

CACHE_DRIVER=array
CACHE_STORE=array
```

Then add your **database configuration** as required.

Example:

```
DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_PORT=3306
DB_DATABASE=your-db
DB_USERNAME=your-user
DB_PASSWORD=your-password
```

---

# Step 7 — Deploy to Vercel

Login to Vercel:

```bash
vercel login
```

Then deploy from your project folder:

```bash
vercel
```

Follow the CLI prompts.

After deployment, Vercel will provide a URL such as:

```
https://your-project.vercel.app
```

---

# Notes

* Laravel runs using a **serverless PHP runtime**.
* The `/tmp` directory is used because Vercel's filesystem is read-only.
* Static assets are served from `/public`.

---

# Troubleshooting

### Error: "No application encryption key has been specified"

Generate an app key:

```bash
php artisan key:generate
```

Then update the `APP_KEY` in your `.env`.

---

### Build errors

Make sure dependencies are installed:

```bash
composer install
npm install
npm run build
```

---

# Summary

Steps performed:

1. Create `api/lambda.php`
2. Force HTTPS in `AppServiceProvider`
3. Add `.vercelignore`
4. Configure `vercel.json`
5. Add Vercel script to `composer.json`
6. Configure `.env`
7. Deploy with Vercel CLI

---

Your Laravel application should now run successfully on Vercel.
