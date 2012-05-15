{% extends "framework/bootstrap.tpl" %}

{% block content %}

<div class="row">
    <div class="pull-right">
        <a href="content/create" class="btn btn-primary">create content</a>
        <a href="content/attributes" class="btn btn-primary">manage attributes</a>
    </div>
</div>

<h4>{{ start }} - {{ end }} of {{ total }} total items
    <small>
        <a href="content/items?page={{ page }}&amp;q={{ q }}&amp;att={{ att }}&amp;val={{ val }}&amp;type={{ type }}&amp;max={{ max }}&amp;display=table">table </a>
        |
        <a href="content/items?page={{ page }}&amp;q={{ q }}&amp;att={{ att }}&amp;val={{ val }}&amp;type={{ type }}&amp;max={{ max }}">thumbnails</a>
    </small>
</h4>

<div class="pagination">
    <ul>
        <li {% if page < 2 %}class="disabled"{% endif %}><a href="content/items?page={{ page-1 }}&amp;q={{ q }}&amp;att={{ att }}&amp;val={{ val }}&amp;type={{ type }}&amp;max={{ max }}&amp;display={{ display }}">Prev</a></li>

        {% for i in 1..5 %}
        <li {% if i == page %}class="active"{% endif %}>
        <a href="content/items?page={{ i }}&amp;q={{ q }}&amp;att={{ att }}&amp;val={{ val }}&amp;type={{ type }}&amp;max={{ max }}&amp;display={{ display }}">{{ i }}</a>
        </li>
        {% endfor %}

        {% if total_pages > 5 %}
        {% if total_pages > 6 %}
        <li><a href="#">...</a></li>
        {% endif %}
        <li><a href="content/items?page={{ total_pages }}&amp;q={{ q }}&amp;att={{ att }}&amp;val={{ val }}&amp;type={{ type }}&amp;max={{ max }}&amp;display={{ display }}">{{ total_pages }}</a></li>
        {% endif %}
        <li {% if page >= total_pages %}class="disabled"{% endif %}><a href="content/items?page={{ page+1 }}&amp;q={{ q }}&amp;att={{ att }}&amp;val={{ val }}&amp;type={{ type }}&amp;max={{ max }}&amp;display={{ display }}">Next</a></li>
    </ul>
</div>


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
        <th></th>
    </tr>
    {% for item in items %}
    <tr>
    <td>{{ loop.index + start - 1 }}.</td>
    <td><a href="content/items?page={{ page }}&amp;q={{ q }}&amp;att={{ att }}&amp;val={{ val }}&amp;type={{ type }}&amp;max={{ max }}&amp;num={{ loop.index + start -1 }}&amp;display={{ display }}">{{ item.title }}</a></td>

        <td>
            <a href="content/items/thumbnails?type={{ item.type }}">{{ item.type }}</a>
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
            <a href="content/item/{{ item.id }}/edit" class="btn btn-warning">edit</a>
        </td>
        <td>
            <a href="{{ item.url }}.json" class="btn btn-info">json</a>
        </td>
    </tr>
    {% endfor %}
</table>
{% endblock %}
