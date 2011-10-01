# CakePHP plugin MTCake

## Synopsis

MTCake is template engine for CakePHP. Powerd by Movable Type Open Source and DynamicMTML.

## Copyright
+ [CakePHP(tm)](http://cakephp.org) Copyright 2005-2010, Cake Software Foundation, Inc.
+ [Movable Type (r) Open Source](https://github.com/movabletype/movabletype) (C) 2001-2011 Six Apart, Ltd.
+ [DynamicMTML](https://github.com/alfasado/DynamicMTML) Copyright 2010-2011 Alfasado Inc.

## System Requirements

+ CakePHP 1.3.10


## Getting Started
+ Edit plugins/MT/mt-config.cgi(CakePHP's Database's settings).
+ Put MT to the CakePHP's plugin directory( plugins/MT ).
+ Put app\_controller.php to the CakePHP's app directory( app/app\_controller.php ).
+ Put webroot/templates\_c and webroot/cache to the CakePHP's webroot directory and set write permissions to directories.


## Tutorial
### See [1.2 Blog Tutorial](http://book.cakephp.org/view/219/Blog)

+ [Creating the Blog Database](http://book.cakephp.org/view/330/Creating-the-Blog-Database)
+ [Cake Database Configuration](http://book.cakephp.org/view/331/Cake-Database-Configuration)
+ [Create a Post Model](http://book.cakephp.org/view/334/Create-a-Post-Model)
+ [Create a Posts Controller](http://book.cakephp.org/view/335/Create-a-Posts-Controller)
+ [Creating Post Views](http://book.cakephp.org/view/336/Creating-Post-Views)


## Original Code
    <!-- File: /app/controllers/posts_controller.php -->
    <?php
    class PostsController extends AppController {
        var $name = 'Posts';
        function index() {
             $this->set('posts', $this->Post->find('all'));
        }
        function view($id = null) {
            $this->Post->id = $id;
            $this->set('post', $this->Post->read());
        }
    }
    ?>

    <!-- File: /app/views/posts/index.ctp -->
    <h1>Blog posts</h1>
    <table>
        <tr>
            <th>Id</th>
            <th>Title</th>
            <th>Created</th>
        </tr>
        <!-- Here is where we loop through our $posts array, printing out post info -->
        <?php foreach ($posts as $post): ?>
        <tr>
            <td><?php echo $post['Post']['id']; ?></td>
            <td>
                <?php echo $html->link($post['Post']['title'], 
    array('controller' => 'posts', 'action' => 'view', $post['Post']['id'])); ?>
            </td>
            <td><?php echo $post['Post']['created']; ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <!-- File: /app/views/posts/view.ctp -->
    <h1><?php echo $post['Post']['title']?></h1>
    <p><small>Created: <?php echo $post['Post']['created']?></small></p>
    <p><?php echo $post['Post']['body']?></p>

## Code with MTCake
    <!-- File: /app/controllers/posts_controller.php -->
    <?php
    class PostsController extends AppController {
        var $name = 'Posts';
        function index() {
            $ctx = $this->ctx;
            $ctx->__stash[ 'vars' ][ 'page_title' ] = 'Blog posts';
            $ctx->stash( 'Post', $this->Post->find( 'all' ) );
            // => <mt:cake:loop model="Post">~</mt:cake:loop>
            // or $ctx->stash( 'posts', $this->Post->find( 'all' ) ); 
            // => <mt:cake:loop model="Post" stash="posts">~</mt:cake:loop>
        }
        function view( $id = null ) {
            $ctx = $this->ctx;
            $this->Post->id = $id;
            $ctx->stash( 'Post', $this->Post->read() );
        }
    }
    ?>

    <!-- File: /app/views/posts/index.ctp -->
    <h1><mt:var name="page_title" escape="html"></h1>
    <!-- Here is where we loop through our posts array, printing out post info -->
    <mt:cake:loop model="Post">
    <mt:ignore>
        or <mt:cake:loop model="Post" stash="posts">
    </mt:ignore>
    <mt:if name="__first__">
    <table>
        <tr>
            <th>Id</th>
            <th>Title</th>
            <th>Created</th>
        </tr>
    </mt:if>
        <tr>
            <td><mt:var name="id"></td>
            <td>
                <a href="./view/<mt:var name="id">"><mt:var name="title" escape="html"></a>
            </td>
            <td><mt:var name="created"></td>
        </tr>
    <mt:if name="__last__">
    </table>
    </mt:if>
    </mt:cake:loop>

    <!-- File: /app/views/posts/view.ctp -->
    <mt:cake:object model="Post">
    <h1><mt:var name="title" escape="html"></h1>
    <p><small>Created: <mt:var name="created"></small></p>
    <p><mt:var name="body"></p>
    </mt:cake:object>