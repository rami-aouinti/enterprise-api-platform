<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

$envFile = dirname(__DIR__) . '/.env';

if (is_file($envFile)) {
    new Dotenv()->bootEnv($envFile);
}

$environment = $_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? getenv('APP_ENV');

if ($environment === 'test') {
    // Functional tests disable the OIDC firewall and must not inherit runtime-only Keycloak settings.
    foreach (['KEYCLOAK_AUDIENCE', 'KEYCLOAK_ISSUER', 'KEYCLOAK_DISCOVERY_BASE_URI'] as $name) {
        unset($_SERVER[$name], $_ENV[$name]);
        putenv($name);
    }
}

if (($_SERVER['APP_DEBUG'] ?? $_ENV['APP_DEBUG'] ?? false)) {
    umask(0o000);
}
