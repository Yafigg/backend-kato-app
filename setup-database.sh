#!/bin/bash

echo "🚀 Setting up Kato App database on Supabase..."

# Set environment variables for Supabase
export DB_CONNECTION=pgsql
export DB_HOST=aws-1-ap-southeast-1.pooler.supabase.com
export DB_PORT=6543
export DB_DATABASE=postgres
export DB_USERNAME=postgres.wmkbpfasrklwdzhbyjzf
export DB_PASSWORD=Socrates2536@

echo "📊 Running migrations..."
php artisan migrate --force

echo "🌱 Running seeders..."
php artisan db:seed --force

echo "🎉 Database setup completed successfully!"
echo "Your Kato App backend is now ready for production!"
