<?php declare(strict_types=1);

use Composer\Autoload\ClassLoader;

/** @var ClassLoader $loader */
$loader = require dirname(__DIR__, 4) . '/vendor/autoload.php';
$loader->addPsr4('Moorl\\PartsListConfigurator\\', dirname(__DIR__) . '/src');
$loader->addPsr4('MoorlFoundation\\', dirname(__DIR__, 2) . '/MoorlFoundation/src');
