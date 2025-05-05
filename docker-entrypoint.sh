#!/bin/bash
set -e

if [ -f "composer.json" ]; then
    if [ ! -d "vendor" ]; then
        echo "📦 Installing composer dependencies..."
        composer install --no-interaction --prefer-dist --no-scripts
    fi
fi

exec "$@"
