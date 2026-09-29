# DriveED Hub - Docker Quickstart

This application is fully containerized using Docker and Docker Compose, configured to run isolated from any other containers on your system.

## Port and Services

- **Web Application**: [`http://localhost:8004`](http://localhost:8004)
- **Container Name (App)**: `driveed_hub_app`
- **Container Name (Database)**: `driveed_hub_db`
- **Isolated Network**: `driveed_hub_net`
- **Database Port on Host**: `127.0.0.1:33064` (prevents collision with local MySQL or other containers using port 3306)

---

## Quick Commands

### 1. Build & Start Containers
```bash
docker compose up -d --build
```

### 2. View Logs
```bash
# Application logs
docker compose logs -f app

# Database logs
docker compose logs -f db
```

### 3. Run Migrations & Seeders
Migrations run automatically on container startup. If you want to manually run or re-seed:
```bash
# Run migrations
docker compose exec app php artisan migrate

# Seed database
docker compose exec app php artisan db:seed
```

### 4. Run Artisan Commands
```bash
docker compose exec app php artisan <command>
```

### 5. Stop Containers
```bash
# Stop containers (preserves database data)
docker compose down

# Stop and delete volumes (fresh start)
docker compose down -v
```
