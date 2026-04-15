#!/bin/sh
set -e

echo "> En attente de la BDD..."
until mysql -h db -ug2s -pg2s -e "SELECT 1;" >/dev/null 2>&1; do
    echo "Erreur connexion BDD (entrypoint)..."
    sleep 2
done
echo "✓ BDD OK !"

composer install --no-interaction --optimize-autoloader
if [ ! -f node_modules/.bin/encore ]; then
    echo "> Installation des dépendances Node (Linux)..."
    npm install
else
    # Si node_modules existe déjà (volume anonyme), on vérifie
    # que le binaire spécifique à Tailwind/LightningCSS est là
    if [ ! -d "node_modules/lightningcss" ]; then
         npm install
    fi
fi

echo "> Creation schema de BDD..."
php bin/console doctrine:schema:update --force --complete

echo "> Build assets..."
npm run build
php bin/console cache:clear --env=prod

echo "✓ Application prete !"
exec "$@"
