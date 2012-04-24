{% extends "framework/bootstrap.tpl" %}

{% block content %}
<h2>{{ item.title }}</h2>
<p>{{ item.body }}</p>
<p>
<img src="{{ item.links.file }}">
</p>
{% endblock %}
