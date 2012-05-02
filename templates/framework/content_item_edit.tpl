{% extends "framework/bootstrap.tpl" %}

{% block content %}
<div class="container">

    <p>
    <a href="content/item/{{ item.id }}" class="btn btn-info">view item</a>
    </p>

<form action="content/item/{{ item.id }}/edit" method="post" class="well form-horizontal">
    <h3>Edit Item</h3>
    <div class="control-group">
        <label class="control-label" for="input-title">Title</label>
        <div class="controls">
            <input type="text" class="span10" name="title" value="{{ item.title }}" id="input-title">
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="input-body">Body</label>
        <div class="controls">
            <textarea class="span10" rows="5" name="body" id="input-body">{{ item.body }}</textarea>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="input-type">Type</label>
        <div class="controls">
            <input type="text" class="span2" name="type" value="{{ item.type }}" id="input-type">
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
            <a class="btn btn-mini btn-info" id="loclink" href="content/item/{{ item.id }}/map">Add/Change Location</a>
        </div>
    </div>
    <div class="controls"><input type="submit" value="submit" class="btn btn-primary"></div>
</form>

<form action="content/item/{{ item.id }}/swap" class="well form-horizontal" method="post" enctype="multipart/form-data">
    {% if item.file_url %}
    <h3>Swap-In File</h3>
    <input type="file" name="uploaded_file"/>
    <input type="submit" class="btn btn-warning" value="swap in file"/>
    {% else %}
    <h3>Add File</h3>
    <input type="file" name="uploaded_file"/>
    <input type="submit" class="btn btn-warning" value="add file"/>
    {% endif %}
</form>

<form action="content/item/{{ item.id }}" class="well form-horizontal" method="delete">
    <input class="btn btn-danger" type="submit" value="delete item">
</form>
</div>


{% endblock %}
