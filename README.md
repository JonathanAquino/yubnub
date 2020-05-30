yubnub
======

A social command line for the web! This is the source code for http://yubnub.org/.

Yubnub was written by Jonathan Aquino for the Rails Day 2005 programming
competition. He then ported it to PHP in 2013.

This source code is made available under the MIT License (see LICENSE).

Apologies for the poor quality of the HTML and CSS. They were written back when
I didn't know much about web design best practices, back in 2005. The PHP is
more recent (2013) and is of better quality.

Installation
------------

This is a simple website that consists of PHP files (beginning with public/index.php)
and a MySQL database.

After installing PHP and MySQL on your computer, create a MySQL database called "yubnub"
and use db/yubnub.sql to create the three tables.

Then copy config/SampleConfig.php to config/MyConfig.php (which is .gitignore'd), and edit
its username and password to match those for your yubnub database.

Apache Configuration
--------------------

This is what I have in my /etc/apache2/sites-available/yubnub.org file:

    <VirtualHost *:80>
      # Admin email, Server Name (domain name), and any aliases
      ServerAdmin webmaster@yubnub.org
      ServerName  www.yubnub.org
      ServerAlias yubnub.org

      # Index file and Document Root (where the public files are located)
      DocumentRoot /home/jon/public/yubnub.org/public

      # From http://framework.zend.com/wiki/display/ZFDEV/Configuring+Your+URL+Rewriter
      RewriteEngine off
      <Location />
          RewriteEngine on
          RedirectMatch 302 ^/$ https://yubnub.org/
          RewriteRule "well-known" "-" [L]
          RewriteCond %{REQUEST_FILENAME} !-f
          RewriteCond %{REQUEST_FILENAME} !-d
          RewriteRule !\.(js|ico|gif|jpg|png|css|html|xml|JPG|xls|doc|txt|reg|sh|sxc|sxw)$ /index.php
      </Location>

      # Log file locations
      LogLevel warn
    </VirtualHost>

Versions
--------

This code works with the following software versions:

* PHP 5.3.10
* PHPUnit 3.7.14