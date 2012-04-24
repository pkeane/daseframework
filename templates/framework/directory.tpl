{extends file="framework/bootstrap.tpl"}

{block name="content"}
<div>
	<h1>Find User in UT Directory</h1>
	<form>
		<label for="lastname">last name:</label>
		<input type="text" name="lastname" value="{$lastname}">
		<input type="submit" value="search">
	</form>
	<ul class="results">
		{foreach item=person from=$results}
		<li><a href="admin/add_user_form/{$person.eid}">{$person.name} : {$person.eid} ({$person.unit})</a></li>
		{/foreach}
	</ul>
</div>
{/block}
