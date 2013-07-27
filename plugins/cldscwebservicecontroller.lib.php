<?php
/**
 * Web Service Controller - CLDSC library
 *
 * @copyright   2001-2013 Universite Catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package     MOBILE
 * @author      Quentin Devos <q.devos@student.uclouvain.be>
 */
class CLDSCWebServiceController
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
		
		From::Module('CLDSC')->uses('courseDescription.lib', 'courseDescription.class');
		
		$dscList = array();
		
		$array = course_description_get_item_list($cid);
		if ( count( $array ) )
		{
			$lastCat = max(array_map(function($row) { return $row['category']; }, $array)) + 1;
		}
		
		foreach ( $array as $item )
		{
			if ( $item['category'] == -1 )
			{
				$item['title'] = get_lang('Other');
				$item['category'] = $lastCat;
			}
			$item['content'] = trim(strip_tags($item['content']));
			$item['visibility'] = ($item['visibility'] != 'HIDE');
			$item['resourceId'] = $item['id'];
			unset($item['id']);
			
			if ( claro_is_allowed_to_edit() || $item['visibility'] )
			{
				$dscList[] = $item;
			}
		}
		return $dscList;
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

		From::Module('CLDSC')->uses('courseDescription.lib', 'courseDescription.class');

		$result;
		
		foreach ( course_description_get_item_list($cid) as $item )
		{
			if ( claro_is_allowed_to_edit() || $item['visibility'] != 'HIDE' )
			{
				if ( $item['id'] == $resourceId )
				{
					$result = $item;
					break;
				}
			}
		}
		if ( $result != null )
		{
			$result['content'] = trim(strip_tags($result['content']));
			$result['visibility'] = ($result['visibility'] != 'HIDE');
			$result['resourceId'] = $result['id'];
			unset($result['id']);
			return $result;
		}
		else
		{
			throw new RuntimeException('Resource not found', 404);
		}
	}
}
