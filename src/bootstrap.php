<?php

if (!class_exists('IMI_Contrun_Bootstrap')) {
    class IMI_Contrun_Bootstrap
    {
        public static function includeIfExists($file)
        {
            if (file_exists($file)) {
                return include $file;
            }
        }

        /**
         * @throws ErrorException
         * @return \Composer\Autoload\ClassLoader
         */
        public static function getLoader()
        {
            if ((!$loader = \IMI_Contrun_Bootstrap::includeIfExists(__DIR__.'/../vendor/autoload.php'))
                && (!$loader = \IMI_Contrun_Bootstrap::includeIfExists(__DIR__.'/../../../autoload.php'))) {
                throw new \ErrorException('You must set up the project dependencies, run the following commands:'.PHP_EOL.
                    'curl -s http://getcomposer.org/installer | php'.PHP_EOL.
                    'php composer.phar install'.PHP_EOL);
            }

            return $loader;
        }
    }
}

try {
    $loader = \IMI_Contrun_Bootstrap::getLoader();
    $application = new \IMI\Contao\Application($loader);

    return $application;

} catch (\Exception $e) {
    echo $e->getMessage();
    exit(1);
}