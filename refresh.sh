#!/usr/bin/env bash

# get the user that owns our app here
APP_PATH="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_USER=$(stat -c '%U' "$APP_PATH")

# make sure we own it
chown -R $APP_USER:$APP_USER $APP_PATH*;

# reset permissions first
find $APP_PATH -type d -exec chmod 700 {} \;
find $APP_PATH -type f -exec chmod 600 {} \;
chmod +x $APP_PATH/refresh.sh;

# make sure composer will not throw up on us...
export COMPOSER_ALLOW_SUPERUSER=1;

# update all packages
composer -d $APP_PATH update;

# dump the composer autoloader and force it to regenerate
composer -d $APP_PATH dumpautoload -o -n;

# Reinstall node_modules with correct permissions
rm -rf $APP_PATH/node_modules && npm install --prefix "$APP_PATH"

# now refresh NPM
npm run build --prefix "$APP_PATH";

# just in case php is caching
service php8.4-fpm restart && service nginx reload

# clear out our redis cache
redis-cli flushall

# get rid of the node modules
rm -rf $APP_PATH/node_modules