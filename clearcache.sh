#php app/console cache:clear --env=dev
php -d memory_limit=-1  bin/console cache:clear --env=prod --verbose --no-warmup
php bin/console assets:install

