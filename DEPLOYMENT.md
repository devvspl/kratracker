# Deployment Checklist

## Pre-Deployment

### 1. Environment Configuration
- [ ] Copy `.env.example` to `.env`
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Generate new `APP_KEY`: `php artisan key:generate`
- [ ] Configure database credentials
- [ ] Set up mail configuration for notifications
- [ ] Configure queue driver (database/redis)

### 2. Database Setup
```bash
# For MySQL
php artisan migrate --force
php artisan db:seed --force

# Or fresh install
php artisan migrate:fresh --seed --force
```

### 3. Permissions & Storage
```bash
# Set proper permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Create storage link
php artisan storage:link
```

### 4. Optimize Application
```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev
```

### 5. Build Assets
```bash
npm install
npm run build
```

## Production Environment Variables

```env
APP_NAME="Performia"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database (MySQL recommended for production)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kra_tracker
DB_USERNAME=your_db_user
DB_PASSWORD=your_secure_password

# Queue (Use redis or database for production)
QUEUE_CONNECTION=database

# Cache (Use redis for production)
CACHE_STORE=redis

# Session
SESSION_DRIVER=database
SESSION_LIFETIME=120

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.your-provider.com
MAIL_PORT=587
MAIL_USERNAME=your_email@domain.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"
```

## Server Requirements

### Minimum Requirements
- PHP >= 8.2
- MySQL 8.0+ or PostgreSQL 13+
- Composer
- Node.js 18+ & NPM
- Web server (Apache/Nginx)

### PHP Extensions
- BCMath
- Ctype
- Fileinfo
- JSON
- Mbstring
- OpenSSL
- PDO
- Tokenizer
- XML
- GD or Imagick (for PDF generation)

## Web Server Configuration

### Apache (.htaccess)
Already included in `public/.htaccess`

### Nginx
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/kra-tracker/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## Post-Deployment

### 1. Create Admin User
```bash
php artisan tinker
```
```php
$user = User::create([
    'name' => 'Admin',
    'email' => 'admin@your-domain.com',
    'password' => bcrypt('secure-password')
]);
$user->assignRole('Admin');
```

### 2. Set Up Scheduled Tasks
Add to crontab:
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### 3. Configure Queue Worker
For systemd:
```bash
sudo nano /etc/systemd/system/kra-queue.service
```

```ini
[Unit]
Description=Performia Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/kra-tracker/artisan queue:work --sleep=3 --tries=3

[Install]
WantedBy=multi-user.target
```

Enable and start:
```bash
sudo systemctl enable kra-queue
sudo systemctl start kra-queue
```

### 4. Set Up Backups
```bash
# Database backup script
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u username -p password kra_tracker > /backups/kra_tracker_$DATE.sql
```

### 5. Monitor Logs
```bash
# Application logs
tail -f storage/logs/laravel.log

# Web server logs
tail -f /var/log/nginx/error.log
```

## Security Checklist

- [ ] SSL certificate installed (Let's Encrypt recommended)
- [ ] Force HTTPS in `.env`: `APP_URL=https://your-domain.com`
- [ ] Firewall configured (UFW/iptables)
- [ ] Database user has minimal required permissions
- [ ] `.env` file is not accessible via web
- [ ] `storage` and `bootstrap/cache` are not web-accessible
- [ ] Regular security updates applied
- [ ] Strong passwords for all accounts
- [ ] Rate limiting enabled (Laravel default)
- [ ] CSRF protection enabled (Laravel default)

## Maintenance Mode

### Enable Maintenance Mode
```bash
php artisan down --secret="your-secret-token"
```
Access via: `https://your-domain.com/your-secret-token`

### Disable Maintenance Mode
```bash
php artisan up
```

## Troubleshooting

### Clear All Caches
```bash
php artisan optimize:clear
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Permission Issues
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### Database Connection Issues
- Check database credentials in `.env`
- Verify database server is running
- Check firewall rules
- Test connection: `php artisan tinker` then `DB::connection()->getPdo();`

### 500 Error
- Check `storage/logs/laravel.log`
- Verify file permissions
- Check `.env` configuration
- Run `php artisan config:cache`

## Performance Optimization

### 1. Enable OPcache
In `php.ini`:
```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
```

### 2. Use Redis for Cache & Sessions
```env
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

### 3. Database Indexing
Already included in migrations, but verify:
```sql
SHOW INDEX FROM work_logs;
```

### 4. CDN for Assets
Consider using CDN for static assets in production.

## Monitoring

### Application Monitoring
- Set up Laravel Telescope (development)
- Use Laravel Horizon for queue monitoring
- Configure error tracking (Sentry, Bugsnag)

### Server Monitoring
- CPU and memory usage
- Disk space
- Database performance
- Queue job processing

## Backup Strategy

### Daily Backups
- Database dump
- `.env` file
- Uploaded files in `storage/app`

### Weekly Backups
- Full application code
- Configuration files

### Monthly Backups
- Archive old logs
- Clean up old backups

## Update Procedure

```bash
# 1. Enable maintenance mode
php artisan down

# 2. Pull latest code
git pull origin main

# 3. Update dependencies
composer install --no-dev --optimize-autoloader
npm install && npm run build

# 4. Run migrations
php artisan migrate --force

# 5. Clear and cache
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Restart queue workers
sudo systemctl restart kra-queue

# 7. Disable maintenance mode
php artisan up
```

## Support

For deployment issues:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Check web server logs
3. Verify all environment variables
4. Test database connection
5. Verify file permissions

---

**Ready for Production! 🚀**
