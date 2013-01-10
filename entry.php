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
if ( get_conf('activeWebService',true) )
{
	
	$iOSimg = 'alt="Claroline Mobile on Apple iOS App Store" '
			.  'id="ios_bagde" width="150" height="50" />';

	$Andimg = 'alt="Claroline Mobile on Android Google Play Store" '
			.  'id="and_bagde" width="150" height="52" />';
	
	$WPimg = 'alt="Claroline Mobile on Microsoft WindowsPhone MarketPlace" '
			. 'id="wp_bagde" width="150" height="49" />';
	
	if ( get_conf('iOSAppReady', true) )
	{
		$iOSlink = '<a href="' . get_conf('iOSAppLink','') . '" alt="Claroline Mobile on Apple iOS App Store" target="_blank">'
		.		   '<img src="'.get_module_url('MOBILE').'/img/ios_badge.png" ' . $iOSimg . '</a>';
	}
	else
	{
		$iOSlink = '<img src="'.get_module_url('MOBILE').'/img/ios_badge_soon.png" ' . $iOSimg;
	}
	

	if ( get_conf('AndAppReady', true) )
	{
		$Andlink = '<a href="' . get_conf('AndAppLink','') . '" alt="Claroline Mobile on Android Google Play Store" target="_blank">' 
		. 		   '<img src="'.get_module_url('MOBILE').'/img/and_badge.png" ' . $Andimg . '</a>';
	}
	else
	{
		$Andlink = '<img src="'.get_module_url('MOBILE').'/img/and_badge_soon.png" ' . $Andimg;
	}
	
	if ( get_conf('WPAppReady', true) )
	{
		$WPlink = '<a href="' . get_conf('WPAppLink','') . '" alt="Claroline Mobile on Microsoft WindowsPhone MarketPlace" target="_blank">'
		. 		  '<img src="'.get_module_url('MOBILE').'/img/wp_badge.png" ' . $WPimg . '</a>';
	}
	else
	{
		$WPlink = '<img src="'.get_module_url('MOBILE').'/img/wp_badge_soon.png" ' . $WPimg;
	}
	
	$html = "\n\n"
	.	 '<div class="header">'
	.	 'Claroline Mobile'
	.	 '</div>'
	.	 "\n</br>" . get_lang('Get the Claroline application for your smartphone !') . "\n"
	.	 '<div style="text-align:center"></br>'
	.    $iOSlink . "\n"
	.    $Andlink . "\n"
	.    $WPlink . "\n"
	.	 '</div>'
	. 	"\n\n";


	$claro_buffer->append($html);
}