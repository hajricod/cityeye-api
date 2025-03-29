#!/bin/bash

echo "Starting Laravel development server..."
php artisan serve --host=0.0.0.0 --port=8000 &

echo "Starting Supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/supervisord.conf
