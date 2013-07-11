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

//Provides to the methods only the following arguments !
$allowedArgs = array('resID', 'recursive', 'curDirPath', 'platform' );

require_once dirname( __FILE__ ) . '/../../claroline/inc/claro_init_global.inc.php';

if ( !claro_is_user_authenticated() )
{
	header('Forbidden',true,403);
	die();
}

if ( !get_conf('activeWebService',true) )
{
	header('Service Unavailable',true,503);
	die();
}

/*
 * Check that the class and method are provided and exist
*/
if ( !( isset($_REQUEST['module']) ) || empty($_REQUEST['module']) )
{
	header('Missing Argument',true, 400);
	die();
}

if ( !( isset($_REQUEST['method']) ) || empty($_REQUEST['method']) )
{
	header('Missing Argument',true, 400);
	die();
}

try
{
	/*
	 * Load the needed lib
	*/
	From::module($tlabelReq)->uses('pluginloader.lib');
	$pl = new PluginLoader( './plugins/' );
	if ( $class = $pl->load($_REQUEST['module']) )
	{
		if ( method_exists($class, $_REQUEST['method']) )
		{
			$method = $_REQUEST['method'];
				
			$args = array();
			foreach ( $allowedArgs as $allowed )
			{
				if ( isset($_REQUEST[$allowed]) )
				{
					$args[$allowed] = $_REQUEST[$allowed];
				}
			}
			$args['module'] = $_REQUEST['module'];
				
			$result = $class->$method($args);
		}
		else
		{
			header('Not Implemented',true,501);
			die();
		}
	}
	else
	{
		header('Not Implemented',true,501);
		die("Plugin not installed");
	}

	/*
	 * Force headers or debug mode
	*/
	if ( isset( $_REQUEST["debug"] ) )
	{
		var_dump($result);
		die();
	}
	
	header("Cache-Control: no-cache, must-revalidate" );
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
	header("Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" );
	header("Pragma: no-cache" );
	header("Content-Type: application/json; charset=utf-8");

	claro_utf8_encode_array($result);
	echo json_encode($result);

}
catch ( RuntimeException $ex )
{
	header($ex->getMessage(),true, $ex->getCode());
	echo $ex->getMessage();
	//die();
}
catch ( Exception $ex )
{
	header('Bad Request',true, 400);
	echo $ex->getMessage();
	//die();
}