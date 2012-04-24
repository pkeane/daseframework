{extends file="framework/bootstrap.tpl"}

{block name="content"}
<div>
	<div class="controls">
		<a href="item/{$item->id}">view item</a>
	</div>
	<h1>Edit Item</h1>
	<form action="item/{$item->id}/edit" method="post">
		<label for="title">title</label>
		<input type="text" name="title" value="{$item->title}"/>
		<label for="body">body</label>
		<textarea name="body">{$item->body}</textarea>

		<label for="meta1">meta1</label>
		<input type="text" name="meta1" value="{$item->meta1}"/>

		<label for="meta2">meta2</label>
		<input type="text" name="meta2" value="{$item->meta2}"/>

		<label for="meta3">meta3</label>
		<input type="text" name="meta3" value="{$item->meta3}"/>

		<p>
		<input type="submit" value="update"/>
		</p>
	</form>
	{if $item->file_url}
	<h1>Swap in File</h1>
	{else}
	<h1>Add a File</h1>
	{/if}
	<form action="item/{$item->id}/swap" method="post" enctype="multipart/form-data">
		<p>
		<label for="uploaded_file">select a file</label>
		<input type="file" name="uploaded_file"/>
		{if $item->file_url}
		<input type="submit" value="swap in file"/>
		{else}
		<input type="submit" value="add file"/>
		{/if}
		</p>
	</form>
	{if $item->file_url}
	<img src="{$item->thumbnail_url}">
	{/if}
</div>
	<div class="controls">
		<form action="item/{$item->id}" method="delete">
			<input type="submit" value="delete item">
		</form>
	</div>
	<div class="clear"></div>

{/block}
