#!/bin/bash

# DragonFortune AI - Environment Setup Script
# This script helps setup the environment variables for the application

echo "🚀 DragonFortune AI - Environment Setup"
echo "======================================"

# Check if .env exists
if [ ! -f .env ]; then
    echo "📝 Creating .env file from .env.example..."
    if [ -f .env.example ]; then
        cp .env.example .env
    else
        echo "❌ .env.example not found. Creating basic .env file..."
        cat > .env << EOF
APP_NAME=Laravel
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=sqlite

CACHE_STORE=file
SESSION_DRIVER=file
SESSION_LIFETIME=120

QUEUE_CONNECTION=sync

# Base URL untuk API backend
# Kosongkan untuk menggunakan path relatif /api
API_BASE_URL=http://202.155.90.20:8000
EOF
    fi
    echo "✅ .env file created"
else
    echo "✅ .env file already exists"
fi

# Check if API_BASE_URL is set
if grep -q "API_BASE_URL=" .env; then
    echo "✅ API_BASE_URL is already configured"
    echo "Current value: $(grep API_BASE_URL .env)"
else
    echo "📝 Adding API_BASE_URL to .env..."
    echo "" >> .env
    echo "# Base URL untuk API backend" >> .env
    echo "# Kosongkan untuk menggunakan path relatif /api" >> .env
    echo "API_BASE_URL=http://202.155.90.20:8000" >> .env
    echo "✅ API_BASE_URL added to .env"
fi

# Generate APP_KEY if not exists
if ! grep -q "APP_KEY=" .env || [ -z "$(grep APP_KEY .env | cut -d '=' -f2)" ]; then
    echo "🔑 Generating APP_KEY..."
    php artisan key:generate
    echo "✅ APP_KEY generated"
else
    echo "✅ APP_KEY already exists"
fi

# Clear and cache config
echo "🔄 Clearing and caching configuration..."
php artisan config:clear
php artisan config:cache
echo "✅ Configuration cached"

# Show current configuration
echo ""
echo "📊 Current Configuration:"
echo "========================"
echo "APP_NAME: $(grep APP_NAME .env | cut -d '=' -f2)"
echo "APP_ENV: $(grep APP_ENV .env | cut -d '=' -f2)"
echo "APP_DEBUG: $(grep APP_DEBUG .env | cut -d '=' -f2)"
echo "APP_URL: $(grep APP_URL .env | cut -d '=' -f2)"
echo "API_BASE_URL: $(grep API_BASE_URL .env | cut -d '=' -f2)"

echo ""
echo "🎉 Environment setup completed!"
echo ""
echo "Next steps:"
echo "1. Start the development server: php artisan serve"
echo "2. Open http://localhost:8000 in your browser"
echo "3. Check browser console for API base URL confirmation"
echo ""
echo "To change API_BASE_URL, edit the .env file and run:"
echo "php artisan config:clear && php artisan config:cache"
