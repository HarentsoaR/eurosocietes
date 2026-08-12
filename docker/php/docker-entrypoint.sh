#!/bin/sh
set -e

# Regenerate Composer autoloader for new API controllers
composer dump-autoload --optimize

# Start the PHP development server
exec php artisan serve --host=0.0.0.0 --port=8000
