<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Invoice\Application;

/**
 * Spusti aplikaciu
 */
$app = new Application();
exit($app->run($_SERVER['argv'] ?? []));
