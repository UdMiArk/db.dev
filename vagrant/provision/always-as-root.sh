#!/usr/bin/env bash

source /app/vagrant/provision/common.sh

#== Provision script ==

info_str "Provision-script user: $(whoami)"

info_str "Restart web-stack"
service php7.3-fpm restart
service nginx restart
service mysql restart
