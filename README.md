# ExamForge

ExamForge is a Laravel and Filament platform for university students to unlock past-question courses, complete timed practice exams, and review their answers.

## Core workflow

- Students register with their department and academic level.
- Students pay through Paystack to unlock a course.
- Administrators can manually approve a payment that remains pending.
- Administrators import questions from Moodle-style TXT or structured DOCX files.
- Students configure and complete timed practice sessions.
- Answers save automatically and results include explanations.

## Local setup

Requirements: PHP, Composer, MySQL, Redis, and Node.js.

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Create the MySQL database, update the database credentials in `.env`, then run:

```bash
php artisan migrate --seed
php artisan exam:create-admin
php artisan filament:assets
npm install --ignore-scripts
npm run build
php artisan serve
```

Open `/admin` for administration or `/student` for the student portal. Add the institution's departments in the admin panel before allowing student registration.

## Redis

Local development and production can use Redis for cache, sessions, and queues:

```env
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

Use `REDIS_URL` when the hosting provider supplies one connection URL. A queue worker must be running in production.

## Paystack

Set the secret key in the production environment:

```env
PAYSTACK_SECRET_KEY=your_live_secret_key
PAYSTACK_BASE_URL=https://api.paystack.co
```

Configure the Paystack webhook to send `charge.success` events to:

```text
https://your-domain.example/webhooks/paystack
```

## Production checklist

- Set `APP_ENV=production`, `APP_DEBUG=false`, and the correct `APP_URL`.
- Attach MySQL and Redis services.
- Run `php artisan migrate --force` and `php artisan db:seed --force`.
- Run a queue worker.
- Run Laravel's scheduler every minute so stale failed jobs are pruned automatically.
- Configure the Paystack webhook and live secret key.
- Create the first administrator with `php artisan exam:create-admin`.
- Confirm `/up` returns a successful health response.
- Run `php artisan exam:check-production` and resolve every failed check.
- Schedule database backups and monitor failed jobs and application errors.

## Tests

```bash
php artisan test
```
