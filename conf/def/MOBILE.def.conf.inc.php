<?php

/**
 * Web Services
 *
 * @version     MOBILE 1.0.0 $Revision: 1 $ - Claroline 1.9
 * @copyright   2001-2012 Universite Catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package     MOBILE
 * @author      Quentin Devos <q.devos@student.uclouvain.be>
 */

if ( count( get_included_files() ) == 1 ) die( '---' );

$conf_def['config_code'] = 'MOBILE';
$conf_def['config_file'] = 'MOBILE.conf.php';
$conf_def['config_name'] = 'Web Services';

$conf_def['section']['WS']['label']      = 'Web Service';
$conf_def['section']['WS']['description']= '';
$conf_def['section']['WS']['properties'] = array ( 'activeWebService');
// WS
$conf_def_property_list[ 'activeWebService' ] =
array ( 'label'       => 'Active le Serveur Web Services'
		, 'description' => ''
		, 'default'     => TRUE
		, 'type'        => 'boolean'
		, 'display'     => TRUE
		, 'readonly'    => FALSE
		,'acceptedValue' => array('TRUE' => 'Oui', 'FALSE' => 'Non')
);