{% extends "framework/bootstrap.tpl" %}

{% block content %}
<div>
	<h1>Add User</h1>
	<dl class="dl-horizontal">
		<dt>name</dt>
		<dd>{{ record.name }}</dd>
		<dt>eid</dt>
		<dd>{{ record.eid }}</dd>
		<dt>email</dt>
		<dd>{{ record.email }}</dd>
		<dt>title</dt>
		<dd>{{ record.title }}</dd>
		<dt>unit</dt>
		<dd>{{ record.unit }}</dd>
		<dt>phone</dt>
		<dd>{{ record.phone }}</dd>
	</dl>
	{% if user %}
	<h3>{{ user.name }} is already registered</h3>
	{% endif %}

	<form method="post" action="admin/users">
		<input type="hidden" name="eid" value="{{ record.eid }}">
		<input type="submit" value="add {{ record.name }}">
	</form>
</div>
{% endblock %}
