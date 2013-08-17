<?php

/**
 * Web Services
 *
 * @version     MOBILE 1.0.0 $Revision: 2 $ - Claroline 1.11
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
$conf_def['section']['WS']['properties'] = array ( 'activeWebService',
													'iOSAppLink',
													'iOSAppReady',
													'AndAppLink',
													'AndAppReady',
													'WPAppLink',
													'WPAppReady');

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

$conf_def_property_list[ 'iOSAppLink' ] =
array ( 'label'       => 'Lien vers l\'application iOS dans l\'Apple iOS App Store'
		, 'description' => ''
		, 'default'     => ''
		, 'type'        => 'string'
		, 'display'     => TRUE
		, 'readonly'    => FALSE
);

$conf_def_property_list[ 'iOSAppReady' ] =
array ( 'label'       => 'Active le lien vers l\'application iPhone'
		, 'description' => 'Retire le bandeau "Soon" de la banni�re de l\'Apple iOS App Store et active le lien vers l\'application'
		, 'default'     => FALSE
		, 'type'        => 'boolean'
		, 'display'     => TRUE
		, 'readonly'    => FALSE
		,'acceptedValue' => array('TRUE' => 'Oui', 'FALSE' => 'Non')
);

$conf_def_property_list[ 'AndAppLink' ] =
array ( 'label'       => 'Lien vers l\'application Android dans l\'Android Google Play Store'
		, 'description' => ''
		, 'default'     => 'https://play.google.com/store/apps/details?id=net.claroline.mobile.android'
		, 'type'        => 'string'
		, 'display'     => TRUE
		, 'readonly'    => FALSE
);

$conf_def_property_list[ 'AndAppReady' ] =
array ( 'label'       => 'Active le lien vers l\'application Android'
		, 'description' => 'Retire le bandeau "Soon" de la banni�re de l\'Android Google Play Store et active le lien vers l\'application'
		, 'default'     => TRUE
		, 'type'        => 'boolean'
		, 'display'     => TRUE
		, 'readonly'    => FALSE
		,'acceptedValue' => array('TRUE' => 'Oui', 'FALSE' => 'Non')
);

$conf_def_property_list[ 'WPAppLink' ] =
array ( 'label'       => 'Lien vers l\'application WindowsPhone dans le Microsoft Windows Phone Store'
		, 'description' => ''
		, 'default'     => 'http://www.windowsphone.com/s?appid=b88c8db5-89ef-4ec1-89bc-6713500d18cf'
		, 'type'        => 'string'
		, 'display'     => TRUE
		, 'readonly'    => FALSE
);

$conf_def_property_list[ 'WPAppReady' ] =
array ( 'label'       => 'Active le lien vers l\'application Windows Phone'
		, 'description' => 'Retire le bandeau "Soon" de la banni�re du Microsoft Windows Phone Store et active le lien vers l\'application'
		, 'default'     => TRUE
		, 'type'        => 'boolean'
		, 'display'     => TRUE
		, 'readonly'    => FALSE
		,'acceptedValue' => array('TRUE' => 'Oui', 'FALSE' => 'Non')
);

