# Guide de Déploiement - ICHRI Tunisia

Guide complet pour déployer l'application en production.

## 📋 Prérequis

- Serveur Ubuntu 20.04+ / CentOS 8+
- Nom de domaine configuré
- Certificat SSL
- Minimum : 2 CPU, 4GB RAM, 40GB SSD

## 🚀 Déploiement Backend (Laravel)

### 1. Préparation Serveur

```bash
# Update système
sudo apt update && sudo apt upgrade -y

# Install PHP 8.2
sudo add-apt-repository ppa:ondrej/php
sudo apt install php8.2 php8.2-fpm php8.2-mysql php8.2-redis \
  php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-gd -y

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install MySQL
sudo apt install mysql-server -y
sudo mysql_secure_installation

# Install Redis
sudo apt install redis-server -y

# Install Nginx
sudo apt install nginx -y
```

### 2. Configuration MySQL

```sql
CREATE DATABASE ichri_tunisia;
CREATE USER 'ichri_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON ichri_tunisia.* TO 'ichri_user'@'localhost';
FLUSH PRIVILEGES;
```

### 3. Déploiement Application

```bash
# Clone repository
cd /var/www
git clone https://github.com/haythemsaa/jumia.git
cd jumia/backend

# Install dependencies
composer install --no-dev --optimize-autoloader

# Configuration
cp .env.example .env
nano .env  # Edit configuration

php artisan key:generate
php artisan migrate --force
php artisan db:seed --force

# Permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Cache optimization
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 4. Configuration Nginx

```nginx
server {
    listen 80;
    server_name api.ichri.tn;
    root /var/www/jumia/backend/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 5. Queue Workers (Supervisor)

```bash
sudo apt install supervisor -y

# Create config
sudo nano /etc/supervisor/conf.d/ichri-worker.conf
```

```ini
[program:ichri-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/jumia/backend/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/jumia/backend/storage/logs/worker.log
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start ichri-worker:*
```

### 6. Cron Jobs

```bash
sudo crontab -e
```

Add:
```
* * * * * cd /var/www/jumia/backend && php artisan schedule:run >> /dev/null 2>&1
```

### 7. SSL Certificate

```bash
sudo apt install certbot python3-certbot-nginx -y
sudo certbot --nginx -d api.ichri.tn
```

## 🌐 Déploiement Frontend

### 1. Build & Optimization

```bash
cd /var/www/jumia/frontend

# Minify CSS
npm install -g minify
minify css/style.css > css/style.min.css

# Optimize images
sudo apt install optipng jpegoptim -y
optipng images/*.png
jpegoptim images/*.jpg
```

### 2. Configuration Nginx

```nginx
server {
    listen 80;
    server_name ichri.tn www.ichri.tn;
    root /var/www/jumia/frontend;
    index index.html;

    location / {
        try_files $uri $uri/ /index.html;
    }

    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml;
}
```

### 3. SSL

```bash
sudo certbot --nginx -d ichri.tn -d www.ichri.tn
```

## 📱 Déploiement Mobile

### Android (Google Play)

```bash
cd mobile

# Create keystore
keytool -genkey -v -keystore android/app/ichri-release-key.keystore \
  -alias ichri -keyalg RSA -keysize 2048 -validity 10000

# Build release APK
cd android
./gradlew assembleRelease

# APK location
# android/app/build/outputs/apk/release/app-release.apk
```

Upload sur Google Play Console.

### iOS (App Store)

1. Ouvrir dans Xcode
```bash
cd mobile/ios
open ICHRITunisia.xcworkspace
```

2. Product > Archive
3. Distribute App
4. Upload vers App Store Connect

## 🔒 Sécurité Post-Déploiement

### Firewall

```bash
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

### Fail2Ban

```bash
sudo apt install fail2ban -y
sudo systemctl enable fail2ban
```

### Monitoring

```bash
# Install monitoring tools
sudo apt install htop iotop nethogs -y
```

## 📊 Maintenance

### Backup Base de Données

```bash
# Daily backup cron
0 2 * * * mysqldump -u ichri_user -p'password' ichri_tunisia > /backups/db_$(date +\%Y\%m\%d).sql
```

### Logs Rotation

```bash
sudo nano /etc/logrotate.d/ichri
```

```
/var/www/jumia/backend/storage/logs/*.log {
    daily
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
}
```

### Updates

```bash
cd /var/www/jumia
git pull origin main
cd backend
composer install --no-dev
php artisan migrate --force
php artisan cache:clear
php artisan config:cache
php artisan route:cache
sudo supervisorctl restart ichri-worker:*
```

## 🚨 Troubleshooting

### Permission Issues
```bash
sudo chown -R www-data:www-data /var/www/jumia/backend
sudo chmod -R 755 /var/www/jumia/backend/storage
```

### Queue Not Working
```bash
sudo supervisorctl status
sudo supervisorctl restart ichri-worker:*
```

### High Server Load
```bash
php artisan queue:restart
php artisan cache:clear
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm
```

## ✅ Checklist Final

- [ ] Base de données configurée
- [ ] Application déployée
- [ ] Nginx configuré
- [ ] SSL activé
- [ ] Queue workers actifs
- [ ] Cron jobs configurés
- [ ] Firewall activé
- [ ] Backups automatiques
- [ ] Monitoring en place
- [ ] Tests de charge effectués

---

Application prête pour production ! 🚀
