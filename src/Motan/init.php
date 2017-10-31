<?php
if (class_exists('Composer\Autoload\ClassLoader') === FALSE) {
	spl_autoload_register(function ($class) {
    $prefix = 'Motan\\';

    if (!defined('MOTAN_PHP_ROOT')) {
    	throw new Exception("Motan init Fail: should define a MOTAN_PHP_ROOT.", 1);
    }

    $len = strlen($prefix);

    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);

    $file = rtrim(MOTAN_PHP_ROOT, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});
}