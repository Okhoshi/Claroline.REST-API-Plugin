<?php
/**
 * Web Service Controller
 *
 * @version     MOBILE 1 $Revision: 9 $ - Claroline 1.11
 * @copyright   2001-2013 Universite Catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package     MOBILE
 * @author      Quentin Devos <q.devos@student.uclouvain.be>
 */

$tlabelReq = 'MOBILE';

rename( get_module_path( $tlabelReq ) . '/htaccess.dist' , get_module_path( $tlabelReq ) . '/.htaccess' );

$file = get_module_path( $tlabelReq ) . '/.htaccess';
$htaccess = file_get_contents( $file );

$append = get_path( 'url' );

$htaccess = str_replace( '%%PLATFORM_APPEND%%', $append, $htaccess);
file_put_contents( $file, $htaccess );