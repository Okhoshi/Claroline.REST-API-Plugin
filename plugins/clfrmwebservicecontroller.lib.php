<?php
/**
 * Web Service Controller - CLFRM library
 *
 * @copyright   2001-2013 Universite Catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package     MOBILE
 * @author      Quentin Devos <q.devos@student.uclouvain.be>
 */
class CLFRMWebServiceController
{

	/**
	 * Returns all the forums of a course.
	 * @throws InvalidArgumentException if the $cid in not provided.
	 * @webservice{/module/MOBILE/CLFRM/getResourcesList/cidReq}
	 * @ws_arg{Method,getResourcesList}
	 * @ws_arg{cidReq,SYSCODE of requested cours}
	 * @return array of Forums object
	 */
	function getResourcesList()
	{
		$cid = claro_get_current_course_id();
		if ( $cid == null )
		{
			throw new InvalidArgumentException('Missing cid argument!');
		}
		
		FromKernel::uses('forum.lib');
		$claroNotification = Claroline::getInstance()->notification;
		$date = $claroNotification->getLastActionBeforeLoginDate(claro_get_current_user_id());
		
		$d = new DateTime($date);
		$d->sub(new DateInterval('PT1M'));
		
		$categories = get_category_list();
		
		$list = array();
		
		foreach ( get_forum_list() as $item )
		{
			if ( $item['cat_id'] == GROUP_FORUMS_CATEGORY )
			{
				continue;
			}
						
			$item['resourceId'] = $item['forum_id'];
			$item['title'] = $item['forum_name'];
			
			foreach ( $categories as $cat )
			{
				if ( $cat['cat_id'] == $item['cat_id'] )
				{
					$item['cat_title'] = $cat['cat_title'];
					$item['cat_order'] = $cat['cat_order'];
					break;
				}
			}
			
			$item['topics'] = array();
			$topics = new topicLister($item['forum_id'], 0, $item['forum_topics']);
			foreach ( $topics->get_topic_list() as $topic )
			{
				$topic['resourceId'] = $topic['topic_id'];
				$topic['title'] = $topic['topic_title'];
				$topic['poster_firstname'] = $topic['prenom'];
				$topic['poster_lastname'] = $topic['nom'];
				$topic['date'] = $topic['topic_time'];
				
				$topic['posts'] = array();
				$posts = new postLister($topic['topic_id'], 0, $topic['topic_replies'] + 1);
				foreach ( $posts->get_post_list() as $post )
				{
				
					$notified = $claroNotification->isANotifiedRessource($cid,
									$date,
									claro_get_current_user_id(),
									claro_get_current_group_id(),
									get_tool_id_from_module_label('CLFRM'),
									$item['forum_id'] . '-' . $topic['topic_id'] . '-' . $post['post_id'],
									false);
									
					$post['notifiedDate'] = $notified
											?$date
											:$post['post_time'];
				
					$post['seenDate'] = $d->format('Y-m-d H:i');
					$post['post_text'] = trim(strip_tags($post['post_text']));
					$post['resourceId'] = $post['post_id'];
					$post['date'] = $post['post_time'];
				
					unset($post['post_id']);
					unset($post['topic_id']);
					unset($post['forum_id']);
					unset($post['poster_id']);
					unset($post['post_time']);
					unset($post['poster_ip']);
				
					$topic['posts'][] = $post;
				}
				
				unset($topic['topic_id']);
				unset($topic['topic_title']);
				unset($topic['topic_poster']);
				unset($topic['topic_time']);
				unset($topic['topic_replies']);
				unset($topic['topic_last_post_id']);
				unset($topic['forum_id']);
				unset($topic['topic_notify']);
				unset($topic['nom']);
				unset($topic['prenom']);
				unset($topic['post_time']);

				$item['topics'][] = $topic;
			}
			
			unset($item['forum_id']);
			unset($item['forum_name']);
			unset($item['forum_moderator']);
			unset($item['forum_topics']);
			unset($item['forum_posts']);
			unset($item['forum_last_post_id']);
			unset($item['forum_type']);
			unset($item['group_id']);
			unset($item['poster_id']);
			unset($item['post_time']);
			
			$list[] = $item;
		}
		return $list;
	}

	/**
	 * Returns a single resquested topic.
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

		FromKernel::uses('forum.lib');
		$claroNotification = Claroline::getInstance()->notification;
		$date = $claroNotification->getLastActionBeforeLoginDate(claro_get_current_user_id());

		$d = new DateTime($date);
		$d->sub(new DateInterval('PT1M'));
		
		$item = null;
		
		foreach ( get_forum_list() as $forum ) {
			if ( $forum['forum_id'] == $resourceID ) {
				$item = $forum;
				break;
			}
		}
		
		if ( $item )
		{
			$item['resourceId'] = $item['forum_id'];
			$item['title'] = $item['forum_name'];
			
			foreach ( get_category_list as $cat )
			{
				if ( $cat['cat_id'] == $item['cat_id'] )
				{
					$item['cat_title'] = $cat['cat_title'];
					$item['cat_order'] = $cat['cat_order'];
					break;
				}
			}
			
			$item['topics'] = array();
			$topics = new topicLister($item['forum_id'], 0, $item['forum_topics']);
			foreach ( $topics->get_topic_list() as $topic )
			{
				$topic['resourceId'] = $topic['topic_id'];
				$topic['title'] = $topic['topic_title'];
				$topic['poster_firstname'] = $topic['prenom'];
				$topic['poster_lastname'] = $topic['nom'];
				$topic['date'] = $topic['topic_time'];
				
				$topic['posts'] = array();
				$posts = new postLister($topic['topic_id'], 0, $topic['topic_replies'] + 1);
				foreach ( $posts->get_post_list() as $post )
				{
				
					$notified = $claroNotification->isANotifiedRessource($cid,
									$date,
									claro_get_current_user_id(),
									claro_get_current_group_id(),
									get_tool_id_from_module_label('CLFRM'),
									$item['forum_id'] . '-' . $topic['topic_id'] . '-' . $post['post_id'],
									false);
									
					$post['notifiedDate'] = $notified
											?$date
											:$post['post_time'];
				
					$post['seenDate'] = $d->format('Y-m-d H:i');
					$post['post_text'] = trim(strip_tags($post['post_text']));
					$post['resourceId'] = $post['post_id'];
					$post['date'] = $post['post_time'];
				
					unset($post['post_id']);
					unset($post['topic_id']);
					unset($post['forum_id']);
					unset($post['poster_id']);
					unset($post['post_time']);
					unset($post['poster_ip']);
				
					$topic['posts'][] = $post;
				}
				
				unset($topic['topic_id']);
				unset($topic['topic_title']);
				unset($topic['topic_poster']);
				unset($topic['topic_time']);
				unset($topic['topic_replies']);
				unset($topic['topic_last_post_id']);
				unset($topic['forum_id']);
				unset($topic['topic_notify']);
				unset($topic['nom']);
				unset($topic['prenom']);
				unset($topic['post_time']);

				$item['topics'][] = $topic;
			}
			
			unset($item['forum_id']);
			unset($item['forum_name']);
			unset($item['forum_moderator']);
			unset($item['forum_topics']);
			unset($item['forum_posts']);
			unset($item['forum_last_post_id']);
			unset($item['forum_type']);
			unset($item['group_id']);
			unset($item['poster_id']);
			unset($item['post_time']);
			
			return $item;
		}
		else
		{
			throw new RuntimeException('Resource not found', 404);
		}
	}
}
