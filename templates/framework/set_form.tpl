{% extends "framework/bootstrap.tpl" %}

{% block content %}
<div class="controls">
	<a href="items">view items</a> |
	<a href="set/list">view sets</a> |
	<a href="admin/create">create content</a> |
	create a set
</div>
<h1>Create a New Set</h1>
<form method="post">
	<label for="title">Title</label>
	<input type="text" name="title">
	<input type="submit" value="create set">
</form>
{% endblock %}
