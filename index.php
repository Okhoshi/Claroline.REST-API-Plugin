<?php
/**
 * Web Services Provider
 *
 * @version     MOBILE 1 $Revision: 2 $ - Claroline 1.9
 * @copyright   2001-2012 Universite Catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package     MOBILE
 * @author      Quentin Devos <q.devos@student.uclouvain.be>
 */
    $tlabelReq = 'MOBILE';
    
    require dirname( __FILE__ ) . '/../../claroline/inc/claro_init_global.inc.php';
    $allowed = array('getUserData',
					 'getCourseList',
					 'getDocList',
					 'getCourseToolList',
					 'getAnnounceList',
					 'getSingleAnnounce',
					 'getUpdates');
	
	if(!claro_is_user_authenticated()){
		header('Forbidden',true,403);
		die();
	}

	if(!get_conf('activeWebService',true)){
		header('Service Unavailable',true,503);
		die();
	}
	
	if(!isset($_REQUEST['Method']) || !in_array($_REQUEST['Method'],$allowed)){
		header('Not Implemented',true,501);
		die();
	}
	
	From::Module( 'MOBILE' )->uses('ClaroServ.lib.php');
	
	/*
	 * Force headers
	 */
	header("Content-Type: application/json; charset=utf-8");
	header("Cache-Control: no-cache, must-revalidate" );
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
	header("Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" );
	header("Pragma: no-cache" );
	
	$serv = ClaroWeb::getInstance();
	
	switch($_REQUEST['Method']){
		case 'getUserData':
			$result =  $serv->getUserData();
			break;
		case 'getCourseList':
			$result = $serv->getCourseList();
			break;
		case 'getDocList':
			if(claro_get_current_course_id() == null){
				header('Missing Argument',true, 400);
				die();
			}
			$result = $serv->getDocList(claro_get_current_course_id(),'',true);
			break;
		case 'getAnnounceList':
			if(claro_get_current_course_id() == null){
				header('Missing Argument',true, 400);
				die();
			}
			$result = $serv->getAnnounceList(claro_get_current_course_id());
			break;
		case 'getSingleAnnounce':
			if(claro_get_current_course_id() == null || !isset($_REQUEST['resId'])){
				header('Missing Argument',true, 400);
				die();
			}
			$result = $serv->getSingleAnnounce(claro_get_current_course_id(), $_REQUEST['resId']);
			break;
		case 'getCourseToolList':
			if(claro_get_current_course_id() == null){
				header('Missing Argument',true, 400);
				die();
			}
			$result = $serv->getCourseToolList(claro_get_current_course_id(),$_profileId,$is_courseAdmin);
			break;
		case 'getUpdates':
			$result = $serv->getUpdates();
			break;
	}
	if(isset($_REQUEST['debug'])){
		print_r($result);
	}
	claro_utf8_encode_array($result);
	echo json_encode($result);
?>