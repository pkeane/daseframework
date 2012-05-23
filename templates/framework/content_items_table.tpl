{% extends "framework/content.tpl" %}

{% block content %}

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

<table class="table table-bordered table-condensed table-striped"> 
    <tr>
        <th></th>
        <th>title</th>
        <th>type</th>
        <th>created</th>
        <th>created by</th>
        <th>file url</th>
        <th>view url</th>
        <th></th>
    </tr>
    {% for item in items %}
    <tr>
    <td>{{ loop.index + start - 1 }}.</td>
    <td><a href="content/items?page={{ page }}&amp;q={{ q }}&amp;att={{ att }}&amp;val={{ val }}&amp;type={{ type }}&amp;max={{ max }}&amp;num={{ loop.index + start -1 }}&amp;display={{ display }}">{{ item.title }}</a></td>

        <td>
            <a href="content/items?type={{ item.type }}">{{ item.type }}</a>
        </td>
        <td>
            {{ item.created|date("Y-m-d") }}
        </td>
        <td>
            {{ item.created_by }}
        </td>
        <td>
            <a href="{{ item.file_url }}">{{ item.file_url }}</a>
        </td>
        <td>
            <a href="{{ item.view_url }}">{{ item.view_url }}</a>
        </td>
        <td>
            <a href="{{ item.url }}.json" class="btn btn-info">json</a>
        </td>
    </tr>
    {% endfor %}
</table>
{% endblock %}
