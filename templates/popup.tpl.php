<div id='content'>
	<div class="portlet">
		<h1><?php echo get_lang('Select your system') ?></h1>
	</div>
	<div id="choose">
		<a href='#android'>
		<div class='item_choose'>
			<div id='android_img'>
			</div>
			<div class='item_name'>
				Android
			</div>
		</div>
		</a>
		<a href='#wp'>
		<div class='item_choose'>
			<div id="windows_img">
			</div>
			<div class='item_name'>
				Windows Phone
			</div>
		</div>
		</a>
		<div class='item_choose'>
			<div id="appstore_img">
			</div>
			<div class='item_name'>
				iOS
			</div>
		</div>
	</div>
	
	<div id='android' class='portlet'><h1>Android<a class="go_home" href="#"><?php echo get_lang('Go up') ?></a></h1></div>
	<div class='section_content'>
		<img class='img_left' src='<?php echo get_module_url('MOBILE')?>/img/print_android2.png' height='200' alt='print 1' />
		<div class='text_right'>
			<div class='subsection_title'><?php echo get_lang('Configure the platform') ?></div>
			<p><?php
				echo get_lang('Once the app is installed on your smartphone from the store, you just need to configure the platform you\'ll use. To that, you can either enter the URL address into the field or click on "Browse..." option to go on the homepage of your site through a Web browser.');
			?></p>
			<p><?php echo get_lang('The address or this platform is :') ?><span class='important'><?php echo get_path( 'rootWeb' ); ?> </span> </p>
		</div>
	</div>
	<div class='section_content'>
	<div class='text_left'>
	<div class='subsection_title'><?php echo get_lang('Log in')?></div>
	<p><?php
		printf(get_lang('Once the app has validated the platform address, you can return to the homepage where a popup will ask your credentials. You must put exactly the same as when you use %s on your computer.'), get_conf('siteName'));
	?></p>
	</div>
	<img class='img_right' src='<?php echo get_module_url('MOBILE')?>/img/print_android1.png' height='200' alt='print 2' />
	</div>
	<?php echo PopupWindowHelper::windowClose() ?>
	<br />
	<div id='wp' class='portlet'><h1>Windows Phone<a class="go_home" href="#"><?php echo get_lang('Go up') ?></a></h1></div>
	<div class='section_content'>
		<img class='img_left' src='<?php echo get_module_url('MOBILE')?>/img/print_wp1.png' height='200' alt='print 1' />
		<div class='text_right'>
			<div class='subsection_title'><?php echo get_lang('Configure the platform') ?></div>
			<p><?php
				echo get_lang('Once the app is installed on your smartphone from the store, you just need to configure the platform you\'ll use. To that, you can either enter the URL address into the field or click on "Browse..." option to go on the homepage of your site through a Web browser.');
			?></p>
			<p><?php echo get_lang('The address or this platform is :') ?><span class='important'><?php echo get_path( 'rootWeb' ); ?> </span> </p>
		</div>
	</div>
	<div class='section_content'>
	<div class='text_left'>
	<div class='subsection_title'><?php echo get_lang('Log in')?></div>
	<p><?php
		printf(get_lang('Once the app has validated the platform address, you can pass to the second screen where you can enter your credentials. You must put exactly the same as when you use %s on your computer.'), get_conf('siteName'));
	?></p>
	</div>
	<img class='img_right' src='<?php echo get_module_url('MOBILE')?>/img/print_wp2.png' height='200' alt='print 2' />
	</div>
	<?php echo PopupWindowHelper::windowClose() ?>
</div>