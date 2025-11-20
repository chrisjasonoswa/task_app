# Task App Monorepo

This repository contains two parts:

1. **Frontend:** `task-app-frontend` (Nuxt.js)
2. **Backend:** `task-app-backend` (Laravel Sail)

This guide explains how to run both locally.

---

## Prerequisites

* Docker
* Node.js & pnpm

---

## Backend (Laravel Sail)

**1. Navigate to the backend folder:**
`cd task-app-backend`

**2. Install dependencies using Docker (no PHP/Composer needed locally):**

```
docker run --rm \
  -u "$(id -u):$(id -g)" \
  -v "$(pwd):/var/www/html" \
  -w /var/www/html \
  laravelsail/php84-composer:latest \
  composer install --ignore-platform-reqs
```

**3. Create Sail alias for convenience:**
`alias sail='[ -f sail ] && bash sail || bash vendor/bin/sail'`

**4. Set up environment:**
`cp .env.example .env`

**5. Start Sail containers:**
`sail up -d`

**6. Run migrations:**
`sail artisan migrate --seed`

**Backend URL:** [http://localhost](http://localhost)

---

## Frontend (Nuxt.js)

**1. Navigate to the frontend folder:**
`cd task-app-frontend`

**2. Install dependencies:**
`pnpm install`

**3. Set up environment:**
`cp .env.example .env`
*Note: Ensure that API_URL=http://localhost/api*

**4. Run Nuxt dev server:**
`pnpm dev`

**Frontend URL:** [http://localhost:3000](http://localhost:3000)

**Default user credentials:**
- **Email:** test@example.com
- **Password:** test1234
- **Login URL:** http://localhost:3000/login

---

> Make sure the backend is running before starting the frontend so API requests work.
> Stop Sail anytime with `./vendor/bin/sail down` or `sail down`.
