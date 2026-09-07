#!/bin/bash
set -e

CONTAINER="dammminvitation-app"
FIRST_DEPLOY=false

echo "🚀 Deploying Dammminvitation..."

# Cek apakah ini first deploy (container belum ada)
if ! docker ps -a --format '{{.Names}}' | grep -q "^${CONTAINER}$"; then
    FIRST_DEPLOY=true
    echo "📦 First deploy detected"
fi

# Build & up
echo "🔨 Building image..."
docker compose build --no-cache

echo "▶️  Starting container..."
docker compose up -d

# Tunggu container ready
echo "⏳ Waiting for container..."
sleep 5

# Selalu jalankan migrate
echo "🗄️  Running migrations..."
docker exec $CONTAINER php artisan migrate --force

# First deploy: seed + storage:link
if [ "$FIRST_DEPLOY" = true ]; then
    echo "🌱 Seeding database..."
    docker exec $CONTAINER php artisan db:seed --force

    echo "🔗 Creating storage link..."
    docker exec $CONTAINER php artisan storage:link
fi

# Cache
echo "⚡ Caching config, routes, views..."
docker exec $CONTAINER php artisan config:cache
docker exec $CONTAINER php artisan route:cache
docker exec $CONTAINER php artisan view:cache

echo "✅ Deploy selesai! https://dammminvitation.ffatahilah.my.id"
