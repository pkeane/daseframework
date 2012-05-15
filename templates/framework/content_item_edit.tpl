
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
            <!-- keeps from collision w/ url passed params -->
            <select name="item_type">
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

    <!-- beware that these do not trounce form values -->
    <input type="hidden" value="{{ not_set }}" name="not_set">
    <input type="hidden" value="{{ page }}" name="page">
    <input type="hidden" value="{{ q }}" name="q">
    <input type="hidden" value="{{ att }}" name="att">
    <input type="hidden" value="{{ val }}" name="val">
    <input type="hidden" value="{{ type }}" name="type">
    <input type="hidden" value="{{ max }}" name="max">
    <input type="hidden" value="{{ num }}" name="num">
    <input type="hidden" value="{{ display }}" name="display">
    <div class="controls"><input type="submit" value="submit" class="btn btn-primary"></div>
</form>

<form action="content/items/{{ item.id }}" id="delete-item" class="form-horizontal" method="delete">
    <input type="hidden" value="{{ not_set }}" name="not_set">
    <input type="hidden" value="{{ page }}" name="page">
    <input type="hidden" value="{{ q }}" name="q">
    <input type="hidden" value="{{ att }}" name="att">
    <input type="hidden" value="{{ val }}" name="val">
    <input type="hidden" value="{{ type }}" name="type">
    <input type="hidden" value="{{ max }}" name="max">
    <input type="hidden" value="{{ num }}" name="num">
    <input type="hidden" value="{{ display }}" name="display">
    <input class="btn btn-danger pull-right" type="submit" value="delete item">
</form>
</div>


{% endblock %}
