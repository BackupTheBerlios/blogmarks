<?php
/** Generation du package
 * Détails.
 *
 * $Id: make_pkg.php,v 1.1 2004/03/31 19:41:50 mbertier Exp $
 */

require_once 'PEAR/PackageFileManager.php';

$pkgxml = new PEAR_PackageFileManager;

$e = $pkgxml->setOptions( array( 'baseinstalldir'       => '/',
                                 'package'              => 'Blogmarks',
                                 'version'              => '0.1',
                                 'summary'              => 'Stop bookmarking, start blogmarking',
                                 'description'          => 'Soon to come...',
                                 'license'              => 'GPL',
                                 'packagedirectory'     => "/home/mbertier/tmp/Blogmarks",
                                 'filelistgenerator'    => 'cvs',
                                 'state'                => 'beta',
                                 'notes'                => 'first try',
                                 'ignore'               => array(),
                                 'installexceptions'    => array(),
                                 'dir_roles'            => array( 'tutorials' => 'doc' ),
                                 'exceptions'           => array()   ) );


# -- Maintainers
$pkgxml->addMaintainer( 'mbertier', 'lead', 'Tristan Rivoallan', 'mbertier@parishq.net' );
$pkgxml->addMaintainer( 'benfle', 'developer', 'Benoit Fleury', 'benfle@tipic.com' );

if ( PEAR::isError($e) ) {
    echo "*** Erreur: ". $e->getMessage() . "\n";
    exit;
}

/*
$e = $pkgxml->debugPackageFile( false );

if ( PEAR::isError($e) ) {
    echo "*** Erreur: ". $e->getMessage() . "\n";
    exit;
}
*/

$e = $pkgxml->writePackageFile( );

if ( PEAR::isError($e) ) {
    echo "*** Erreur: ". $e->getMessage() . "\n";
    exit;
}

echo "package.xml a ete genere :)\n";
echo ">>> la suite:\n";
echo ">>> pear package-validate && pear package\n"

?>