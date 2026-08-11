# Glow Co. — V2 (PHP + MySQL)

Premium body cream / perfume e-commerce store. PHP + MySQL, sessions, Paystack payments.

> **Vercel note:** this app is **not** Vercel-compatible. Vercel hosts static sites and
> Node/Edge serverless functions only — it cannot run PHP or MySQL. Use **Railway** or
> **Render** instead (files for both are included).

## Stack

- PHP 8+ (no composer dependencies)
- MySQL / MariaDB
- Sessions-based auth (bcrypt), CSRF protection on all POST forms
- Paystack payment gateway (cURL with a stream fallback)

## Run locally (XAMPP / built-in server)

```bash
# 1. Create the database and seed sample data
mysql -u root -p < database/glowco_db.sql

# 2. Start the built-in server (MySQL must be running)
php -S localhost:8000
# open http://localhost:8000
```

Or copy the project into `htdocs/` and open `http://localhost/Glowco-V2/`.

### Environment variables (optional)

The app works out of the box on a default XAMPP setup (`localhost`, `root`, empty password,
DB `glowco_db`). To override, set these env vars:

| Variable              | Default                         | Notes                              |
| --------------------- | ------------------------------- | ---------------------------------- |
| `APP_URL`             | auto-detected from host         | full public URL, e.g. `https://x.onrender.com` |
| `DB_HOST`             | `localhost`                     |                                    |
| `DB_PORT`             | `3306`                          |                                    |
| `DB_NAME`             | `glowco_db`                     |                                    |
| `DB_USER`             | `root`                          |                                    |
| `DB_PASS`             | *(empty)*                       |                                    |
| `PAYSTACK_SECRET_KEY` | placeholder                     | set a real key for live payments   |
| `PAYSTACK_PUBLIC_KEY` | placeholder                     |                                    |

Copy `.env.example` → `.env` (or set them in the hosting dashboard).

## Default login

The seed SQL creates an admin account:

- **Email:** `admin@example.com`
- **Password:** `REDACTED`

**Change this password before going live.**

## Deploy to Railway (recommended)

1. Push this repo to GitHub.
2. On [Railway](https://railway.app), create a new project → **MySQL** template (add-on).
3. Add a **New Service** → **Deploy from GitHub repo**. Railway uses `nixpacks.toml`
   (auto-detects PHP and runs `start.sh`).
4. In the service's **Variables**, connect the MySQL add-on (host/port/user/pass/name)
   and set `APP_URL=https://<your-railway-domain>`.
5. Open the MySQL add-on's **Shell** and run the schema:
   ```bash
   mysql $MYSQL_DATABASE < database/glowco_db.sql
   ```
6. Open your `*.up.railway.app` URL.

## Deploy to Render

1. Push this repo to GitHub.
2. **New +** → **Blueprint** and pick the repo — `render.yaml` is read automatically
   (env vars are set in the dashboard, or replace the `sync: false` values).
   Or create a manual **Web Service**:
   - Runtime: **Docker**
   - Start command: `bash start.sh`
3. Add a MySQL database (Render's own MySQL isn't free — use Railway MySQL, PlanetScale,
   DigitalOcean, etc.) and set the `DB_*` and `APP_URL` env vars.
4. Load the schema into that database, then deploy.

## Payment (Paystack)

Set `PAYSTACK_SECRET_KEY` to a real key to accept payments. The checkout flow:

1. Cart → checkout creates a `pending` order.
2. Redirect to Paystack with the order total (free shipping over ₦15,000, otherwise +₦1,500).
3. Paystack calls back to `cart/verify_payment.php`, which verifies the transaction and
   marks the order `paid`.

## Project structure

```
admin/         admin panel (products, orders, status)
auth/          login / register / forgot + reset password
cart/          add / remove / cart / checkout / verify payment
wishlist/      add / remove / view
pages/         shop / product / quiz / about / contact / search
includes/      shared header, footer, auth guards
config/        config / database / security / paystack
database/      glowco_db.sql (schema + seed)
uploads/       product images
```

## Notes / limitations

- Product uploads store files on the local disk; on Railway/Render the disk is
  ephemeral between deploys — use a cloud storage bucket if images must persist
  across redeploys.
- The skin quiz ("View Product") links to the shop search for the recommended item.
- `includes/navbar.php` is legacy/unused — the header includes the nav.
