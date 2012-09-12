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
	
	if(!isset($_REQUEST['Method'])){
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
	
	switch($_REQUEST['Method']){
	//GENERAL.LIB
		case 'getUserData':
			From::Module( 'MOBILE' )->uses('General.lib.php');
			$result =  General::getInstance()->getUserData();
			break;
		case 'getCourseList':
			From::Module( 'MOBILE' )->uses('General.lib.php');
			$result = General::getInstance()->getCourseList();
			break;
		case 'getUpdates':
			From::Module( 'MOBILE' )->uses('General.lib.php');
			$result = General::getInstance()->getUpdates();
			break;
		case 'getCourseToolList':
			if(claro_get_current_course_id() == null){
				header('Missing Argument',true, 400);
				die();
			}
			From::Module( 'MOBILE' )->uses('Announce.lib.php');
			$result = Announce::getInstance()->getCourseToolList(claro_get_current_course_id(),$_profileId,$is_courseAdmin);
			break;
	//DOCUMENTS.LIB
		case 'getDocList':
			if(claro_get_current_course_id() == null){
				header('Missing Argument',true, 400);
				die();
			}
			From::Module( 'MOBILE' )->uses('Documents.lib.php');
			$result = Documents::getInstance()->getDocList(claro_get_current_course_id(),'',true);
			break;
	//ANNOUNCE.LIB
		case 'getAnnounceList':
			From::Module( 'MOBILE' )->uses('General.lib.php');
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
			From::Module( 'MOBILE' )->uses('Announce.lib.php');
			$result = Announce::getInstance()->getSingleAnnounce(claro_get_current_course_id(), $_REQUEST['resId']);
			break;
//\\INSERT NEW CASES BEFORE THIS LINE
	//NOT IMPLEMENTED -> NO LIB
		default :
			header('Not Implemented',true,501);
			die();
			break;
	}
	
	claro_utf8_encode_array($result);
	echo json_encode($result);
	
	//Debug Mode
	if(isset($_REQUEST['debug'])){
		print_r($result);
	}
?>