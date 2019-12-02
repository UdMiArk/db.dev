#!/usr/bin/env bash

source /app/vagrant/provision/common.sh

#== Import script args ==

github_token=$(echo "$1")

#== Provision script ==

info_str "Provision-script user: $(whoami)"

info_str "Configure composer"
composer config --global github-oauth.github.com "${github_token}"
echo "Done!"

info_str "Install project dependencies"
cd /app || exit
composer --no-progress --prefer-dist install

info_str "Init project"
./init --env=Development --overwrite=y

info_str "Apply migrations"
/app/yii migrate --interactive=0

info_str "Create bash-alias 'app' for vagrant user"
echo 'alias app="cd /app"' | tee /home/vagrant/.bash_aliases
echo 'alias yii="/app/yii"' | tee -a /home/vagrant/.bash_aliases

info_str "Enabling colorized prompt for guest console"
sed -i "s/#force_color_prompt=yes/force_color_prompt=yes/" /home/vagrant/.bashrc
