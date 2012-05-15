{% extends "framework/bootstrap.tpl" %}

{% block content %}
<div class="pull-right">
    <a href="content/create" class="btn btn-primary">create content</a>
    <a href="content/attributes" class="btn btn-primary">manage attributes</a>
</div>


<div class="page-header">
<h2>{{ start }} - {{ end }} of {{ total }} total items
    <small>
        <a href="content/items?page={{ page }}&amp;q={{ q }}&amp;att={{ att }}&amp;val={{ val }}&amp;type={{ type }}&amp;max={{ max }}&amp;display=table">table </a>
        |
        <a href="content/items?page={{ page }}&amp;q={{ q }}&amp;att={{ att }}&amp;val={{ val }}&amp;type={{ type }}&amp;max={{ max }}">thumbnails</a>
    </small>
</h2>
</div>

{% if total > max %}
<div class="pagination">
    <ul>
        <li {% if page < 2 %}class="disabled"{% endif %}><a href="content/items?page={{ page-1 }}&amp;q={{ q }}&amp;att={{ att }}&amp;val={{ val }}&amp;type={{ type }}&amp;max={{ max }}&amp;display={{ display }}">Prev</a></li>

        {% for i in range(1,paginated) %}
        <li {% if i == page %}class="active"{% endif %}>
        <a href="content/items?page={{ i }}&amp;q={{ q }}&amp;att={{ att }}&amp;val={{ val }}&amp;type={{ type }}&amp;max={{ max }}&amp;display={{ display }}">{{ i }}</a>
        </li>
        {% endfor %}

        {% if total_pages > paginated %}
        {% if total_pages > paginated + 1 %}
        <li><a href="#">...</a></li>
        {% endif %}
        <li><a href="content/items?page={{ total_pages }}&amp;q={{ q }}&amp;att={{ att }}&amp;val={{ val }}&amp;type={{ type }}&amp;max={{ max }}&amp;display={{ display }}">{{ total_pages }}</a></li>
        {% endif %}
        <li {% if page >= total_pages %}class="disabled"{% endif %}><a href="content/items?page={{ page+1 }}&amp;q={{ q }}&amp;att={{ att }}&amp;val={{ val }}&amp;type={{ type }}&amp;max={{ max }}&amp;display={{ display }}">Next</a></li>
    </ul>
</div>
{% endif %}


<ul class="thumbnails contact_sheet"> 
    {% for item in items %}
    <li class="span2{% if curr == loop.index+start-1 %} highlight{% endif %}{% if 'type' == item.type %} type{% endif %}">
    <a name="curr{{ loop.index + start + 6 }}"></a>
    <span class="index">{{ loop.index + start - 1 }}.</span>
    <!--<a href="content/items/{{ item.id }}">-->
        <a href="content/items?page={{ page }}&amp;q={{ q }}&amp;att={{ att }}&amp;val={{ val }}&amp;type={{ type }}&amp;max={{ max }}&amp;num={{ loop.index + start -1 }}&amp;display={{ display }}">
        <div class="thumbnail">
            {% if item.thumbnail_url %}
            <img src="{{ item.thumbnail_url }}" alt="{{ item.title }}">
            {% else %}
            <img src="content/file/thumb/{{ item.type }}.jpg" alt="{{ item.type }} icon">
            {% endif %}
            <div class="caption">{{ item.title|slice(0,20) }} <small>[{{ item.type }}]</small></div>
        </div>
    </a>
    </li>
    {% endfor %}
</ul>
{% endblock %}
