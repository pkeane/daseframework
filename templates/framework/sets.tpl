{% extends "framework/bootstrap.tpl" %}

{% block content %}
<div class="controls">
	<a href="items">view items</a> |
	view sets |
	<a href="admin/create">create content</a> |
	<a href="set/form">create a set</a>
</div>
<h1>Sets</h1>
<ul class="sets">
	{% for set in sets %}
	<li><a href="set/{{ set.name }}">{{ set.title }}</a></li>
	{% endfor %}
</ul>
{% endblock %}
