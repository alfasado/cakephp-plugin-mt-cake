<mt:ignore>/app/views/posts/view.ctp</mt:ignore>

<mt:cake:object model="Post">
<h1><mt:var name="title" escape="html"></h1>
<p><small>Created: <mt:var name="created"></small></p>
<p><mt:var name="body"></p>
</mt:cake:object>