<?php
/**
 * CLAROLINE Applet
 *
 * @author Quentin Devos <q.devos@student.uclouvain.be>
 *
 * @package MOBILE
 *
 */

if ( count( get_included_files() ) == 1 ) die( '---' );


$tlabelReq = 'MOBILE';
if(get_conf('activeWebService',true)){
	$html = "\n\n"
	.	 '<div class="header">'
	.	 'Claroline Mobile'
	.	 '</div>'
	.	 "\n</br>" . get_lang('Get the Claroline application for your smartphone soon !') . "\n"
	.	 '<div style="text-align:center"></br>'
	.	 '<a href="' . get_conf('iOSAppLink','') . '" alt="Claroline Mobile on Apple iOS App Store">'
	.    '<img src="'.get_module_url('MOBILE').'/img/ios_badge.png" alt="Claroline Mobile on Apple iOS App Store" id="ios_bagde" width="150" height="50" /></a>' . "\n"
	.	 '<a href="' . get_conf('AndAppLink','') . '" alt="Claroline Mobile on Android Google Play Store">'
	.    '<img src="'.get_module_url('MOBILE').'/img/and_badge.png" alt="Claroline Mobile on Android Google Play Store" id="and_bagde" width="150" height="52" /></a>' . "\n"
	.	 '<a href="' . get_conf('WPAppLink','') . '" alt="Claroline Mobile on Microsoft WindowsPhone MarketPlace">'
	.    '<img src="'.get_module_url('MOBILE').'/img/wp_badge.png" alt="Claroline Mobile on Microsoft WindowsPhone MarketPlace" id="wp_bagde" width="150" height="49" /></a>' . "\n"
	.	 '</div>'
	. 	"\n\n";


	$claro_buffer->append($html);
}


?>