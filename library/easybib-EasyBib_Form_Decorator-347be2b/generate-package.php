#!/usr/bin/env php
<?php
ini_set('date.timezone', 'Europe/Berlin');

require_once 'PEAR/PackageFileManager2.php';
PEAR::setErrorHandling(PEAR_ERROR_DIE);

$api_version     = '0.3.1';
$api_state       = 'alpha';

$release_version = '0.3.1';
$release_state   = 'alpha';
$release_notes   = "Changes for MessageFormatter helper to work nice with Bootstrap 2\n";

$description = "EasyBib_Form Decorator supports three modes: \n"
    . " * Table \n"
    . " * Div \n"
    . " * Twitter Bootstrap \n\n"
    . "The default is 'Twitter Bootstrap'.\n";

$package = new PEAR_PackageFileManager2();

$package->setOptions(
    array(
        'filelistgenerator' => 'file',
        'simpleoutput'      => true,
        'baseinstalldir'    => '/',
        'packagedirectory'  => './',
        'dir_roles'         => array(
            'library' => 'php',
            'tests'   => 'test',
            'docs'    => 'doc',
        ),
        'exceptions'        => array(
            'README.md' => 'doc',
        ),
        'ignore'            => array(
            '.git*',
            'generate-package.php',
            '*.tgz',
        )
    )
);

$package->setPackage('EasyBib_Form_Decorator');
$package->setSummary('A set of decorators for Zend_Form');
$package->setDescription($description);
$package->setChannel('easybib.github.com/pear');
$package->setPackageType('php');
$package->setLicense(
    'MIT',
    'http://www.opensource.org/licenses/mit-license.php'
);

$package->setNotes($release_notes);
$package->setReleaseVersion($release_version);
$package->setReleaseStability($release_state);
$package->setAPIVersion($api_version);
$package->setAPIStability($api_state);

$package->addMaintainer(
    'lead',
    'mischosch',
    'Michael Scholl',
    'michael@sch0ll.de'
);

$package->addMaintainer(
    'lead',
    'till',
    'Till Klampaeckel',
    'till@lagged.biz'
);

/**
 * Generate the list of files in {@link $GLOBALS['files']}
 *
 * @param string $path
 *
 * @return void
 */
function readDirectory($path) {
    foreach (glob($path . '/*') as $file) {
        if (!is_dir($file)) {
            $GLOBALS['files'][] = $file;
        } else {
            readDirectory($file);
        }
    }   
}

$files = array();
readDirectory(__DIR__ . '/library');

/**
 * @desc Strip this from the filename for 'addInstallAs'
 */
$base = __DIR__ . '/';

foreach ($files as $file) {

    $file2 = str_replace($base, '', $file);

    $package->addReplacement(
       $file2,
       'package-info',
       '@package_version@',
       'version'
    );
    $file2 = str_replace($base, '', $file);
    $package->addInstallAs($file2, str_replace('library/', '', $file2));
}

$package->setPhpDep('5.2.0');

$package->addPackageDepWithChannel(
    'optional',
    'ZF',
    'pear.zfcampus.org',
    '1.10.0'
);

$package->addExtensionDep('required', 'spl');
$package->setPearInstallerDep('1.4.0a7');
$package->generateContents();

if (   isset($_GET['make'])
    || (isset($_SERVER['argv']) && @$_SERVER['argv'][1] == 'make')
) {
    $package->writePackageFile();
} else {
    $package->debugPackageFile();
}
