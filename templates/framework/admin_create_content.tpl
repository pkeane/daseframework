{extends file="framework/bootstrap.tpl"}

{block name="content"}
<div>
	<div class="controls">
		<a href="items">view items</a> |
		<a href="set/list">view sets</a> |
		create content |
		<a href="set/form">create a set</a>
	</div>
	<h1>Create Content</h1>
	<form action="admin/create" method="post" enctype="multipart/form-data">
		<label for="title">title</label>
		<input type="text" name="title"/>
		<label for="body">body</label>
		<textarea name="body"></textarea>
		<label for="uploaded_file">select a file</label>
		<input type="file" name="uploaded_file"/>
		<p>
		<input type="submit" value="create/upload"/>
		</p>
	</form>
</div>
{/block}
