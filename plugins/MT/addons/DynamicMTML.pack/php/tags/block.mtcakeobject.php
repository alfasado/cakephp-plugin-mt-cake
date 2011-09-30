<?php
function smarty_block_mtcakeobject ( $args, $content, &$ctx, &$repeat ) {
    require_once( 'block.mtcakeloop.php' );
    return smarty_block_mtcakeloop( $args, $content, $ctx, $repeat );
}
?>