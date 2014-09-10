<?php

$application = require_once __DIR__ . '/../../src/bootstrap.php';
$application->detectContao();
if ($application->initContao()) {
    echo <<<WELCOME
===========================
MAGENTO INTERACTIVE CONSOLE
===========================
WELCOME;
    echo PHP_EOL . PHP_EOL . 'Initialized Contao (' . \Mage::getVersion() . ')' . PHP_EOL . PHP_EOL;
} else {
    echo "FATAL: Contao could not be initialized." . PHP_EOL;
}
