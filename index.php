<?php
/**
 * Web Services Provider
 *
 * @version     MOBILE 1 $Revision: 3 $ - Claroline 1.11
 * @copyright   2001-2012 Universite Catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package     MOBILE
 * @author      Quentin Devos <q.devos@student.uclouvain.be>
 */
    $tlabelReq = 'MOBILE';
    
    require dirname( __FILE__ ) . '/../../claroline/inc/claro_init_global.inc.php';
	
	if(!claro_is_user_authenticated()){
		header('Forbidden',true,403);
		die();
	}

	if(!get_conf('activeWebService',true)){
		header('Service Unavailable',true,503);
		die();
	}
	
	if(!(isset($_REQUEST['Method']) && isset($_REQUEST['Package']))){
		header('Missing Argument',true, 400);
		die();
	}
	
	/*
	 * Force headers
	 */
	header("Content-Type: application/json; charset=utf-8");
	header("Cache-Control: no-cache, must-revalidate" );
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
	header("Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" );
	header("Pragma: no-cache" );

	//LOAD ALL LIBS
	From::Module($tlabelReq)->uses($_REQUEST['Package'] . '.lib');
	
	try{
		if(is_callable($_REQUEST['Package'] . '::' . $_REQUEST['Method'])){
			$args = array();
			
			if(claro_get_current_course_id() != null){
				$args[] = claro_get_current_course_id();
			}
			if(isset($_REQUEST['resID'])){
				$args[] = $_REQUEST['resID'];
			}
			
			$result = call_user_func_array($_REQUEST['Package'] . '::' . $_REQUEST['Method'],$args);
		} else {
			header('Not Implemented',true,501);
			die();
		}
		
		claro_utf8_encode_array($result);
		echo json_encode($result);
		
		//Debug Mode
		if(isset($_REQUEST['debug'])){
			print_r($result);
		}
		
	} catch (Exception $ex){
		header('Bad Request',true, 400);
		echo $ex->getMessage();
		die();
	}
?>