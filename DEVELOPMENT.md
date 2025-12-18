# YubNub Local Development Guide

This guide explains how to run YubNub locally using Docker.

## Prerequisites

- [Docker](https://docs.docker.com/get-docker/)
- [Docker Compose](https://docs.docker.com/compose/install/)

## Quick Start

1. **Clone the repository** (if you haven't already):
   ```bash
   git clone https://github.com/JonathanAquino/yubnub.git
   cd yubnub
   ```

2. **Set up the configuration file**:
   ```bash
   cp config/DockerConfig.php config/MyConfig.php
   ```

3. **Start the Docker containers**:
   ```bash
   cd docker
   docker-compose up -d --build
   ```

4. **Install PHP dependencies** (for running tests):
   ```bash
   docker exec yubnub-dev composer install
   ```

5. **Access YubNub** at http://localhost:8080

## Services

| Service | Container Name | Port | Description |
|---------|---------------|------|-------------|
| YubNub  | yubnub-dev    | 8080 | PHP 8.3 + Apache |
| MySQL   | yubnub-mysql  | 3306 | MySQL 8.0 database |

## Running Tests

Run the PHPUnit test suite:

```bash
docker exec yubnub-dev vendor/bin/phpunit test/
```

Run a specific test file:

```bash
docker exec yubnub-dev vendor/bin/phpunit test/ParserTest.php
```

Run tests with verbose output:

```bash
docker exec yubnub-dev vendor/bin/phpunit test/ --verbose
```

## Common Commands

**View logs**:
```bash
docker logs yubnub-dev
docker logs yubnub-mysql
```

**Stop containers**:
```bash
cd docker
docker-compose down
```

**Stop and remove all data** (including database):
```bash
cd docker
docker-compose down -v
```

**Rebuild after Dockerfile changes**:
```bash
cd docker
docker-compose up -d --build
```

**Access the container shell**:
```bash
docker exec -it yubnub-dev bash
```

**Access MySQL**:
```bash
docker exec -it yubnub-mysql mysql -u yubnub -pyubnub_dev_password yubnub
```

## Database

The database is automatically initialized with the schema from `db/yubnub.sql` when the MySQL container starts for the first time.

To reset the database:
```bash
cd docker
docker-compose down -v
docker-compose up -d
```

## Configuration

The configuration file `config/MyConfig.php` is gitignored. For Docker development, copy the Docker config:

```bash
cp config/DockerConfig.php config/MyConfig.php
```

Key settings in `DockerConfig.php`:
- Database host: `mysql` (Docker service name)
- Database name: `yubnub`
- Database user: `yubnub`
- Database password: `yubnub_dev_password`
- CAPTCHA: Uses Cloudflare Turnstile test keys (always passes)

### Cloudflare Turnstile CAPTCHA

YubNub uses [Cloudflare Turnstile](https://developers.cloudflare.com/turnstile/) for CAPTCHA protection when creating new commands.

**For local development**, the `DockerConfig.php` includes test keys that always pass:
- Public key: `1x00000000000000000000AA`
- Private key: `1x0000000000000000000000000000000AA`

**For production**, you need real Turnstile keys:

1. Log in to the [Cloudflare Dashboard](https://dash.cloudflare.com/)
2. Go to **Turnstile** in the left sidebar
3. Click **Add site**
4. Enter your site name and domain (e.g., `yubnub.org`)
5. Select widget type (Managed is recommended)
6. Copy the **Site Key** (public) and **Secret Key** (private)
7. Add them to your `config/MyConfig.php`:
   ```php
   public function getCaptchaPublicKey() {
       return 'your-site-key-here';
   }

   public function getCaptchaPrivateKey() {
       return 'your-secret-key-here';
   }
   ```

See the [Turnstile documentation](https://developers.cloudflare.com/turnstile/get-started/) for more details.

## Project Structure

```
yubnub/
├── app/
│   ├── controllers/    # Request handlers
│   ├── helpers/        # Utility classes
│   ├── models/         # Data models
│   └── views/          # HTML templates
├── config/
│   ├── Config.php      # Configuration interface
│   ├── DockerConfig.php # Docker development config
│   ├── MyConfig.php    # Your local config (gitignored)
│   └── SampleConfig.php # Sample configuration
├── db/
│   └── yubnub.sql      # Database schema
├── docker/
│   ├── Dockerfile      # PHP 8.3 + Apache image
│   ├── apache.conf     # Apache virtual host config
│   └── docker-compose.yml
├── public/             # Web root (DocumentRoot)
│   └── index.php       # Entry point
├── test/               # PHPUnit tests
└── vendor/             # Composer dependencies (gitignored)
```

## Troubleshooting

**Port 8080 already in use**:
Edit `docker/docker-compose.yml` and change the port mapping (e.g., `8081:80`).

**Database connection refused**:
Wait a few seconds after starting containers. MySQL needs time to initialize.
Check if MySQL is healthy: `docker ps` (should show "healthy" status).

**Tests fail with "Class not found"**:
Make sure you've installed dependencies: `docker exec yubnub-dev composer install`

**Changes not appearing**:
The source code is mounted as a volume, so changes should appear immediately.
Try clearing your browser cache or using incognito mode.

## Software Versions

- PHP 8.3
- MySQL 8.0
- PHPUnit 9.x
- Apache 2.4
