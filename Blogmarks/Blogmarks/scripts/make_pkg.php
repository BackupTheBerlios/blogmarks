<?php
/** Génération du package PEAR
 * Editer le fichier.
 *
 * $Id: make_pkg.php,v 1.2 2004/04/01 01:51:59 mbertier Exp $
 */

require_once 'Console/Getopt.php';
require_once 'PEAR/PackageFileManager.php';

# -- Définition des paramètres acceptés / obligatoires
$short_opts = 'p:r';
$long_opts  = array( 'packagedir=' );
                        
# -- Traitement des paramètres passés au script
$getopts =& new Console_Getopt();

# -- Récupération de paramètre (on ne peut pas définir de paramètre oblifgatoire!)
$opts = $getopts->getopt( $getopts->readPHPArgv(), $short_opts, $long_opts );
if ( PEAR::isError($opts) ) die( "!!" . $opts->getMessage() . "\n" );

# -- Constitution d'un tableau de paramètres plus clair (pour mon cerveau)
for ( $i = 0; $i < count($opts[0]); $i++ ) {
    $opts_h[ $opts[0][$i][0] ] = ( isset($opts[0][$i][1]) ? $opts[0][$i][1] : $opts[1][$i] );
}

# -- Contrôle des paramètres obligatoires
foreach ( $mandatory_opts as $short => $long ) {
    if ( ! in_array($short, $opts_h) && ! in_array($long, $opts_h) ) {
        
    }
}

print_r( $opts_h );

# -- Par défaut, on considère que le package se situe dans le réperoire courant


$pkgxml = new PEAR_PackageFileManager;

# -- Options
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
$pkgxml->addMaintainer( 'mbertier', 
                        'lead', 
                        'Tristan Rivoallan', 
                        'mbertier@parishq.net' );

$pkgxml->addMaintainer( 'benfle', 
                        'developer', 
                        'Benoit Fleury', 
                        'benfle@tipic.com' );


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

echo ">>> package.xml a été généré :)\n";
echo ">>> Il reste à valider package.xml et à créer le package :\n";
echo ">>> pear package-validate && pear package\n"

?>