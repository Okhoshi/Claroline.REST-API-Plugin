<?php
/**
 * CLAROLINE Applet
 *
 * @author Loic Fortemps
 *
 * @package MOBILE
 *
 */
$tlabelReq = 'MOBILE';
require_once dirname( __FILE__ ) . '/../../claroline/inc/claro_init_global.inc.php';

Claroline::initDisplay(Claroline::POPUP);
CssLoader::getInstance()->load('mobile', 'all');

$pageTitle = array('mainTitle' => get_lang('Mobile Apps Configuration'), 'subTitle' => get_lang('Configuration helper'));

ClaroBreadCrumbs::getInstance()->append( $pageTitle[ 'mainTitle' ] , $_SERVER[ 'PHP_SELF' ] );
ClaroBreadCrumbs::getInstance()->append( $pageTitle[ 'subTitle' ] );

$template = new ModuleTemplate( $tlabelReq, 'popup.tpl.php' );

Claroline::getInstance()->display->body->appendContent( claro_html_tool_title( $pageTitle ) . $template->render() );
echo Claroline::getInstance()->display->render();
?>