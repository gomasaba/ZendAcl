<?php
/**
 *
 * This file is loaded automatically by the app/webroot/index.php file after the core bootstrap.php is loaded
 * This is an application wide file to load any function that is not used within a class define.
 * You can also use this to include or require any files in your application.
 *
 */
// allow Zend Framework libraries to be included from the vendors folder


set_include_path(get_include_path() . PATH_SEPARATOR . dirname(dirname(__FILE__)) . DS .'Vendor');
