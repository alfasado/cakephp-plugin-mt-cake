<?php
function smarty_block_mtcakeloop ( $args, $content, &$ctx, &$repeat ) {
    $localvars = array( '__loop_keys', '__loop_values', '__out' );
    if (! isset( $content ) ) {
        $ctx->localize( $localvars );
        $vars = array();
        $name;
        $value = '';
        if ( isset( $args[ 'model' ] ) ) {
            $name = $args[ 'model' ];
        } else {
            if ( isset( $args[ 'name' ] ) ) {
                $name = $args[ 'name' ];
            }
        }
        if (! $name ) {
            $repeat = FALSE;
            return '';
        }
        $stash = '';
        if ( isset( $args[ 'stash' ] ) ) {
            $stash = $args[ 'stash' ];
        }
        if (! $stash ) $stash = $name;
        $objects = $ctx->stash( $stash );
        $loop = array();
        if ( $objects ) {
            foreach ( $objects as $obj ) {
                if ( isset( $obj[ $name ] ) ) {
                    array_push( $loop, $obj[ $name ] );
                } else {
                    array_push( $loop, $obj );
                }
            }
            $vars[ $name ] = $loop;
        }
        if ( isset( $vars[ $name ] ) )
            $value = $vars[ $name ];
        if ( !is_array( $value )
          && preg_match( '/^smarty_fun_[a-f0-9]+$/', $value ) ) {
            if ( function_exists( $value ) ) {
                ob_start();
                $value( $ctx, array() );
                $value = ob_get_contents();
                ob_end_clean();
            } else {
                $value = '';
            }
        }
        if ( !is_array( $value ) || ( 0 == count( $value ) ) ) {
            $repeat = FALSE;
            return '';
        }
        $sort = '';
        if ( isset ( $args[ 'sort_by' ] ) ) {
            $sort = $args[ 'sort_by' ];
        }
        $keys = array_keys( $value );
        if ( $sort ) {
            $sort = strtolower( $sort );
            if ( preg_match('/\bkey\b/', $sort ) ) {
                usort( $keys, create_function(
                    '$a,$b',
                    'return strcmp($a, $b);'
                ) );
            } elseif ( preg_match( '/\bvalue\b/', $sort ) ) {
                $sort_fn = '';
                foreach ( array_keys( $value ) as $key) {
                    $v = $value[ $key ];
                    $sort_fn .= "\$value['$key']='$v';";
                }
                if ( preg_match( '/\bnumeric\b/', $sort ) ) {
                    $sort_fn .= 'return $value[$a] === $value[$b] ? 0 : ($value[$a] > $value[$b] ? 1 : -1);';
                    $sorter = create_function(
                        '$a,$b',
                        $sort_fn );
                } else {
                    $sort_fn .= 'return strcmp($value[$a], $value[$b]);';
                    $sorter = create_function(
                        '$a,$b',
                        $sort_fn );
                }
                usort( $keys, $sorter );
            }
            if ( preg_match( '/\breverse\b/', $sort ) ) {
                $keys = array_reverse( $keys );
            }
        }
        $counter = 1;
        $ctx->stash( '__loop_values', $value );
        $ctx->stash( '__out', FALSE );
    } else {
        $counter = $ctx->__stash[ 'vars' ][ '__counter__' ] + 1;
        $keys = $ctx->stash( '__loop_keys' );
        $value = $ctx->stash( '__loop_values' );
        $out = $ctx->stash( '__out' );
        if (! isset( $keys ) || $keys == 0 ) {
            $ctx->restore( $localvars );
            $repeat = FALSE;
            if ( isset( $args[ 'glue' ]) && $out && !empty( $content ) )
                $content = $args[ 'glue' ] . $content;
            return $content;
        }
    }
    $key = array_shift( $keys );
    $this_value = $value[ $key ];
    $ctx->stash( '__loop_keys', $keys );
    $ctx->__stash[ 'vars' ][ '__counter__' ] = $counter;
    $ctx->__stash[ 'vars' ][ '__odd__' ] = ( $counter % 2 ) == 1;
    $ctx->__stash[ 'vars' ][ '__even__' ] = ( $counter % 2 ) == 0;
    $ctx->__stash[ 'vars' ][ '__first__' ] = $counter == 1;
    $ctx->__stash[ 'vars' ][ '__last__' ] = count( $value ) == 0;
    $ctx->__stash[ 'vars' ][ '__key__' ] = $key;
    $ctx->__stash[ 'vars' ][ '__value__' ] = $this_value;
    if ( is_array( $this_value ) && ( 0 < count( $this_value ) ) ) {
        require_once( "MTUtil.php" );
        if ( is_hash( $this_value ) ) {
            foreach ( array_keys( $this_value ) as $inner_key ) {
                $ctx->__stash[ 'vars' ][ strtolower( $inner_key ) ] = $this_value[ $inner_key ];
            }
        }
    }
    if (isset( $args[ 'glue' ] ) && !empty( $content ) ) {
        if ( $out )
            $content = $args[ 'glue' ] . $content;
        else
            $ctx->stash( '__out', TRUE );
    }
    if ( 0 === count( $keys ) )
        $ctx->stash( '__loop_keys', 0 );
    $repeat = TRUE;
    return $content;
}
?>