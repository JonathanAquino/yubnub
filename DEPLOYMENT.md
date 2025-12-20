# YubNub Production Deployment Guide

This guide covers deploying YubNub to a production server running Ubuntu 24.04 LTS.

## Server Requirements

### Minimum Specifications
- **OS**: Ubuntu 24.04 LTS (or similar)
- **CPU**: 1 core
- **RAM**: 1 GB
- **Storage**: 25 GB
- **Network**: Public IPv4 address

### Software Stack
- **Web Server**: Apache 2.4+
- **PHP**: 8.1+ (tested with PHP 8.3)
- **Database**: MySQL 8.0+ or MariaDB 10.6+
- **SSL**: LetsEncrypt/Certbot

## Initial Server Setup

### 1. Create Non-Root User

```bash
# SSH as root
ssh root@your-server-ip

# Create user
adduser jon
usermod -aG sudo jon

# Set up SSH key authentication
mkdir -p /home/jon/.ssh
cp /root/.ssh/authorized_keys /home/jon/.ssh/
chown -R jon:jon /home/jon/.ssh
chmod 700 /home/jon/.ssh
chmod 600 /home/jon/.ssh/authorized_keys

# Test SSH as new user
exit
ssh jon@your-server-ip
```

### 2. Secure SSH

```bash
sudo vim /etc/ssh/sshd_config
```

Set these values:
```
PermitRootLogin no
PasswordAuthentication no
PubkeyAuthentication yes
```

Restart SSH:
```bash
sudo systemctl restart sshd
```

### 3. Configure Firewall

```bash
# Install UFW if not present
sudo apt update
sudo apt install -y ufw

# Configure firewall
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow http
sudo ufw allow https
sudo ufw enable
```

## Software Installation

### 1. Install Apache

```bash
sudo apt update
sudo apt install -y apache2

# Enable required modules
sudo a2enmod rewrite
sudo a2enmod ssl
sudo systemctl restart apache2
```

### 2. Install PHP

```bash
sudo apt install -y php8.3 php8.3-mysql php8.3-xml php8.3-mbstring php8.3-curl
```

### 3. Install MySQL

```bash
sudo apt install -y mysql-server

# Secure MySQL installation
sudo mysql_secure_installation
```

Answer the prompts:
- Set root password: Yes (choose a strong password)
- Remove anonymous users: Yes
- Disallow root login remotely: Yes
- Remove test database: Yes
- Reload privilege tables: Yes

### 4. Install Certbot (SSL)

```bash
sudo apt install -y certbot python3-certbot-apache
```

## Database Setup

### 1. Create Database and User

```bash
sudo mysql -u root -p
```

In MySQL:
```sql
CREATE DATABASE yubnub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'yubnub'@'localhost' IDENTIFIED BY 'your-secure-password';
GRANT ALL PRIVILEGES ON yubnub.* TO 'yubnub'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 2. Verify MySQL Configuration

Ensure MySQL only listens on localhost:

```bash
sudo vim /etc/mysql/mysql.conf.d/mysqld.cnf
```

Verify this line exists:
```
bind-address = 127.0.0.1
```

Restart MySQL:
```bash
sudo systemctl restart mysql
```

## Application Deployment

### 1. Clone Repository

```bash
sudo mkdir -p /var/www
cd /var/www
sudo git clone https://github.com/JonathanAquino/yubnub yubnub.org
```

### 2. Configure Application

```bash
cd /var/www/yubnub.org
sudo cp config/SampleConfig.php config/MyConfig.php
sudo vim config/MyConfig.php
```

Update these values in `MyConfig.php`:
```php
public function createPdo() {
    return new PDO('mysql:host=localhost;dbname=yubnub;charset=utf8mb4',
            'yubnub',
            'your-secure-password',  // Match password from database setup
            array(
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
}

public function getCaptchaPublicKey() {
    return 'your-turnstile-public-key';  // Get from Cloudflare Turnstile
}

public function getCaptchaPrivateKey() {
    return 'your-turnstile-private-key';  // Get from Cloudflare Turnstile
}
```

**Note**: Get free Turnstile CAPTCHA keys from https://dash.cloudflare.com/sign-up/turnstile

### 3. Set File Permissions

```bash
sudo chown -R www-data:www-data /var/www/yubnub.org
sudo chmod -R 755 /var/www/yubnub.org
sudo chmod 640 /var/www/yubnub.org/config/MyConfig.php
```

### 4. Import Database

If migrating from an existing installation:
```bash
# Transfer dump file to server
scp /path/to/yubnub-dump.sql jon@your-server-ip:/tmp/

# Import
mysql -u yubnub -p yubnub < /tmp/yubnub-dump.sql
```

For a fresh installation, the database will be populated when users create commands.

### 5. Handle MySQL Strict Mode (if migrating old data)

If you see errors about invalid dates (`0000-00-00 00:00:00`):

```bash
mysql -u yubnub -p yubnub
```

```sql
SET SESSION sql_mode = '';
SOURCE /tmp/yubnub-dump.sql;
EXIT;
```

### 6. Convert to InnoDB (recommended)

```bash
mysql -u yubnub -p yubnub
```

```sql
SET SESSION sql_mode = '';
ALTER TABLE commands ENGINE=InnoDB;
ALTER TABLE banned_url_patterns ENGINE=InnoDB;
ALTER TABLE sessions ENGINE=InnoDB;
EXIT;
```

## Apache Configuration

### 1. Create Virtual Host

```bash
sudo vim /etc/apache2/sites-available/yubnub.org.conf
```

Add this configuration:
```apache
<VirtualHost *:80>
    ServerName yubnub.org
    ServerAlias www.yubnub.org
    DocumentRoot /var/www/yubnub.org/public

    <Directory /var/www/yubnub.org/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted

        RewriteEngine on
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule !\.(js|ico|gif|jpg|png|css|html|xml|JPG|xls|doc|txt|reg|sh|sxc|sxw)$ /index.php
    </Directory>

    ErrorLog /dev/null
    CustomLog /dev/null combined
</VirtualHost>
```

### 2. Enable Site

```bash
sudo a2ensite yubnub.org
sudo systemctl reload apache2
```

### 3. Test HTTP Access

```bash
curl -I http://your-server-ip
```

Should return HTTP 200 OK.

## SSL Setup

### 1. Point DNS to Server

Before running certbot, ensure your domain's DNS A record points to your server's IP address.

```bash
dig yubnub.org +short
# Should show your server IP
```

### 2. Obtain SSL Certificate

```bash
sudo certbot --apache -d yubnub.org -d www.yubnub.org
```

Prompts:
- Email: your-email@example.com (for renewal notifications)
- Terms of Service: Agree (Y)
- Share email with EFF: Your choice (Y/N)

Certbot will:
- Obtain certificate from LetsEncrypt
- Create HTTPS virtual host automatically
- Set up HTTP → HTTPS redirects
- Configure auto-renewal via systemd timer

### 3. Fix HTTP→HTTPS Redirect (if needed)

If HTTP doesn't redirect to HTTPS, edit the HTTP virtual host:

```bash
sudo vim /etc/apache2/sites-available/yubnub.org.conf
```

Add `RewriteEngine on` before the redirect rules at the VirtualHost level:
```apache
    ErrorLog /dev/null
    CustomLog /dev/null combined
RewriteEngine on
RewriteCond %{SERVER_NAME} =yubnub.org [OR]
RewriteCond %{SERVER_NAME} =www.yubnub.org
RewriteRule ^ https://%{SERVER_NAME}%{REQUEST_URI} [END,NE,R=permanent]
</VirtualHost>
```

Reload Apache:
```bash
sudo systemctl reload apache2
```

### 4. Verify SSL

```bash
curl -I https://yubnub.org
# Should return HTTP/2 200 with valid SSL
```

Test certificate auto-renewal:
```bash
sudo certbot renew --dry-run
```

## Verification

### Test Core Functionality

1. **Homepage**: https://yubnub.org
2. **Search command**: Enter `g test` in search box
3. **List commands**: https://yubnub.org/kernel/ls
4. **Create command**: https://yubnub.org/kernel/man?args=create
   - Verify CAPTCHA widget loads
5. **Direct API**: https://yubnub.org/parser/parse?command=g+test
   - Should redirect to Google search

### Check System Status

```bash
# Apache status
sudo systemctl status apache2

# MySQL status
sudo systemctl status mysql

# Disk usage
df -h

# Memory usage
free -h

# Check open ports
sudo ufw status
```

## Ongoing Maintenance

### Pull Code Updates

```bash
ssh jon@your-server-ip
cd /var/www/yubnub.org
sudo git pull
```

### Database Backups

Create backup script:
```bash
vim ~/backup-yubnub.sh
```

```bash
#!/bin/bash
DATE=$(date +%Y-%m-%d)
mysqldump -u yubnub -pyour-password yubnub | gzip > ~/backups/yubnub-$DATE.sql.gz
# Keep only last 30 days
find ~/backups/ -name "yubnub-*.sql.gz" -mtime +30 -delete
```

Make executable and add to cron:
```bash
chmod +x ~/backup-yubnub.sh
mkdir -p ~/backups
crontab -e
```

Add daily backup at 2 AM:
```
0 2 * * * /home/jon/backup-yubnub.sh
```

### Monitor Certificate Renewal

SSL certificates auto-renew via systemd timer. Check status:
```bash
systemctl status certbot.timer
```

Certificates expire in 90 days. Certbot runs twice daily and renews certificates within 30 days of expiration.

### System Updates

Enable automatic security updates:
```bash
sudo apt install -y unattended-upgrades
sudo dpkg-reconfigure -plow unattended-upgrades
```

Manual updates:
```bash
sudo apt update
sudo apt upgrade -y
sudo systemctl restart apache2
sudo systemctl restart mysql
```

## Troubleshooting

### Apache Won't Start

```bash
# Check configuration
sudo apache2ctl configtest

# Check error logs
sudo tail -f /var/log/apache2/error.log
```

### MySQL Connection Errors

```bash
# Verify MySQL is running
sudo systemctl status mysql

# Test connection
mysql -u yubnub -p yubnub

# Check bind address
sudo netstat -tlnp | grep mysql
# Should show 127.0.0.1:3306
```

### PHP Errors

Enable error display temporarily in `config/MyConfig.php`:
```php
public function shouldDisplayErrors() {
    return true;  // Set to false in production after debugging
}
```

### Git Permission Errors

If you see "dubious ownership" errors when pulling:
```bash
sudo git config --global --add safe.directory /var/www/yubnub.org
```

## Security Best Practices

1. **Firewall**: Only ports 22, 80, 443 open
2. **SSH**: Key-based authentication only, no passwords
3. **MySQL**: Bound to localhost only, not exposed to internet
4. **Passwords**: Use strong, unique passwords for MySQL
5. **Updates**: Enable unattended security updates
6. **Backups**: Automated daily database backups
7. **Monitoring**: Consider UptimeRobot or similar for uptime alerts
8. **Config Files**: Never commit `MyConfig.php` to git (already in .gitignore)

## Additional Resources

- **Linode Guides**: https://www.linode.com/docs/guides/
- **LetsEncrypt**: https://letsencrypt.org/docs/
- **Apache Documentation**: https://httpd.apache.org/docs/2.4/
- **Cloudflare Turnstile**: https://www.cloudflare.com/products/turnstile/
