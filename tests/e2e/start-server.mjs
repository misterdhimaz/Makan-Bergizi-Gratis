import { execFileSync, spawn } from 'node:child_process';
import fs from 'node:fs';
import path from 'node:path';

const e2eDb = process.env.E2E_DB_DATABASE || 'mbg_e2e';
const env = { ...process.env, DB_DATABASE: e2eDb };

const bootstrapScript = path.resolve('storage/framework/e2e_bootstrap.php');
fs.writeFileSync(
    bootstrapScript,
    `<?php
require __DIR__ . '/../../vendor/autoload.php';
$dotenv = Dotenv\\Dotenv::createImmutable(__DIR__ . '/../..');
$dotenv->safeLoad();

$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$port = $_ENV['DB_PORT'] ?? '3306';
$user = $_ENV['DB_USERNAME'] ?? 'root';
$pass = $_ENV['DB_PASSWORD'] ?? '';
$db = getenv('DB_DATABASE') ?: 'mbg_e2e';

$pdo = new PDO("mysql:host={$host};port={$port}", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->exec("CREATE DATABASE IF NOT EXISTS \`{$db}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
`,
);

execFileSync('php', [bootstrapScript], { env, stdio: 'inherit' });
execFileSync('php', ['artisan', 'migrate:fresh', '--seed', '--force'], { env, stdio: 'inherit' });

const seedUsersScript = path.resolve('storage/framework/e2e_seed_users.php');
fs.writeFileSync(
    seedUsersScript,
    `<?php
require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap();

$adminPw = getenv('E2E_ADMIN_PASSWORD');
$tempPw = getenv('E2E_TEMP_PASSWORD');

$admin = App\\Models\\User::where('username', 'arief.mahendra')->firstOrFail();
$admin->forceFill([
    'password' => Illuminate\\Support\\Facades\\Hash::make($adminPw),
    'must_change_password' => false,
    'failed_login_attempts' => 0,
    'locked_until' => null,
])->saveQuietly();

$firstLogin = App\\Models\\User::where('username', 'nadia.putri')->firstOrFail();
$firstLogin->forceFill([
    'password' => Illuminate\\Support\\Facades\\Hash::make($tempPw),
    'must_change_password' => true,
    'password_expires_at' => now()->addHours(24),
    'failed_login_attempts' => 0,
    'locked_until' => null,
])->saveQuietly();

$inactive = App\\Models\\User::where('username', 'rina.safitri')->firstOrFail();
$inactive->forceFill([
    'password' => Illuminate\\Support\\Facades\\Hash::make($tempPw),
    'is_active' => false,
])->saveQuietly();
`,
);

execFileSync('php', [seedUsersScript], { env, stdio: 'inherit' });
fs.unlinkSync(bootstrapScript);
fs.unlinkSync(seedUsersScript);

const child = spawn('php', ['artisan', 'serve', '--host=127.0.0.1', '--port=8011'], {
    env,
    stdio: 'inherit',
});

process.on('SIGTERM', () => child.kill('SIGTERM'));
process.on('SIGINT', () => child.kill('SIGINT'));
child.on('exit', (code) => process.exit(code ?? 0));
