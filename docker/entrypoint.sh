#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

# 1) إنشاء ملف البيئة إن لم يكن مربوطًا من المضيف
if [ ! -f .env ] && [ -f .env.docker ]; then
    cp .env.docker .env
fi

# 2) تهيئة مجلدات التخزين والصلاحيات
mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    storage/app/private \
    storage/app/public \
    bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R ug+rwX storage bootstrap/cache 2>/dev/null || true

# 3) الحاويات الثانوية (المهام المجدولة) تتخطى خطوات التهيئة
if [ "${SKIP_SETUP:-0}" = "1" ]; then
    exec "$@"
fi

# 4) تنظيف الكاش القديم
php artisan config:clear >/dev/null 2>&1 || true

# 5) توليد مفتاح التطبيق عند غيابه
if ! grep -qE '^APP_KEY=base64:' .env; then
    php artisan key:generate --force
fi

# 6) الانتظار حتى تجهز قاعدة البيانات
echo "بانتظار قاعدة البيانات..."
for _ in $(seq 1 60); do
    if php artisan migrate:status >/dev/null 2>&1; then
        break
    fi
    sleep 2
done

# 7) تنفيذ الترحيلات ثم تحسين الأداء
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "تم تشغيل المنصة بنجاح."

exec "$@"
