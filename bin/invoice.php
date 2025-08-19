<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Invoice\Application;

/**
 * Spustí aplikáciu
 */
$app = new Application();
exit($app->run($_SERVER['argv'] ?? []));
