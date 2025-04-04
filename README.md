# 🛡️ Crime Management System API

A robust Laravel 12-based API for managing crime reporting and investigation workflows in District Core. Features role-based access, evidence management, queue workers, background jobs, public crime reporting, notifications, and full Docker support.


## 🚀 Features

- 🔐 Role-based access (Admin, Investigator, Officer, Citizen)
- 🗂 Case and evidence management
- 🧾 Crime reporting with report tracking
- 📬 Email alerts for citizens
- ⏳ Queue jobs with Supervisor
- 📊 Text analysis + link extraction from case descriptions
- 🔁 Redis & PostgreSQL integration
- 🐳 Fully Dockerized (Laravel + Supervisor + Redis + PostgreSQL)


## ⚙️ Requirements

- Docker + Docker Compose
- PHP 8.3 (inside Docker)
- Composer (handled via Docker)


## 📦 Quick Setup

### 1. Clone the repository

```bash
git clone https://github.com/hajricod/cityeye-api.git

cd cityeye-api
```

### 2. Create .env file

```bash
cp .env.example .env
```

#### Update the following in .env (or put it at the end of the file)

```
QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
REDIS_CLIENT=phpredis
REDIS_HOST=host.docker.internal
REDIS_PASSWORD=null
REDIS_PORT=6379

DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=cityeye_db
DB_USERNAME=cityeye
DB_PASSWORD=1958?aA
```

#### config mailtrap for awarnace notification in .env (or put it at the end of the file)

* sign up to http://mailtrap.io and use your username and password

```
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=setUpYourOwnUser
MAIL_PASSWORD=setUpYourOwnPass
MAIL_FROM_ADDRESS="admin@cityeye.com"
MAIL_FROM_NAME="${APP_NAME}"
MAIL_PASSMAIL_FROM_ADDRESS="admin@cityeye.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### 3. Build and run the containers

```bash
docker-compose build --no-cache

docker-compose up -d
```

### 4. Generate app key

```bash
docker-compose exec app php artisan key:generate
```

### 5. Run migrations & seeders

```bash
docker-compose exec app php artisan migrate --seed
```

#### This will create:

- Admin, investigator, officer users

- Cases, assignees, reports, evidence records

## 👨‍✈️ Accessing the App

- API Docs: http://localhost:8000/docs/api

- you can test most of the api endpoints from the docs


## 🧰 Useful Commands

### View application logs

```bash
docker-compose logs -f app
```

### View worker logs

```bash
docker-compose exec app tail -f storage/logs/worker.log
```

### Run queue worker manually

```bash
docker-compose exec app php artisan queue:work
```

## ⚙️ Supervisor Setup

Supervisor handles long-running tasks like queue:work.

It's configured via:

```text
/docker/supervisor/laravel-worker.conf
```

And launched via start.sh, which also runs Laravel’s dev server:

```bash
php artisan serve --host=0.0.0.0 --port=8000 &
supervisord -c /etc/supervisor/supervisord.conf
```

## 🧪 Testing Queue Jobs

You can test the queue by dispatching a job:
```bash
php artisan tinker

App\Jobs\TestJob::dispatch();
```

Then check storage/logs/laravel.log.

## 📂 Project Structure (Simplified)

```bash
/app
/config
/database
/routes
/docker
  └── start.sh
  └── supervisor/
       └── laravel-worker.conf
       └── supervisord.conf
```

## 📚 Documentation

- API Docs: /docs/api route (powered by Scramble)

- Auth: Basic Auth (email + password)


## 🧼 Cleanup

To stop and remove everything:

```bash
docker-compose down -v
```

## 👏 Credits

Developed by Hajricod

Built with ❤️ using Laravel 12, PostgreSQL, Redis, and Docker.

## 📜 License

This project is licensed under the MIT License.
