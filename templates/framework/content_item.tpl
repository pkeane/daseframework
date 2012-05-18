{% extends "framework/bootstrap.tpl" %}

{% block content %}

<div class="pull-right">
    <form action="content/items" class="form-inline" method="get">
        <input type="text" name="q" value="{{ q }}">
        <input type="submit" value="search" class="btn btn-primary">
        <a href="content/create" class="btn btn-primary">create content</a>
        <a href="content/attributes" class="btn btn-primary">manage attributes</a>
    </form>
</div>


<div class="page-header">
{% if num %}
<h2>{{ num }} of {{ total }} total items</h2>
{% else %}
<h2>Item: {{ item.title }}</h2>
{% endif %}
</div>



{% if is_set %}
<div class="pagination">
    <ul>
        <li {% if num < 2 %}class="disabled"{% endif %}><a href="content/items?page={{ page }}&amp;q={{ q }}&amp;att={{ att }}&amp;val={{ val }}&amp;type={{ type }}&amp;max={{ max }}&amp;num={{ num - 1 }}&amp;display={{ display }}">Prev</a></li>

        <li><a href="content/items?page={{ page }}&amp;q={{ q }}&amp;att={{ att }}&amp;val={{ val }}&amp;type={{ type }}&amp;max={{ max }}&amp;curr={{ num }}&amp;display={{ display }}#curr{{ num }}">Up</a></li>
        <li {% if num >= total %}class="disabled"{% endif %}><a href="content/items?page={{ page }}&amp;q={{ q }}&amp;att={{ att }}&amp;val={{ val }}&amp;type={{ type }}&amp;max={{ max }}&amp;num={{ num + 1 }}&amp;display={{ display }}">Next</a></li>
    </ul>
</div>
{% endif %}

<ul class="thumbnails">
    <li class="span5">
    <div class="thumbnail">
    <dl class="dl-horizontal fix metadata">
        {% for k,vs in item.metadata %}
        {% for v in vs %}
        <dt>{{ k }}</dt>
        <dd>
        <a href="content/items?att={{ k }}&amp;val={{ v }}">{{ v }}</a>
        {% if att_map[k] %}
        <a href="content/items?type={{ att_map[k] }}&amp;att=title&amp;val={{ v }}">[find]</a>
        {% endif %}
        </dd>
        {% endfor %}
        {% endfor %}
        <dt>----</dt><dd>----</dd>
        <dt>Permalink</dt><dd><a href="{{ app_root }}/content/item/{{ item.serial_number }}">content/item/{{ item.serial_number }}</a></dd>
        <dt>Serial Number</dt><dd>{{ item.serial_number }}</dd>
        <dt>Title</dt><dd>{{ item.title }}</dd>
        <dt>Body</dt><dd>{{ item.body }}</dd>
        <dt>Type</dt><dd><a href="content/items?type={{ item.type }}">{{ item.type }}</a></dd>
        <dt>Url</dt><dd><a href="{{ item.url }}">{{ item.url }}</a></dd>
        <dt>JSON Url</dt><dd><a href="{{ item.url }}.json">{{ item.url }}.json</a></dd>
        <dt>File Url</dt><dd><a href="{{ item.file_url }}">{{ item.file_url }}</a></dd>
        <dt>Thumbnail Url</dt><dd><a href="{{ item.thumbnail_url}}">{{ item.thumbnail_url }}</a></dd>
        <dt>View Url</dt><dd><a href="{{ item.view_url }}">{{ item.view_url }}</a></dd>
        <dt>Filesize</dt><dd>{{ item.filesize }}</dd>
        <dt>File Ext</dt><dd>{{ item.file_ext }}</dd>
        <dt>File Original Name</dt><dd>{{ item.file_original_name }}</dd>
        <dt>Mime</dt><dd>{{ item.mime }}</dd>
        <dt>Width</dt><dd>{{ item.width }}</dd>
        <dt>Height</dt><dd>{{ item.height }}</dd>
        <dt>Lat</dt><dd>{{ item.lat }}</dd>
        <dt>Lng</dt><dd>{{ item.lng }}</dd>
        <dt>Created</dt><dd>{{ item.created }}</dd>
        <dt>Created By</dt><dd>{{ item.created_by }}</dd>
        <dt>Updated</dt><dd>{{ item.updated }}</dd>
        <dt>Updated By</dt><dd>{{ item.updated_by }}</dd>
    </dl>
    <div class="caption">
        <p>
        {% if is_set %}
        <a href="content/items/{{ item.id }}/edit?page={{ page }}&amp;q={{ q }}&amp;att={{ att }}&amp;val={{ val }}&amp;type={{ type }}&amp;max={{ max }}&amp;num={{ num }}&amp;display={{ display }}" id="edit-item" class="btn btn-info">edit item</a>
        <a href="content/items/{{ item.id }}/edit/metadata?page={{ page }}&amp;q={{ q }}&amp;att={{ att }}&amp;val={{ val }}&amp;type={{ type }}&amp;max={{ max }}&amp;num={{ num }}&amp;display={{ display }}" id="edit-item-metadata" class="btn btn-info">add/edit metadata</a>
        <a class="btn btn-info" id="loclink" href="content/items/{{ item.id }}/map">Add/Change Location</a>
        {% else %}
        <a href="content/items/{{ item.id }}/edit?not_set=1" id="edit-item" class="btn btn-info">edit item</a>
        <a href="content/items/{{ item.id }}/edit/metadata?not_set=1" id="edit-item-metadata" class="btn btn-info">add/edit metadata</a>
        <a class="btn btn-info" id="loclink" href="content/items/{{ item.id }}/map">Add/Change Location</a>
        {% endif %}
        </p>
    </div>
    </div>
    </li>

    <li class="span7">
    <div class="thumbnail">
        <a href="{{ item.file_url }}">
            {% if item.view_url %}
            <img src="{{ item.view_url }}" alt="{{ item.title }}">
            {% else %}
            <img src="content/file/thumb/{{ item.type }}.jpg" alt="{{ item.type }} icon">
            {% endif %}
        </a>
        <div class="caption">
            <p>
            {% if is_set %}
            <a href="content/items/{{ item.id }}/swap?page={{ page }}&amp;q={{ q }}&amp;att={{ att }}&amp;val={{ val }}&amp;type={{ type }}&amp;max={{ max }}&amp;num={{ num }}&amp;display={{ display }}" id="edit-item-swap" class="btn btn-warning">{% if item.file_url %}swap{% else %}add{% endif %} image</a>
            {% else %}
            <a href="content/items/{{ item.id }}/swap?not_set=1" id="edit-item-swap" class="btn btn-warning">{% if item.file_url %}swap{% else %}add{% endif %} image</a>
            {% endif %}
            </p>
        </div>
    </div>
    </li>
</ul>

{% endblock %}
