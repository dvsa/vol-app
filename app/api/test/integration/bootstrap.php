<?php

/**
 * Bootstrap for the integration test suite (phpunit-integration.xml).
 *
 * These tests run real repository queries against the local development
 * database: `docker compose up -d db`, then load the schema and test data
 * with `npm run refresh` (from the repository root). Connection details can
 * be overridden with the VOL_TEST_DB_* environment variables (see
 * Dvsa\OlcsTest\Integration\Support\Database).
 */

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';
