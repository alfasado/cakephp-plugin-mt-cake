<?php
function smarty_block_mtifuseragent( $args, $content, &$ctx, &$repeat ) {
    $app   = $ctx->stash( 'bootstrapper' );
    $like  = $args[ 'like' ];
    $wants = $args[ 'wants' ];
    if ( (! $like ) && (! $wants ) ) {
        return $ctx->_hdlr_if( $args, $content, $ctx, $repeat, TRUE );
    }
    if ( $app->get_agent( $wants, $like ) ) {
        return $ctx->_hdlr_if( $args, $content, $ctx, $repeat, TRUE );
    }
    return $ctx->_hdlr_if( $args, $content, $ctx, $repeat, FALSE );
}
?>