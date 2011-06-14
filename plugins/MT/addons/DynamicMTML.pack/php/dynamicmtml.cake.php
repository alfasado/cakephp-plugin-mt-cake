<?php
    $plugin_path = dirname( __File__ ) . DIRECTORY_SEPARATOR;
    $mt_dir = dirname( dirname( dirname( $plugin_path ) ) );
    require_once( $plugin_path . 'dynamicmtml.util.php' );
    require_once( $plugin_path . 'dynamicmtml.php' );
    require_once( $mt_dir . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'MTUtil.php' );
    if (! isset( $mt_config ) ) $mt_config = $mt_dir . DIRECTORY_SEPARATOR . 'mt-config.cgi';
    global $mt;
    global $ctx;
    global $app;
    $ctx = NULL;
    $app = new DynamicMTML();
    $app->configure( $mt_config );
    $dynamic_config = $app->config;
    $app->stash( 'no_database', 1 );
    require_once( $plugin_path . 'mt.php' );
    $mt = new MT();
    $include_static   = $app->config( 'DynamicIncludeStatic' );
    $dynamicphpfirst  = $app->config( 'DynamicPHPFirst' );
    $allow_magicquote = $app->config( 'AllowMagicQuotesGPC' );
    if (! $allow_magicquote ) {
        if ( get_magic_quotes_gpc() ) {
            function strip_magic_quotes_slashes ( $arr ) {
                return is_array( $arr ) ?
                array_map( 'strip_magic_quotes_slashes', $arr ) :
                stripslashes( $arr );
            }
            $_GET = strip_magic_quotes_slashes( $_GET );
            $_POST = strip_magic_quotes_slashes( $_POST );
            $_REQUEST = strip_magic_quotes_slashes( $_REQUEST );
            $_COOKIE = strip_magic_quotes_slashes( $_COOKIE );
        }
    }
    if ( isset( $_SERVER[ 'REDIRECT_STATUS' ] ) ) {
        $status = $_SERVER[ 'REDIRECT_STATUS' ];
        if ( ( $status == 403 ) || ( $status == 404 ) ) {
            if ( isset( $_SERVER[ 'REDIRECT_QUERY_STRING' ] ) ) {
                if (! $_GET ) {
                    parse_str( $_SERVER[ 'REDIRECT_QUERY_STRING' ], $_GET );
                }
            }
            if (! $_POST ) {
                if ( $params = file_get_contents( "php://input" ) ) {
                    parse_str( $params, $_POST );
                }
            }
            $app->request_method = $_SERVER[ 'REDIRECT_REQUEST_METHOD' ];
            $app->mod_rewrite = 0;
        } else {
            $app->request_method = $_SERVER[ 'REQUEST_METHOD' ];
            $app->mod_rewrite = 1;
        }
    } else {
        $app->mod_rewrite = 1;
    }
    $app->run_callbacks( 'init_request' );
    $secure       = empty( $_SERVER[ 'HTTPS' ] ) ? '' : 's';
    $base         = "http{$secure}://{$_SERVER[ 'HTTP_HOST' ]}";
    $port         = (int) $_SERVER[ 'SERVER_PORT' ];
    if (! empty( $port ) && $port !== ( $secure === '' ? 80 : 443 ) ) $base .= ":$port";
    $request_uri = NULL;
    if ( isset( $_SERVER[ 'HTTP_X_REWRITE_URL' ] ) ) {
        // IIS.
        $request_uri  = $_SERVER[ 'HTTP_X_REWRITE_URL' ];
    } elseif ( isset( $_SERVER[ 'REQUEST_URI' ] ) ) {
        // Apache and others.
        $request_uri  = $_SERVER[ 'REQUEST_URI' ];
    } elseif ( isset( $_SERVER[ 'ORIG_PATH_INFO' ] ) ) {
        // IIS 5.0, PHP as CGI.
        $request_uri = $_SERVER[ 'ORIG_PATH_INFO' ];
        if (! empty( $_SERVER[ 'QUERY_STRING' ] ) ) {
            $request_uri .= '?' . $_SERVER[ 'QUERY_STRING' ];
        }
    }
    $root         = $app->chomp_dir( $_SERVER[ 'DOCUMENT_ROOT' ] );
    $ctime        = empty( $_SERVER[ 'REQUEST_TIME' ] )
                  ? time() : $_SERVER[ 'REQUEST_TIME' ];
    $request      = NULL;
    $text         = NULL;
    $param        = NULL;
    $orig_mtime   = NULL;
    $clear_cache  = NULL;
    $result_type  = NULL;
    $build_type   = NULL;
    $data         = NULL;
    $dynamicmtml  = FALSE;
    $is_secure    = NULL; if ( $secure ) { $is_secure = 1; }
    if (! isset( $extension ) ) $extension = '.html';
    if (! isset( $use_cache ) ) $use_cache = 0;
    if (! isset( $conditional ) ) $conditional = 0;
    if (! isset( $indexes ) ) $indexes = 'index.html';
    if (! isset( $size_limit ) ) $size_limit = 524288;
    if (! isset( $server_cache ) ) $server_cache = 7200;
    if (! isset( $excludes ) ) $excludes = 'php';
    if (! isset( $require_login ) ) $require_login = FALSE;
    if (! isset( $dynamic_caching ) ) $dynamic_caching = FALSE;
    if (! isset( $dynamic_conditional ) ) $dynamic_conditional = FALSE;
    if ( strpos( $request_uri, '?' ) ) {
        list( $request, $param ) = explode( '?', $request_uri );
        $app->stash( 'query_string', $param );
    } else {
        $request = $request_uri;
        $param = NULL;
    }
    $url = $base . $request_uri;
    // ========================================
    // Set File and Content_type
    // ========================================
    $file = $root . DIRECTORY_SEPARATOR . $request;
    $static_path = $app->__add_slash( $app->config( 'StaticFilePath' ) );
    $app->check_excludes( $file, $excludes, $mt_dir, $static_path );
    if (! is_null( $file ) ) {
        $pinfo = pathinfo( $file );
        if ( isset( $pinfo[ 'extension' ] ) ) {
            $extension = $pinfo[ 'extension' ];
        }
    }
    $contenttype = $app->get_mime_type( $extension );
    $type_text = $app->type_text( $contenttype );
    $path = preg_replace( '!(/[^/]*$)!', '', $request );
    $path .= '/';
    $script = preg_replace( '!(^.*?/)([^/]*$)!', '$2', $request );
    // ========================================
    // Include DPAPI
    // ========================================
    $force_compile = NULL;
    $args = array( 'conditional' => $conditional,
                   'use_cache' => $use_cache,
                   'root' => $root,
                   'plugin_path' => $plugin_path,
                   'file' => $file,
                   'base' => $base,
                   'path' => $path,
                   'script' => $script,
                   'request' => $request,
                   'param' => $param,
                   'is_secure' => $is_secure,
                   'url' => $url,
                   'contenttype' => $contenttype,
                   'extension' => $extension );
    $app->init( $args );
    $app->run_callbacks( 'pre_run', $mt, $ctx, $args );
    require_once $mt_dir . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'class.exception.php';
    if ( isset( $mt ) ) {
        $ctx =& $mt->context();
        $ctx->stash( 'no_database', 1 );
        $app->set_context( $mt, $ctx );
        $mt->load_plugin( $mt_dir . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'lib' );
        $mt->init_addons();
        $mt->init_plugins();
        //$mt->load_plugin( $plugin_path . 'tags' );
        $ctx->stash( 'callback_dir', $app->stash( 'callback_dir' ) );
        $ctx->stash( 'content_type', $contenttype );
        $base_original = $root;
        $request_original = $request;
        $app->run_callbacks( 'post_init', $mt, $ctx, $args );
        if ( ( $base_original != $root ) || ( $request_original != $request ) ) {
            $file = $root . DIRECTORY_SEPARATOR . $request;
            $file = $app->adjust_file( $file, $indexes );
            $app->stash( 'file', $file );
            $app->stash( 'root', $root );
            $app->stash( 'request', $request );
        }
        $cfg_forcecompile = $app->config( 'DynamicForceCompile' );
        if ( $cfg_forcecompile ) {
            $force_compile = 1;
        }
    }
    // ========================================
    // Run DynamicMTML
    // ========================================
    if ( isset( $mt ) && $force_compile ) {
        $app->stash( 'force_compile', 1 );
        $ctx->force_compile = TRUE;
    }
    return;
?>