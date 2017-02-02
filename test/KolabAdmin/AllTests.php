<?php
/**
 * All tests for the KolabAdmin application.
 *
 * PHP version 5
 *
 * @category Kolab
 * @package  KolabAdmin
 * @author   Gunnar Wrobel <wrobel@pardus.de>
 * @license  http://www.fsf.org/copyleft/lgpl.html LGPL
 * @link     http://pear.horde.org/index.php?package=KolabAdmin
 */

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'KolabAdmin_AllTests::main');
}

/**
 * Combine the tests for this package.
 *
 * Copyright 2007-2010 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @category Kolab
 * @package  KolabAdmin
 * @author   Gunnar Wrobel <wrobel@pardus.de>
 * @license  http://www.fsf.org/copyleft/lgpl.html LGPL
 * @link     http://pear.horde.org/index.php?package=KolabAdmin
 */
class KolabAdmin_AllTests
{
    /**
     * Main entry point for running the suite.
     *
     * @return NULL
     */
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    /**
     * Collect the unit tests of this directory into a new suite.
     *
     * @return PHPUnit_Framework_TestSuite The test suite.
     */
    public static function suite()
    {
        // Catch strict standards
        //error_reporting(E_ALL | E_STRICT);

        // Build the suite
        $suite = new PHPUnit_Framework_TestSuite('KolabAdmin');

        $basedir = dirname(__FILE__);
        $baseregexp = preg_quote($basedir . DIRECTORY_SEPARATOR, '/');

        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($basedir)) as $file) {
            if ($file->isFile() && preg_match('/Test.php$/', $file->getFilename())) {
                $pathname = $file->getPathname();
                require $pathname;

                $class = str_replace(DIRECTORY_SEPARATOR, '_',
                                     preg_replace("/^$baseregexp(.*)\.php/", '\\1', $pathname));
                $suite->addTestSuite('KolabAdmin_' . $class);
            }
        }

        return $suite;
    }

}

if (PHPUnit_MAIN_METHOD == 'KolabAdmin_AllTests::main') {
    KolabAdmin_AllTests::main();
}
