<?php
/**
 * Web Service Controller - CLANN library
 *
 * @copyright   2001-2013 Universite Catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package     MOBILE
 * @author      Quentin Devos <q.devos@student.uclouvain.be>
 */
class CLANNWebServiceController {
	
	/**
	 * Returns all the announces of a course.
	 * @param string $cid unique identifier of requested course
	 * @throws InvalidArgumentException if the $cid in not provided.
	 * @webservice /module/MOBILE/CLANN/getResourcesList/cidReq
	 * @ws_arg{Method,getResourcesList}
	 * @ws_arg{cidReq,SYSCODE of requested cours}
	 * @return array of Announce object
	 */
	static function getResourcesList($cid){

		if($cid == null){
			throw new InvalidArgumentException('Missing cid argument!');
		}

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
	
	/**
	 * Returns a single resquested announce.
	 * @param string $cid unique identifier of requested course
	 * @param array $args must contain 'resID' key with the resource identifier of the requested resource
	 * @throws InvalidArgumentException if one of the paramaters is missing
	 * @webservice /module/MOBILE/CLANN/getSingleResource/cidReq/resID
	 * @ws_arg{Method,getSingleResource}
	 * @ws_arg{cidReq,SYSCODE of requested cours}
	 * @ws_arg{resID,Resource Id of requested resource}
	 * @return announce object (can be null if not visible for the current user)
	 */
	static function getSingleResource($cid, $args){
		$resourceId = isset($args['resID'])?$args['resID']:null;
		
		if($cid == null || $resourceId == null){
			throw new InvalidArgumentException('Missing cid or resourceId argument!');
		}
		
		$claroNotification = Claroline::getInstance()->notification;
		$date = $claroNotification->getLastActionBeforeLoginDate(claro_get_current_user_id());

		From::Module('CLANN')->uses('announcement.lib');
		
		if($announce = announcement_get_item($resourceId,$cid)){
			$announce['visibility'] = ($announce['visibility'] != 'HIDE');
			$announce['content'] = trim(strip_tags($announce['content']));
			$announce['cours']['sysCode'] = $cid;
			$announce['ressourceId'] = $announce['id'];
			$announce['date'] = $date;
			$announce['notified'] = $claroNotification->isANotifiedRessource($cid,
					$date,
					claro_get_current_user_id(),
					claro_get_current_group_id(),
					get_tool_id_from_module_label('CLANN'),
					$announce['id'],
					false);
			unset($announce['id']);
			return (claro_is_allowed_to_edit() || $announce['visibility'])?$announce:null;
		} else {
			throw new RuntimeException('Resource not found');
		}
	}
}
?>