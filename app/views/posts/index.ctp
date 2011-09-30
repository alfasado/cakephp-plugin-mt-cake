<mt:ignore>/app/views/posts/index.ctp</mt:ignore>
<h1><mt:var name="page_title" escape="html"></h1>

<mt:cake:loop model="Post">
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

<mt:ignore>
<mt:loop name="posts">
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
</mt:loop>
</mt:ignore>
