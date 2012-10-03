<span>
    <a class="claroCmd" href="<?php echo htmlspecialchars( Url::Contextualize( $_SERVER['PHP_SELF'].'?cmd=AddForm' ) ); ?>">
        <img src="<?php echo get_icon_url( 'import' ); ?>" alt="<?php echo get_lang( 'import'); ?>"/>
        <?php echo get_lang( 'Add a library' ); ?>
    </a>
</span><br />

<table class="claroTable emphaseLine">
    <thead>
        <tr class="headerX">
            <th align="center"><?php echo get_lang( 'Library Name' ); ?></th>
            <th align="center"><?php echo get_lang( 'Delete' ); ?></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach( $this->libList as $lib ) : ?>
        <tr>
            <td>
                <?php echo $lib['lib_name']; ?></td>
			<?php if(!in_array($lib['lib_name'], array('General','Announce','Documents'))) : ?>
			<td><a class="claroCmd" href="<?php echo $_SERVER['PHP_SELF'] .'?cmd=DeleteLib&amp;libFile=' . rawurlencode($lib['lib_file']); ?>">
				<img src="<?php echo get_icon('delete'); ?>" alt="<?php echo get_lang('Delete'); ?>" /></a>
			</td>
			<?php else : ?>
			<td />
			<?php endif; ?>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>