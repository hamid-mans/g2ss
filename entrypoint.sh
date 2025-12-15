#!/bin/sh
set -e

echo "⏳ Waiting for MySQL..."
until php -r '
$dsn="mysql:host=db;dbname=g2ss;charset=utf8mb4";
try { new PDO($dsn, "g2ss", "g2ss"); echo "OK\n"; }
catch (Exception $e) { exit(1); }
'; do
  sleep 2
done

echo "✅ MySQL is ready"

echo "👤 Upserting admin user..."
php -r '
$pdo = new PDO(
  "mysql:host=db;dbname=g2ss;charset=utf8mb4",
  "g2ss",
  "g2ss",
  [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$email = "email@email.fr";
$hash  = "$2y$13$1ctbhhwiTYmHYTu0ZviUvesDLSKsV0dAqdYQc5X5xp9tN0AzzMRA2";
$roles = json_encode(["ROLE_SA"]);
$fn    = "Administrateur";
$ln    = "Admin";

/* insert si absent, update si déjà là (email est UNIQUE) */
$sql = "INSERT INTO user (email, password, roles, firstname, lastname)
        VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
          password=VALUES(password),
          roles=VALUES(roles),
          firstname=VALUES(firstname),
          lastname=VALUES(lastname)";
$pdo->prepare($sql)->execute([$email, $hash, $roles, $fn, $ln]);

echo "✔ done\n";
'

echo "🚀 Starting app..."
exec "$@"
