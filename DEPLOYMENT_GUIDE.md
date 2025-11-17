# ICHRI Tunisia - Deployment Guide

## 📋 Table of Contents
- [Prerequisites](#prerequisites)
- [Server Requirements](#server-requirements)
- [Installation Steps](#installation-steps)
- [Environment Configuration](#environment-configuration)
- [Database Setup](#database-setup)
- [Queue Workers](#queue-workers)
- [Cron Jobs](#cron-jobs)
- [SSL Certificate](#ssl-certificate)
- [Performance Optimization](#performance-optimization)
- [Monitoring](#monitoring)
- [Backup Strategy](#backup-strategy)
- [Troubleshooting](#troubleshooting)

## Prerequisites

### Required Software
- PHP 8.2 or higher
- Composer 2.x
- MySQL 8.0 or PostgreSQL 14+
- Redis 6.x or higher
- Nginx or Apache 2.4
- Node.js 18+ (for asset compilation)
- Supervisor (for queue workers)

### Domain & SSL
- Domain name (e.g., api.ichri.tn)
- SSL certificate (Let's Encrypt recommended)

## Server Requirements

### Minimum Specifications
- **CPU:** 2 cores
- **RAM:** 4GB
- **Storage:** 40GB SSD
- **Bandwidth:** 100 Mbps

### Recommended Specifications (Production)
- **CPU:** 4+ cores
- **RAM:** 8GB+
- **Storage:** 100GB+ SSD
- **Bandwidth:** 1 Gbps

### PHP Extensions Required
```bash
php8.2-cli
php8.2-fpm
php8.2-mysql
php8.2-pgsql
php8.2-redis
php8.2-mbstring
php8.2-xml
php8.2-curl
php8.2-zip
php8.2-gd
php8.2-intl
php8.2-bcmath
```

## Installation Steps

### 1. Clone Repository
```bash
cd /var/www
git clone https://github.com/your-repo/ichri-backend.git
cd ichri-backend/backend
```

### 2. Install Dependencies
```bash
composer install --no-dev --optimize-autoloader
```

### 3. Set Permissions
```bash
chown -R www-data:www-data /var/www/ichri-backend
chmod -R 775 storage bootstrap/cache
```

### 4. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

### 5. Configure Environment
Edit `.env` file with production values (see Environment Configuration below)

### 6. Run Migrations
```bash
php artisan migrate --force
php artisan db:seed --class=LoyaltySeeder --force
```

### 7. Create Storage Link
```bash
php artisan storage:link
```

### 8. Optimize Application
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

## Environment Configuration

### Essential .env Variables

```bash
# Application
APP_NAME="ICHRI Tunisia"
APP_ENV=production
APP_KEY=base64:GENERATED_KEY
APP_DEBUG=false
APP_URL=https://api.ichri.tn
APP_TIMEZONE=Africa/Tunis

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ichri_production
DB_USERNAME=ichri_user
DB_PASSWORD=SECURE_PASSWORD

# Cache & Session
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=REDIS_PASSWORD
REDIS_PORT=6379

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=noreply@ichri.tn
MAIL_PASSWORD=MAIL_PASSWORD
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@ichri.tn
MAIL_FROM_NAME="${APP_NAME}"

# Payment Gateways
EDINAR_MERCHANT_ID=your_merchant_id
EDINAR_SECRET_KEY=your_secret_key
EDINAR_API_URL=https://payment.edinar.tn/api

KONNECT_API_KEY=your_api_key
KONNECT_WALLET_ID=your_wallet_id
KONNECT_API_URL=https://api.konnect.network

D17_MERCHANT_ID=your_merchant_id
D17_API_KEY=your_api_key
D17_API_URL=https://api.d17.tn

# SMS Provider
SMS_PROVIDER=tunisiesms
SMS_API_KEY=your_api_key
SMS_SENDER_NAME=ICHRI

# Firebase Cloud Messaging
FCM_SERVER_KEY=your_fcm_server_key

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=error
LOG_SLACK_WEBHOOK_URL=https://hooks.slack.com/services/YOUR/WEBHOOK/URL

# Performance
CACHE_PREFIX=ichri_prod_
REDIS_CACHE_DB=1
```

## Database Setup

### Create Database
```sql
CREATE DATABASE ichri_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE USER 'ichri_user'@'localhost' IDENTIFIED BY 'SECURE_PASSWORD';
GRANT ALL PRIVILEGES ON ichri_production.* TO 'ichri_user'@'localhost';
FLUSH PRIVILEGES;
```

### Optimize MySQL
Add to `/etc/mysql/mysql.conf.d/mysqld.cnf`:
```ini
[mysqld]
innodb_buffer_pool_size = 2G
innodb_log_file_size = 512M
max_connections = 200
query_cache_size = 128M
query_cache_limit = 2M
```

### Database Backups
```bash
# Daily backup script
#!/bin/bash
BACKUP_DIR="/var/backups/ichri"
DATE=$(date +%Y%m%d_%H%M%S)

mysqldump -u ichri_user -p'SECURE_PASSWORD' ichri_production | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Keep only last 30 days
find $BACKUP_DIR -name "db_*.sql.gz" -mtime +30 -delete
```

## Queue Workers

### Configure Supervisor

Create `/etc/supervisor/conf.d/ichri-workers.conf`:

```ini
[program:ichri-queue-default]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/ichri-backend/backend/artisan queue:work redis --queue=default --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/ichri-backend/backend/storage/logs/queue-default.log
stopwaitsecs=3600

[program:ichri-queue-emails]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/ichri-backend/backend/artisan queue:work redis --queue=emails --sleep=3 --tries=3
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/ichri-backend/backend/storage/logs/queue-emails.log

[program:ichri-queue-notifications]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/ichri-backend/backend/artisan queue:work redis --queue=notifications --sleep=3 --tries=3
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/ichri-backend/backend/storage/logs/queue-notifications.log

[program:ichri-queue-payments]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/ichri-backend/backend/artisan queue:work redis --queue=payments --sleep=3 --tries=5
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/ichri-backend/backend/storage/logs/queue-payments.log
```

### Start Workers
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start ichri-queue-default:*
sudo supervisorctl start ichri-queue-emails:*
sudo supervisorctl start ichri-queue-notifications:*
sudo supervisorctl start ichri-queue-payments:*
```

## Cron Jobs

Add to `/etc/cron.d/ichri`:

```bash
# Laravel Scheduler
* * * * * www-data cd /var/www/ichri-backend/backend && php artisan schedule:run >> /dev/null 2>&1

# Clear expired cache daily at 2 AM
0 2 * * * www-data cd /var/www/ichri-backend/backend && php artisan cache:prune-stale-tags

# Generate reports daily at 3 AM
0 3 * * * www-data cd /var/www/ichri-backend/backend && php artisan reports:generate

# Database backup daily at 4 AM
0 4 * * * root /var/backups/ichri/backup-db.sh

# Cleanup old logs weekly
0 0 * * 0 www-data cd /var/www/ichri-backend/backend && php artisan log:clear --days=30
```

## Nginx Configuration

Create `/etc/nginx/sites-available/ichri-api`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name api.ichri.tn;

    # Redirect to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name api.ichri.tn;

    root /var/www/ichri-backend/backend/public;
    index index.php;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/api.ichri.tn/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/api.ichri.tn/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    # Logging
    access_log /var/log/nginx/ichri-api-access.log;
    error_log /var/log/nginx/ichri-api-error.log;

    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss application/json;

    # Rate Limiting
    limit_req_zone $binary_remote_addr zone=api:10m rate=60r/m;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache static files
    location ~* \.(jpg|jpeg|gif|png|css|js|ico|xml)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    # Deny access to sensitive files
    location ~ /\.env {
        deny all;
    }
}
```

Enable site:
```bash
ln -s /etc/nginx/sites-available/ichri-api /etc/nginx/sites-enabled/
nginx -t
systemctl reload nginx
```

## SSL Certificate

### Using Let's Encrypt
```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d api.ichri.tn
sudo certbot renew --dry-run
```

## Performance Optimization

### OPcache Configuration
Edit `/etc/php/8.2/fpm/conf.d/10-opcache.ini`:

```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.revalidate_freq=60
opcache.fast_shutdown=1
```

### PHP-FPM Tuning
Edit `/etc/php/8.2/fpm/pool.d/www.conf`:

```ini
pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
pm.max_requests = 500
```

### Redis Configuration
Edit `/etc/redis/redis.conf`:

```ini
maxmemory 2gb
maxmemory-policy allkeys-lru
save ""
appendonly yes
```

## Monitoring

### Health Check Endpoint
```bash
curl https://api.ichri.tn/up
```

### System Monitoring Script
Create `/usr/local/bin/ichri-monitor.sh`:

```bash
#!/bin/bash

# Check application status
curl -f https://api.ichri.tn/up || echo "API is down!"

# Check queue workers
supervisorctl status | grep ichri-queue | grep -v RUNNING && echo "Queue workers down!"

# Check disk space
df -h | grep -E '9[0-9]%|100%' && echo "Disk space critical!"

# Check Redis
redis-cli ping || echo "Redis is down!"

# Check MySQL
mysqladmin -u root -p ping || echo "MySQL is down!"
```

### Set up monitoring cron:
```bash
*/5 * * * * /usr/local/bin/ichri-monitor.sh | mail -s "ICHRI Monitoring Alert" admin@ichri.tn
```

## Backup Strategy

### Full System Backup Script
Create `/var/backups/ichri/full-backup.sh`:

```bash
#!/bin/bash
BACKUP_DIR="/var/backups/ichri"
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="$BACKUP_DIR/full_backup_$DATE.tar.gz"

# Database backup
mysqldump -u ichri_user -p'SECURE_PASSWORD' ichri_production > /tmp/db.sql

# Create archive
tar -czf $BACKUP_FILE \
    /var/www/ichri-backend \
    /tmp/db.sql \
    /etc/nginx/sites-available/ichri-api \
    /etc/supervisor/conf.d/ichri-workers.conf

# Cleanup
rm /tmp/db.sql

# Upload to S3 (optional)
aws s3 cp $BACKUP_FILE s3://ichri-backups/

# Keep only last 7 full backups
find $BACKUP_DIR -name "full_backup_*.tar.gz" -mtime +7 -delete

echo "Backup completed: $BACKUP_FILE"
```

## Troubleshooting

### Clear All Caches
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
redis-cli FLUSHALL
```

### Queue Worker Issues
```bash
# Restart all queue workers
supervisorctl restart ichri-queue-default:*
supervisorctl restart ichri-queue-emails:*

# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

### Permission Issues
```bash
chown -R www-data:www-data /var/www/ichri-backend
chmod -R 775 storage bootstrap/cache
```

### Performance Issues
```bash
# Check slow queries
mysql -u root -p -e "SELECT * FROM mysql.slow_log ORDER BY start_time DESC LIMIT 10;"

# Monitor Redis memory
redis-cli info memory

# Check PHP-FPM status
curl http://localhost/fpm-status
```

### Database Migration Issues
```bash
# Rollback last migration
php artisan migrate:rollback --step=1

# Fresh migration (CAUTION: Deletes all data)
php artisan migrate:fresh --seed
```

## Production Checklist

- [ ] All .env variables configured
- [ ] APP_DEBUG=false
- [ ] SSL certificate installed
- [ ] Database backups scheduled
- [ ] Queue workers running
- [ ] Cron jobs configured
- [ ] Monitoring set up
- [ ] Firewall configured
- [ ] Logs rotation configured
- [ ] Payment gateways tested
- [ ] Email sending tested
- [ ] SMS sending tested
- [ ] Push notifications tested
- [ ] Rate limiting verified
- [ ] API documentation accessible
- [ ] Error tracking configured
- [ ] Performance optimizations applied

## Security Recommendations

1. **Firewall**: Only allow ports 80, 443, 22
2. **Fail2Ban**: Protect against brute force attacks
3. **Regular Updates**: Keep all packages updated
4. **Key Rotation**: Rotate API keys every 90 days
5. **Audit Logs**: Review security logs weekly
6. **Penetration Testing**: Conduct quarterly security audits
7. **Backup Testing**: Test backup restoration monthly

## Support

For deployment support, contact: devops@ichri.tn
