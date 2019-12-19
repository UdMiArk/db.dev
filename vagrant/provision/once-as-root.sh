#!/usr/bin/env bash

source /app/vagrant/provision/common.sh

#== Import script args ==

timezone=$(echo "$1")
remote_host=$(echo "$2")
xdebug_idekey=$(echo "$3")

#== Provision script ==

info_str "Provision-script user: $(whoami)"

export DEBIAN_FRONTEND=noninteractive

info_str "Configure timezone"
timedatectl set-timezone "${timezone}" --no-ask-password

info_str "Prepare root password for MySQL"
debconf-set-selections <<<"mysql-community-server mysql-community-server/root-pass password \"''\""
debconf-set-selections <<<"mysql-community-server mysql-community-server/re-root-pass password \"''\""
echo "Done!"

info_str "Update OS software"
apt-get update
apt-get upgrade -y
apt-get install software-properties-common

info_str "Install MySQL repository"
curl -OL https://dev.mysql.com/get/mysql-apt-config_0.8.14-1_all.deb
echo mysql-apt-config mysql-apt-config/select-server select mysql-8.0 | sudo debconf-set-selections
dpkg -i mysql-apt-config*
rm mysql-apt-config*

info_str "Install PHP repository"
add-apt-repository ppa:ondrej/php -y

apt-get update
apt-get upgrade -y

echo mysql-community-server mysql-server/default-auth-override select "Use Legacy Authentication Method (Retain MySQL 5.x Compatibility)" | sudo debconf-set-selections
echo mysql-community-server mysql-community-server/root-pass password '' | sudo debconf-set-selections
echo mysql-community-server mysql-community-server/re-root-pass password '' | sudo debconf-set-selections

info_str "Install additional software"
apt-get install -y bash-completion debconf-utils
apt-get install -y php7.3-fpm php7.3-cli php7.3-curl php7.3-intl php7.3-mysqlnd php7.3-gd php7.3-mbstring php7.3-xml php7.3-ldap unzip nginx mysql-server php.xdebug
apt-get autoremove -y

info_str "Configure MySQL"
# sed -i "s/.*bind-address.*/bind-address = 0.0.0.0/" /etc/mysql/mysql.conf.d/mysqld.cnf
mysql -uroot <<<"CREATE USER 'root'@'%' IDENTIFIED BY ''"
mysql -uroot <<<"GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' WITH GRANT OPTION"
mysql -uroot <<<"DROP USER 'root'@'localhost'"
mysql -uroot <<<"FLUSH PRIVILEGES"
echo "Done!"

info_str "Configure PHP-FPM"
sed -i 's/user = www-data/user = vagrant/g' /etc/php/7.3/fpm/pool.d/www.conf
sed -i 's/group = www-data/group = vagrant/g' /etc/php/7.3/fpm/pool.d/www.conf
sed -i 's/owner = www-data/owner = vagrant/g' /etc/php/7.3/fpm/pool.d/www.conf
sed -i -E 's/;access.log = .*/access.log = \/app\/vagrant\/nginx\/log\/fpm.access.log/g' /etc/php/7.3/fpm/pool.d/www.conf
sed -i -E 's/;access.format = .*/access.format = "%R - %u %t \\"%m %r%Q%q\\" %s %f %{mili}d %{kilo}M %C%%"/g' /etc/php/7.3/fpm/pool.d/www.conf
sed -i -E 's/;?error_log = .*/error_log = \/app\/vagrant\/nginx\/log\/fpm.error.log/g' /etc/php/7.3/fpm/php-fpm.conf
sed -i -E 's/upload_max_filesize = .*/upload_max_filesize = 2g/g' /etc/php/7.3/fpm/php.ini
sed -i -E 's/post_max_size = .*/post_max_size = 4g/g' /etc/php/7.3/fpm/php.ini

sed -i -E 's,;?date.timezone =.*,date.timezone = '"${timezone}"',g' /etc/php/7.3/fpm/php.ini
sed -i -E 's,;?date.timezone =.*,date.timezone = '"${timezone}"',g' /etc/php/7.3/cli/php.ini


cat <<EOF >/etc/php/7.3/mods-available/xdebug.ini
zend_extension=xdebug.so
xdebug.remote_enable=1
xdebug.remote_timeout=100
xdebug.remote_host=${remote_host}
xdebug.idekey="${xdebug_idekey}"
xdebug.remote_port=9000
xdebug.remote_autostart=1
xdebug.remote_log="/app/vagrant/nginx/log/xdebug.log"
EOF
echo "Done!"

info_str "Configure NGINX"
sed -i 's/user www-data/user vagrant/g' /etc/nginx/nginx.conf
rm -r /etc/nginx/sites-enabled/default
echo "Done!"

info_str "Enabling site configuration"
ln -s /app/vagrant/nginx/app.conf /etc/nginx/sites-enabled/app.conf
echo "Done!"

info_str "Initailize databases for MySQL"
mysql -uroot <<<"CREATE DATABASE yii_template CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
echo "Done!"

info_str "Install composer"
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# info_str "Install node.js"
# curl -sSL https://deb.nodesource.com/setup_12.x | bash -s
# apt-get install -y nodejs
