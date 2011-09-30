<?php
class PostsController extends AppController {
    var $name = 'Posts';
    function index() {
        $this->set( 'posts', $this->Post->find( 'all' ) );
        $ctx = $this->ctx;
        $ctx->__stash[ 'vars' ][ 'page_title' ] = 'Blog posts';
        $ctx->stash( 'Post', $this->Post->find( 'all' ) );
        // $ctx->__stash[ 'vars' ][ 'foo' ] = 'bar';
        
        // $all_posts = $this->Post->find( 'all' );
        // $loop = array();
        // if ( $all_posts ) {
        //     foreach ( $all_posts as $post ) {
        //         array_push( $loop, $post[ 'Post' ] );
        //     }
        // }
        // $ctx->__stash[ 'vars' ][ 'posts' ] = $loop;
    }
    function view( $id = null ) {
        $ctx = $this->ctx;
        $this->Post->id = $id;
        // $this->set( 'post', $this->Post->read() );
        $ctx->stash( 'Post', $this->Post->read() );
    }
}
?>