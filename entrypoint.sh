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
$adminFirstname = getenv("ADMIN_FIRSTNAME") ?: "Admin";
$adminLastname  = getenv("ADMIN_LASTNAME")  ?: "User";

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

// Vérifie si la colonne firstname existe (sécurité si schéma varie)
$col = $pdo->prepare("
  SELECT column_name
  FROM information_schema.columns
  WHERE table_schema=? AND table_name=? AND column_name IN (\"firstname\",\"last_name\",\"lastname\")
");
$col->execute([$db, "user"]);
$cols = $col->fetchAll(PDO::FETCH_COLUMN);
$hasFirstname = in_array("firstname", $cols, true);
$hasLastname  = in_array("lastname", $cols, true) || in_array("last_name", $cols, true);

// Hash : je te conseille Argon2id si dispo, sinon bcrypt
$algo = defined("PASSWORD_ARGON2ID") ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT;
$hash = password_hash($adminPassword, $algo);

try {
  // Cherche l’utilisateur
  $sel = $pdo->prepare("SELECT id FROM user WHERE email = :email LIMIT 1");
  $sel->execute([":email" => $adminEmail]);
  $id = $sel->fetchColumn();

  $rolesJson = json_encode([$adminRole]);

  if ($id) {
    // Update
    $sql = "UPDATE user SET password = :password, roles = :roles";
    $params = [":password" => $hash, ":roles" => $rolesJson, ":id" => $id];

    if ($hasFirstname) { $sql .= ", firstname = :firstname"; $params[":firstname"] = $adminFirstname; }
    if ($hasLastname)  { $sql .= ", lastname = :lastname";  $params[":lastname"]  = $adminLastname; }

    $sql .= " WHERE id = :id";
    $upd = $pdo->prepare($sql);
    $upd->execute($params);

    echo "✅ Admin updated (email=$adminEmail)\n";
  } else {
    // Insert (avec firstname si présent / requis)
    $fields = ["email", "password", "roles"];
    $values = [":email", ":password", ":roles"];
    $params = [":email" => $adminEmail, ":password" => $hash, ":roles" => $rolesJson];

    if ($hasFirstname) { $fields[] = "firstname"; $values[] = ":firstname"; $params[":firstname"] = $adminFirstname; }
    if ($hasLastname)  { $fields[] = "lastname";  $values[] = ":lastname";  $params[":lastname"]  = $adminLastname; }

    $sql = "INSERT INTO user (".implode(",", $fields).") VALUES (".implode(",", $values).")";
    $ins = $pdo->prepare($sql);
    $ins->execute($params);

    echo "✅ Admin created (email=$adminEmail)\n";
  }
} catch (Throwable $e) {
  fwrite(STDERR, "⚠️ Admin upsert failed (non-fatal): ".$e->getMessage()."\n");
  exit(0);
}
' || true
if [ -f "bin/console" ]; then
  echo "🎛️ asset-map:compile..."
  php bin/console asset-map:compile --env="${APP_ENV:-prod}" || true

  echo "🔥 cache:warmup..."
  php bin/console cache:warmup --env="${APP_ENV:-prod}" || true
fi

echo "🚀 Starting app..."

exec "$@"
