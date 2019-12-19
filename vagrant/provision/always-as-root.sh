#!/usr/bin/env bash

source /app/vagrant/provision/common.sh

#== Provision script ==

info_str "Provision-script user: $(whoami)"

info_str "Restart web-stack"
service php7.3-fpm restart
service nginx restart
service mysql restart

info_str "Restart Yii Queue service"
cp /app/vagrant/systemd/dbowen-queue@.service /etc/systemd/system/dbowen-queue@.service
systemctl daemon-reload
systemctl start dbowen-queue@1 dbowen-queue@2 dbowen-queue@3