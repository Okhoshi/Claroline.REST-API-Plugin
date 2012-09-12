<?php
class Announce {
	    // Singleton instance
    private static $instance = false; // this class is a singleton

	static function getInstance(){
        if ( ! self::$instance )
        {
            self::$instance = new self;
        }

        return self::$instance;
	}
	
	function getAnnounceList($cid){
		From::Module('CLANN')->uses('announcement.lib');
		$claroNotification = Claroline::getInstance()->notification;
		$date = $claroNotification->getLastActionBeforeLoginDate(claro_get_current_user_id());
		$annList = array();
		foreach ( announcement_get_item_list(array('course'=>$cid)) as $announce ) {
			$announce['notified'] = $claroNotification->isANotifiedRessource($cid,
				$date,
				claro_get_current_user_id(),
				claro_get_current_group_id(),
				get_tool_id_from_module_label('CLANN'),
				$announce['id'],
				false);
			$announce['visibility'] = ($announce['visibility'] != 'HIDE');
			$announce['cours']['sysCode'] = $cid;
			$announce['date'] = $announce['time'];
			$announce['ressourceId'] = $announce['id'];
			$announce['content'] = trim(strip_tags($announce['content']));
			unset($announce['id']);
			if(claro_is_allowed_to_edit() || $announce['visibility'])
				$annList[] = $announce;
		}
		return $annList;
	}
	
	function getSingleAnnounce($cid, $resourceId){
		$claroNotification = Claroline::getInstance()->notification;
		From::Module('CLANN')->uses('announcement.lib');
		$announce = announcement_get_item($resourceId,$cid);
		$announce['visibility'] = ($announce['visibility'] != 'HIDE');
		$announce['content'] = trim(strip_tags($announce['content']));
		$announce['cours']['sysCode'] = $cid;
		$announce['ressourceId'] = $announce['id'];
		$announce['date'] = $claroNotification->getLastActionBeforeLoginDate(claro_get_current_user_id());
		$announce['notified'] = $claroNotification->isANotifiedRessource($cid,
				$date,
				claro_get_current_user_id(),
				claro_get_current_group_id(),
				get_tool_id_from_module_label('CLANN'),
				$announce['id'],
				false);
		unset($announce['id']);
		return (claro_is_allowed_to_edit() || $announce['visibility'])?$announce:null;
	}
}
?>