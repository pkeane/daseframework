
{% block content %}
<div class="container span8">

<form id="edit_item_form" action="content/items/{{ item.id }}/edit" method="post" class="form-horizontal">
    <h3>Edit Item</h3>
    <div class="control-group">
        <label class="control-label" for="input-title">Title</label>
        <div class="controls">
            <input type="text" class="span6" name="title" value="{{ item.title }}" id="input-title">
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="input-body">Body</label>
        <div class="controls">
            <textarea class="span6" rows="5" name="body" id="input-body">{{ item.body }}</textarea>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="input-type">Type</label>
        <div class="controls">
            <select name="type">
                <option value="">select one:</option>
                <option {% if item.type == 'type' %}selected{% endif %}>type</option>
                {% for type in types %}
                <option {% if type.title == item.type %}selected{% endif %}>{{ type.title }}</option>
                {% endfor %}
            </select>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label">Thumbnail</label>
        <div class="controls">
            <img src="{{ item.thumbnail_url }}">
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="input-lat-lng">Lat/Lng</label>
        <div class="controls">
            <input type="text" class="span4" name="lat" value="{{ item.lat }},{{ item.lng }}" id="input-lat-lng" readonly>
            <a class="btn btn-mini btn-info" id="loclink" href="content/items/{{ item.id }}/map">Add/Change Location</a>
        </div>
    </div>
    <div class="controls"><input type="submit" value="submit" class="btn btn-primary"></div>
</form>

<form action="content/items/{{ item.id }}" class="form-horizontal" method="delete">
    <input class="btn btn-danger" type="submit" value="delete item">
</form>
</div>


{% endblock %}
