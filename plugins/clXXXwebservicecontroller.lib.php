<?php
/**
 * Web Service Controller - CLXXX library
 *
 * @copyright   2001-2013 Universite Catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package     MOBILE
 * @author      Quentin Devos <q.devos@student.uclouvain.be>
 */
class CLXXXWebServiceController
{

	/**
	 * Returns all the descriptions of a course.
	 * @throws InvalidArgumentException if the $cid in not provided.
	 * @webservice{/module/MOBILE/CLANN/getResourcesList/cidReq}
	 * @ws_arg{Method,getResourcesList}
	 * @ws_arg{cidReq,SYSCODE of requested cours}
	 * @return array of Descriptions object
	 */
	function getResourcesList()
	{
		$cid = claro_get_current_course_id();
		if ( $cid == null )
		{
			throw new InvalidArgumentException('Missing cid argument!');
		}
		
		From::Module('CLXXX')->uses('');
		$claroNotification = Claroline::getInstance()->notification;
		$date = $claroNotification->getLastActionBeforeLoginDate(claro_get_current_user_id());
		
		$list = array();
		
		foreach (  as $item )
		{
			$notified = $claroNotification->isANotifiedRessource($cid,
							$date,
							claro_get_current_user_id(),
							claro_get_current_group_id(),
							get_tool_id_from_module_label('CLXXX'),
							$item['id'],
							false);
		
			$item['notifiedDate'] = $notified
									?$date
									:null;
			
			$item['resourceId'] = $item['id'];
			unset($item['id']);
			
			if ( claro_is_allowed_to_edit() || $item['visibility'] )
			{
				$list[] = $item;
			}
		}
		return $list;
	}

	/**
	 * Returns a single resquested decsription.
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

		From::Module('CLXXX')->uses('');
		$claroNotification = Claroline::getInstance()->notification;
		$date = $claroNotification->getLastActionBeforeLoginDate(claro_get_current_user_id());

		if ( $item = )
		{
			$notified = $claroNotification->isANotifiedRessource($cid,
					$date,
					claro_get_current_user_id(),
					claro_get_current_group_id(),
					get_tool_id_from_module_label('CLANN'),
					$item['id'],
					false);
		
			$item['visibility'] = ($item['visibility'] != 'HIDE');
			$item['resourceId'] = $item['id'];
			$item['notifiedDate'] = $notified
									?$date
									:null;
			unset($item['id']);
			
			return (claro_is_allowed_to_edit() || $item['visibility'])
				?$item
				:null
				;
		}
		else
		{
			throw new RuntimeException('Resource not found', 404);
		}
	}
}
