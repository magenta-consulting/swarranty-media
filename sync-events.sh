#!/bin/bash
for page in {1..29}
do
    php /var/www/wellness/legacy/bin/console sync:redemption-event --env=prod
    sleep 2
done


