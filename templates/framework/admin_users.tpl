{% extends "framework/bootstrap.tpl" %}

{% block content %}
<div class="sets_instructor">
	<h1>Users</h1>
	<ul class="users" id="user_privs">
		{% for u in users %}
		<li>{{ u.name }}
		{% if u.is_admin %}
		<a href="admin/user/{{ u.id }}/is_admin" class="delete">[remove privileges]</a>
		{% else %}
		<a href="admin/user/{{ u.id }}/is_admin" class="put">[grant privileges]</a>
		{% endif %}
		</li>
		{% endfor %}
	</ul>
</div>
{% endblock %}
