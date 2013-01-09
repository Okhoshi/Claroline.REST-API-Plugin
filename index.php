<?php
/**
 * Web Service Controller
 *
 * @version     MOBILE 1 $Revision: 8 $ - Claroline 1.11
 * @copyright   2001-2013 Universite Catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package     MOBILE
 * @author      Quentin Devos <q.devos@student.uclouvain.be>
 */
    $tlabelReq = 'MOBILE';
	
	//var_dump($_REQUEST);
    
    require dirname( __FILE__ ) . '/../../claroline/inc/claro_init_global.inc.php';
	
	if(!claro_is_user_authenticated()){
		header('Forbidden',true,403);
		die();
	}

	if(!get_conf('activeWebService',true)){
		header('Service Unavailable',true,503);
		die();
	}
	
	/*
	 * Check that the class provided and existing
	 */
	

	if(!(isset($_REQUEST['WS_Module'])) || empty($_REQUEST['WS_Module'])){
		header('Missing Argument',true, 400);
		die();
	} elseif(!file_exists('./lib/' . $_REQUEST['WS_Module'] . '_WSC.lib.php')){
		header('Not Implemented : Missing library', true, 501);
		die();
	}
	
	$class = $_REQUEST['WS_Module'] . 'WebServiceController';
	$classFile = $_REQUEST['WS_Module'] . '_WSC.lib';
	
	if(!(isset($_REQUEST['Method'])) || empty($_REQUEST['Method'])){
		header('Missing Argument',true, 400);
		die();
	}
	
	$method = $_REQUEST['Method'];
	
	/*
	 * Force headers
	 */
	if(!isset($_REQUEST['debug']))
		header("Content-Type: application/json; charset=utf-8");
	header("Cache-Control: no-cache, must-revalidate" );
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
	header("Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" );
	header("Pragma: no-cache" );
	
	/*
	 * Load the needed lib
	 */
	From::Module($tlabelReq)->uses($classFile);
	
	try{
		if(is_callable($class . '::' . $method)){
			$args = array();
			
			if(claro_get_current_course_id() != null){
				$args['cid'] = claro_get_current_course_id();
			} else {
				$args['cid'] = null;
			}
			
			$args['params'] = $_REQUEST;
			
			$result = call_user_func_array($class . '::' . $method,$args);
		} else {
			header('Not Implemented',true,501);
			die();
		}
		//Debug Mode
		if(isset($_REQUEST['debug'])){
			echo "\n";
			var_dump($result);
		}
		
		claro_utf8_encode_array($result);
		echo json_encode($result);
		
		
	} catch (RuntimeException $ex){
		header('Not Found',true, 404);
		echo $ex->getMessage();
		die();
	} catch (Exception $ex){
		header('Bad Request',true, 400);
		echo $ex->getMessage();
		die();
	}
?>