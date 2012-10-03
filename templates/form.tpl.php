<h4><?php echo get_lang( 'Add WebService Libraries' );?></h4>
    <form name="libFiles" action="<?php echo $_SERVER['PHP_SELF'];?>" method="post"  enctype="multipart/form-data">
    <input type="hidden" name="cmd" value="AddLib" />
	
	<label for="libFile"><?php echo get_lang('Choose File');?>:</label><br />
    <input type="file" name="libFile" id="libFile" /><br />
	
    <input type="submit" name="submit" value="<?php echo get_lang('Ok');?>" />
        
    <a href="<?php echo $_SERVER['PHP_SELF']; ?>">
        <input type="button" name="cancel" value="<?php echo get_lang('Cancel');?>" onclick="window.location=''<?php echo $_SERVER['PHP_SELF'];?>" />
	</a>
	</form>