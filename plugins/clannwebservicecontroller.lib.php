<?php
/**
 * Web Service Controller - CLANN library
 *
 * @copyright   2001-2013 Universite Catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package     MOBILE
 * @author      Quentin Devos <q.devos@student.uclouvain.be>
 */
class CLANNWebServiceController
{

	/**
	 * Returns all the announces of a course.
	 * @throws InvalidArgumentException if the $cid in not provided.
	 * @webservice{/module/MOBILE/CLANN/getResourcesList/cidReq}
	 * @ws_arg{Method,getResourcesList}
	 * @ws_arg{cidReq,SYSCODE of requested cours}
	 * @return array of Announce object
	 */
	function getResourcesList()
	{
		$cid = claro_get_current_course_id();
		if ( $cid == null )
		{
			throw new InvalidArgumentException('Missing cid argument!');
		}

		From::Module('CLANN')->uses('announcement.lib');
		$claroNotification = Claroline::getInstance()->notification;
		$date = $claroNotification->getLastActionBeforeLoginDate(claro_get_current_user_id());
		$annList = array();
		
		$d = new DateTime($date);
		$d->sub(new DateInterval('PT1M'));

		foreach ( announcement_get_item_list(array('course'=>$cid)) as $announce )
		{
			$notified = $claroNotification->isANotifiedRessource($cid,
						$date,
						claro_get_current_user_id(),
						claro_get_current_group_id(),
						get_tool_id_from_module_label('CLANN'),
						$announce['id'],
						false);
		
			$announce['notifiedDate'] = $notified
										?$date
										:$announce['time'];
			$announce['seenDate'] = $d->format('Y-m-d H:i');
			$announce['visibility'] = ($announce['visibility'] != 'HIDE');
			$announce['cours']['sysCode'] = $cid;
			$announce['date'] = $announce['time'];
			$announce['resourceId'] = $announce['id'];
			$announce['content'] = trim(strip_tags($announce['content']));
			unset($announce['id'], $announce['visibleFrom'], $announce['visibleUntil']);
				
			if ( claro_is_allowed_to_edit() || $announce['visibility'] )
			{
				$annList[] = $announce;
			}
		}
		return $annList;
	}

	/**
	 * Returns a single resquested announce.
	 * @param array $args must contain 'resID' key with the resource identifier of the requested resource
	 * @throws InvalidArgumentException if one of the paramaters is missing
	 * @webservice{/module/MOBILE/CLANN/getSingleResource/cidReq/resId}
	 * @ws_arg{Method,getSingleResource}
	 * @ws_arg{cidReq,SYSCODE of requested cours}
	 * @ws_arg{resID,Resource Id of requested resource}
	 * @return announce object (can be null if not visible for the current user)
	 */
	function getSingleResource( $args )
	{
		$resourceId = isset( $args['resID'] )
			?$args['resID']
			:null
			;
		$cid = claro_get_current_course_id();
		
		if ( $cid == null || $resourceId == null )
		{
			throw new InvalidArgumentException('Missing cid or resourceId argument!');
		}

		$claroNotification = Claroline::getInstance()->notification;
		$date = $claroNotification->getLastActionBeforeLoginDate(claro_get_current_user_id());
		
		$d = new DateTime($date);
		$d->sub(new DateInterval('PT1M'));

		From::Module('CLANN')->uses('announcement.lib');

		if ( $announce = $this->announcement_get_item($resourceId,$cid) )
		{
			$notified = $claroNotification->isANotifiedRessource($cid,
					$date,
					claro_get_current_user_id(),
					claro_get_current_group_id(),
					get_tool_id_from_module_label('CLANN'),
					$announce['id'],
					false);
		
			$announce['visibility'] = ($announce['visibility'] != 'HIDE');
			$announce['content'] = trim(strip_tags($announce['content']));
			$announce['cours']['sysCode'] = $cid;
			$announce['resourceId'] = $announce['id'];
			$announce['date'] = $announce['time'];
			$announce['notifiedDate'] = $notified
										?$date
										:$announce['time'];
			$announce['seenDate'] = $d->format('Y-m-d H:i');
			unset($announce['id']);
			
			return (claro_is_allowed_to_edit() || $announce['visibility'])
				?$announce
				:null
				;
		}
		else
		{
			throw new RuntimeException('Resource not found', 404);
		}
	}
	
	function announcement_get_item($announcement_id, $course_id=NULL)
	{
		$tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));

		$sql = "SELECT                id,
									  title,
					   contenu     AS content,
						   temps   AS `time`,
									  visibility,
					   ordre       AS rank
				FROM  `" . $tbl['announcement'] . "`
				WHERE id=" . (int) $announcement_id ;

		$announcement = claro_sql_query_get_single_row($sql);

		if ($announcement) return $announcement;
		else               return claro_failure::set_failure('ANNOUNCEMENT_UNKNOW');
	}
}
