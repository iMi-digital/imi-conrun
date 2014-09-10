#!/usr/bin/env php
<?php

Phar::mapPhar('imi-conrun.phar');

$application = require_once 'phar://imi-conrun.phar/src/bootstrap.php';
$application->setPharMode(true);
$application->run();

__HALT_COMPILER();