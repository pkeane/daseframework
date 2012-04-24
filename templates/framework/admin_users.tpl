{extends file="framework/bootstrap.tpl"}

{block name="content"}
<div class="sets_instructor">
	<h1>Users</h1>
	<ul class="users" id="user_privs">
		{foreach item=u from=$users}
		<li>{$u->name}
		{if $u->is_admin}
		<a href="admin/user/{$u->id}/is_admin" class="delete">[remove privileges]</a>
		{else}
		<a href="admin/user/{$u->id}/is_admin" class="put">[grant privileges]</a>
		{/if}
		</li>
		{/foreach}
	</ul>
</div>
{/block}
