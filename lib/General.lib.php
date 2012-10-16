<?php
class General {
	    // Singleton instance
    private static $instance = false; // this class is a singleton

	static function getInstance(){
        if ( ! self::$instance )
        {
            self::$instance = new self;
        }

        return self::$instance;
	}
	
	static function getUserData() {
		$userData = claro_get_current_user_data();
		unset($userData['authSource'],
			  $userData['creatorId'],
			  $userData['lastLogin'],
			  $userData['mail'],
			  $userData['officialEmail'],
			  $userData['phone'],
			  $userData['picture']);
		$userData['platformName'] = get_conf('siteName', "Claroline");
		$userData['institutionName'] = get_conf('institution_name',"");
		$userData['platformTextAuth'] = trim(strip_tags(claro_text_zone::get_content("textzone_top.authenticated")));
		$userData['platformTextAnonym'] = trim(strip_tags(claro_text_zone::get_content("textzone_top.anonymous")));
		return $userData;
	}

	static function getCourseList(){
		FromKernel::uses('courselist.lib');
		$claroNotification = Claroline::getInstance()->notification;
		$date = $claroNotification->getLastActionBeforeLoginDate(claro_get_current_user_id());
		$courseList = array();
		$notifiedCourses = $claroNotification->getNotifiedCourses($date,claro_get_current_user_id());
		foreach (get_user_course_list(claro_get_current_user_id()) as $course){
			$course_data = claro_get_course_data($course['sysCode']);
			$course['officialEmail'] = $course_data['email'];
			$course['notified'] = in_array($course['sysCode'],$notifiedCourses);
			$courseList[] = $course;
		}
		return $courseList;
	}
	
	static function getCourseToolList(){
		FromKernel::uses('courselist.lib');
		$claroNotification = Claroline::getInstance()->notification;
		$date = $claroNotification->getLastActionBeforeLoginDate(claro_get_current_user_id());
		$course = array();
		$course['sysCode'] = claro_get_current_course_id();
		$course['isAnn'] = false;
		$course['annNotif'] = false;
		$course['isDnL'] = false;
		$course['dnlNotif'] = false;
		$notifiedTools = $claroNotification->getNotifiedTools(claro_get_current_course_id(),$date,claro_get_current_user_id());
		foreach(claro_get_course_tool_list($course['sysCode'],claro_get_current_user_profile_id_in_course(claro_get_current_course_id())) as $tool){ 
			switch($tool['label']){
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
	
	static function getUpdates(){
		$claroNotification = Claroline::getInstance()->notification;
		$gid = 0;
		$date = $claroNotification->getLastActionBeforeLoginDate(claro_get_current_user_id());
		$result = array();
		foreach($claroNotification->getNotifiedCourses($date, claro_get_current_user_id()) as $cid){
			foreach ( $claroNotification->getNotifiedTools($cid, $date, claro_get_current_user_id()) as $tid ) {
				$result[$cid][get_module_label_from_tool_id($tid)] = $claroNotification->getNotifiedRessources($cid,$date,claro_get_current_user_id(),$gid,$tid);
			}
		}
		return $result;
	}
}
?>