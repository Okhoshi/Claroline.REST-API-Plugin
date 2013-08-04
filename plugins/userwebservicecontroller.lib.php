<?php
/**
 * Web Service Controller - User plugin
 *
 * @copyright   2001-2013 Universite Catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package     MOBILE
 * @author      Quentin Devos <q.devos@student.uclouvain.be>
 */
class USERWebServiceController
{
	
	/**
	 * Returns the data of the current user.
	 * @webservice{/module/MOBILE/User/getUserData}
	 * @ws_arg{Method,getUserData}
	 * @return array of string
	 */
	function getUserData()
	{
		$userData = claro_get_current_user_data();
		//Bug fix for kernel bug, into user_get_picture_***() into user.lib. Bad index ?
		$userData['user_id'] = $userData['userId'];
		$userData['picture'] = $_SERVER['SERVER_NAME'] . user_get_picture_url($userData);
		unset($userData['authSource'],
			  $userData['creatorId'],
			  $userData['lastLogin'],
			  $userData['mail'],
			  $userData['officialEmail'],
			  $userData['phone']);
		$userData['platformName'] = get_conf('siteName', "Claroline");
		$userData['institutionName'] = get_conf('institution_name',"");
		return $userData;
	}

	/**
	 * Returns the list of courses followed by the user.
	 * @webservice{/module/MOBILE/User/getCourseList}
	 * @ws_arg{Method,getCourseList}
	 * @return array of course object
	 */
	function getCourseList()
	{
		FromKernel::uses('courselist.lib');
		$claroNotification = Claroline::getInstance()->notification;
		$date = $claroNotification->getLastActionBeforeLoginDate(claro_get_current_user_id());
		$courseList = array();
		$notifiedCourses = $claroNotification->getNotifiedCourses($date,claro_get_current_user_id());
		foreach ( get_user_course_list(claro_get_current_user_id()) as $course )
		{
			$course_data = claro_get_course_data($course['sysCode']);
			$course['officialEmail'] = $course_data['email'];
			$course['notifiedDate'] = in_array($course['sysCode'],$notifiedCourses);
			$courseList[] = $course;
		}
		return $courseList;
	}
	
	/**
	 * Returns the tool list for each cours of the user.
	 * @todo Rewrite in a more generic way of treating tool...
	 * @throws InvalidArgumentException if the cid in not provided.
	 * @webservice{/module/MOBILE/User/getCourseToolList/cidReq}
	 * @ws_arg{Method,getCourseToolList}
	 * @ws_arg{cidReq,SYSCODE of requested cours}
	 * @return array of course object with only the syscode and tool-related fields filled.
	 */
	function getCourseToolList()
	{
		$cid = claro_get_current_course_id();
		if ( $cid == null )
		{
			throw new InvalidArgumentException('Missing cid argument!');
		}
		
		FromKernel::uses('courselist.lib');
		$claroNotification = Claroline::getInstance()->notification;
		$date = $claroNotification->getLastActionBeforeLoginDate(claro_get_current_user_id());
		$course = array();
		$course['sysCode'] = $cid;
		$notifiedTools = $claroNotification->getNotifiedTools($cid,$date,claro_get_current_user_id());
		foreach ( claro_get_course_tool_list($course['sysCode'],claro_get_current_user_profile_id_in_course($cid)) as $tool )
		{ 
			switch ( $tool['label'] )
			{
				case 'CLANN':
					$course['isAnn'] = ($tool['visibility'] || claro_is_allowed_to_edit());
					$course['annNotif'] = in_array($tool['tool_id'],$notifiedTools);
					break;
				case 'CLDOC':
					$course['isDnL'] = ($tool['visibility'] || claro_is_allowed_to_edit());
					$course['dnlNotif'] = in_array($tool['tool_id'],$notifiedTools);
					break;
				default:
					break;
			}
		}
		return $course;
	}
	
	/**
	 * Returns the tool list for each cours of the user.
	 * @throws InvalidArgumentException if the cid in not provided.
	 * @webservice{/module/MOBILE/User/getCoursToolList/cidReq}
	 * @ws_arg{method,getCoursToolList}
	 * @ws_arg{cidReq,SYSCODE of requested cours}
	 * @return array of course object with only the syscode and tool-related fields filled.
	 */
	function getToolList()
	{
		$cid = claro_get_current_course_id();
		if ( $cid == null )
		{
			throw new InvalidArgumentException('Missing cid argument!');
		}
		
		FromKernel::uses('courselist.lib');
		$tools = array();
		foreach ( claro_get_course_tool_list($cid,claro_get_current_user_profile_id_in_course($cid)) as $tool )
		{
			if ( $tool['installed'] && $tool['activated'] && ($tool['visibility'] || claro_is_allowed_to_edit()) )
			{
				unset($tool['id']);
				unset($tool['tool_id']);
				unset($tool['external_name']);
				unset($tool['external']);
				unset($tool['icon']);
				unset($tool['activation']);
				unset($tool['url']);
				unset($tool['activated']);
				unset($tool['installed']);
				$tool['name'] = get_lang($tool['name']);
				$tool['visibility'] = $tool['visibility'] == true; // Force the boolean representation in JSON
				$tools[] = $tool;
			}
		}
		return $tools;
	}
	
	/**
	 * Retrieve the notified items for the current user. Do not mark them as showed.
	 * @webservice{/module/MOBILE/User/getUpdates}
	 * @ws_arg{Method, getUpdates}
	 * @return empty array if no notification.
	 * 		   Else, return array([SYSCODE] => array([LABEL] => notified resource object, ...), ...)
	 */
	function getUpdates( $args )
	{
		$claroNotification = Claroline::getInstance()->notification;
		
		$date = isset( $args['date'] )
			  ?$args['date']
			  :$claroNotification->getLastActionBeforeLoginDate(claro_get_current_user_id())
			  ;
		$gid = 0;
		$result = array();
		foreach ( $claroNotification->getNotifiedCourses($date, claro_get_current_user_id()) as $cid )
		{
			foreach ( $claroNotification->getNotifiedTools($cid, $date, claro_get_current_user_id()) as $tid )
			{
				$result[$cid][get_module_label_from_tool_id($tid)] = $claroNotification->getNotifiedRessources($cid,$date,claro_get_current_user_id(),$gid,$tid);
			}
		}
		return $result;
	}
}