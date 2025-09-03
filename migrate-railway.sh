#!/bin/bash

echo "🚀 Running Laravel migrations on Railway..."

# Set environment variables
export $(grep -v '^#' .env | xargs)

# Run migrations
echo "📊 Running migrations..."
php artisan migrate --force

# Run seeders
echo "🌱 Running seeders..."
php artisan db:seed --force

echo "🎉 Database setup completed!"
