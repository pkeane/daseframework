{% extends "framework/bootstrap.tpl" %}

{% block content %}
<div>
	<h1>Administration</h1>
	<ul class="unstyled">
		<li><h2>Manage</h2></li>
		<li><a href="user/settings">my user settings</a></li>
		{if $request->user->is_admin}
		<li><a href="directory">add a user</a></li>
		<li><a href="admin/users">list users</a></li>
		<li><h2>View</h2></li>
		<li><a href="items">view items</a></li>
		<li><a href="set/list">view sets</a></li>
		<li><a href="admin/cats">cats</a></li>
		<li><h2>Create</h2></li>
		<li><a href="admin/create">create content</a></li>
		<li><a href="set/form">create a set</a></li>
		{/if}
	</ul>
</div>
{% endblock %}
