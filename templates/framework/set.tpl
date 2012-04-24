{extends file="framework/bootstrap.tpl"}

{block name="head-links}
<link rel="items_order" href="set/{$set->id}/order">
{/block}

{block name="content"}
<div class="controls">
	<a href="set/{$set->id}/edit">edit set</a> | 
	<a href="set/{$set->name}.json">view json</a> | 
	<a href="set/list">view sets</a> |
	<a href="items">view items</a> 
</div>
<h1>Set {$set->title} ({$set->name})</h1>
<table class="items">
	<tbody id="set">
		<tr>
			<th></th>
			<th></th>
			<th>title</th>
			<th>created</th>
			<th>created by</th>
			<th>edit</th>
			<th>remove</th>
		</tr>
		{foreach item=item name=foo from=$set->items}
		<tr id="{$item->id}" >
			<td><span class="key">{$smarty.foreach.foo.iteration}</span></td>
			<td class="thumb">
				<a href="item/{$item->id}"><img src="{$item->thumbnail_url}"></a>
			</td>
			<td>
				{$item->title}
			</td>
			<td>
				{$item->created|date_format:'%D'}
			</td>
			<td>
				{$item->created_by}
			</td>
			<td>
				<a href="item/{$item->id}/edit">edit</a>
			</td>
			<td>
				<a href="set/{$set->id}/item/{$item->id}" class="delete">remove</a>
			</td>
		</tr>
		{/foreach}
	</tbody>
</table>
{/block}
