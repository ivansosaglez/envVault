#!/bin/sh
# Optional: reload the demo account and project on every boot (set DEMO_SEED=true).
# Handy for a public demo: it also wipes whatever visitors changed in the demo project.
if [ "$DEMO_SEED" = "true" ]; then
    echo "Seeding demo data..."
    php /var/www/html/artisan db:seed --class=DemoSeeder --force
fi
