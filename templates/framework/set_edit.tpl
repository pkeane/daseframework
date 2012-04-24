{extends file="framework/bootstrap.tpl"}

{block name="content"}
<div>
	<div class="controls">
		<a href="set/{$set->name}">view set</a>
	</div>
	<h1>Edit Set</h1>
	<form action="set/{$set->id}/edit" method="post">
		<label for="title">title</label>
		<input type="text" name="title" value="{$set->title}"/>
		<p>
		<input type="submit" value="update"/>
		</p>
	</form>
	<div class="controls">
		<form action="set/{$set->id}" method="delete">
			<input type="submit" value="delete set">
		</form>
	</div>
	<div class="clear"></div>
</div>

{/block}
