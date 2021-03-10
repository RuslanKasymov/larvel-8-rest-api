#!/bin/bash
php artisan migrate --seed
php artisan queue:work --daemon redis &

exec $@
