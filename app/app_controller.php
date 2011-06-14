<?php
class Appcontroller extends Controller {
    function beforeFilter() {
        global $mt_root_dir;
        global $run_cake;
        $run_cake = 1;
        $mt_root_dir = dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'MT' . DIRECTORY_SEPARATOR;
        $mt_dir = $mt_root_dir;
        $blog_id = NULL;
        require_once( $mt_dir . DIRECTORY_SEPARATOR . 'addons' . DIRECTORY_SEPARATOR . 'DynamicMTML.pack' .
                                DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'dynamicmtml.cake.php' );
        $this->ctx  = $ctx;
        $this->mt   = $mt;
        $this->app  = $app;
        $this->args = $args;
        $app->run_callbacks( 'before_filter', $mt, $ctx, $args );
    }
    function afterFilter() {
        $ctx  = $this->ctx;
        $app  = $this->app;
        if (! $ctx ) return;
        if (! $app ) return;
        $mt   = $this->mt;
        $args = $this->args;
        $text = $this->output;
        $app->run_callbacks( 'after_filter', $mt, $ctx, $args, $text );
        $_var_compiled = '';
        if (! $ctx->_compile_source( 'evaluated template', $text, $_var_compiled ) ) {
            echo 'Error compiling template.';
            exit();
        }
        ob_start();
        $ctx->_eval( '?>' . $_var_compiled );
        $text = ob_get_contents();
        ob_end_clean();
        $app->run_callbacks( 'build_page', $mt, $ctx, $args, $text );
        $this->output = $text;
    }
}
?>