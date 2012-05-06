{% extends "framework/bootstrap.tpl" %}

{% block content %}

<div class="row">
    <div class="pull-right">
        <a href="content/create" class="btn btn-primary">create content</a>
        <a href="content/attributes" class="btn btn-primary">manage attributes</a>
    </div>
</div>

<h3>Items ({{ items|length }}) <small><a href="content/items">table</a> | <a href="content/items/thumbnails">thumbnails</a></h3>
<ul class="thumbnails"> 
    {% for item in items %}
    <li class="span2">
    <a href="content/item/{{ item.id }}" class="thumbnail">
        {% if item.thumbnail_url %}
        <img src="{{ item.thumbnail_url }}">
        {% else %}
        <img src="content/file/thumb/{{ item.type }}.jpg">
        {% endif %}
    </a>
    <h5>{{ item.title|slice(0,20) }} <small>{{ item.type }}</small></h5>
    </li>
    {% endfor %}
</ul>
{% endblock %}
