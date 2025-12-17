#!/usr/bin/env sh
set -eu

APP_DIR="${APP_DIR:-/app}"
cd "$APP_DIR"

echo "⏳ Waiting for MySQL..."

# Attente MySQL via PDO (pas besoin de mysql client)
php -r '
$dsn = getenv("DATABASE_URL");
if (!$dsn) { fwrite(STDERR, "DATABASE_URL is missing\n"); exit(1); }

$u = parse_url($dsn);
$host = $u["host"] ?? "db";
$port = $u["port"] ?? 3306;
$user = $u["user"] ?? "";
$pass = $u["pass"] ?? "";
$db   = ltrim($u["path"] ?? "", "/");
$charset = "utf8mb4";

$pdoDsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";

$timeout = (int)(getenv("MYSQL_WAIT_TIMEOUT") ?: 60);
$start = time();
while (true) {
  try {
    new PDO($pdoDsn, $user, $pass, [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_TIMEOUT => 2,
    ]);
    echo "OK\n";
    exit(0);
  } catch (Throwable $e) {
    if (time() - $start >= $timeout) {
      fwrite(STDERR, "❌ MySQL not reachable after {$timeout}s: ".$e->getMessage()."\n");
      exit(1);
    }
    echo ".";
    usleep(500000); // 0.5s
  }
}
'

echo "✅ MySQL is ready"

# Migrations / schema (si Symfony/Doctrine)
if [ -f "bin/console" ]; then
  echo "🗄️ Running database migrations..."
  # Ne pas faire tomber le conteneur si aucune migration / déjà OK
  php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration || true

  # Filet de sécurité (optionnel) : utile si pas de migrations sur une DB vierge
  php bin/console doctrine:schema:update --force || true
fi

# Upsert admin (robuste: ne crash pas si table absente)
echo "👤 Upserting admin user..."
php -r '
$dsn = getenv("DATABASE_URL");
if (!$dsn) { fwrite(STDERR, "DATABASE_URL is missing\n"); exit(0); }

$adminEmail = getenv("ADMIN_EMAIL") ?: "admin@g2ss.fr";
$adminPassword = getenv("ADMIN_PASSWORD") ?: "admin";
$adminRole = getenv("ADMIN_ROLE") ?: "ROLE_ADMIN";

$u = parse_url($dsn);
$host = $u["host"] ?? "db";
$port = $u["port"] ?? 3306;
$user = $u["user"] ?? "";
$pass = $u["pass"] ?? "";
$db   = ltrim($u["path"] ?? "", "/");

$pdo = new PDO(
  "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4",
  $user,
  $pass,
  [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// Vérifie que la table user existe
$st = $pdo->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema=? AND table_name=?");
$st->execute([$db, "user"]);
if (!$st->fetchColumn()) {
  fwrite(STDERR, "⚠️ Table `user` not found (DB not initialized yet). Skipping admin upsert.\n");
  exit(0);
}

$hash = password_hash($adminPassword, PASSWORD_BCRYPT);

// Essaie d’upsert “compatible” (si ta table est différente, adapte les colonnes ici)
try {
  // Cherche l’utilisateur
  $sel = $pdo->prepare("SELECT id FROM user WHERE email = :email LIMIT 1");
  $sel->execute([":email" => $adminEmail]);
  $id = $sel->fetchColumn();

  if ($id) {
    $upd = $pdo->prepare("UPDATE user SET password = :password, roles = :roles WHERE id = :id");
    $upd->execute([
      ":password" => $hash,
      ":roles" => json_encode([$adminRole]),
      ":id" => $id
    ]);
    echo "✅ Admin updated (email=$adminEmail)\n";
  } else {
    $ins = $pdo->prepare("INSERT INTO user (email, password, roles) VALUES (:email, :password, :roles)");
    $ins->execute([
      ":email" => $adminEmail,
      ":password" => $hash,
      ":roles" => json_encode([$adminRole]),
    ]);
    echo "✅ Admin created (email=$adminEmail)\n";
  }
} catch (Throwable $e) {
  // Ne jamais faire tomber le conteneur à cause de l’admin seed
  fwrite(STDERR, "⚠️ Admin upsert failed (non-fatal): ".$e->getMessage()."\n");
  exit(0);
}
' || true

echo "🚀 Starting app..."
exec "$@"
