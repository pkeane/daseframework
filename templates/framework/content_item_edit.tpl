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
            <a class="btn btn-mini btn-info" id="loclink" href="content/item/{{ item.id }}/map">Add/Change Location</a>
        </div>
    </div>
    <div class="controls"><input type="submit" value="submit" class="btn btn-primary"></div>
</form>


<div class="well">
    <h3>Edit Metadata</h3>
    <table id="item_metadata" class="table table-striped table-condensed">
        <tbody>
            {% for k,vs in item.metadata_extended %}
        {% for v in vs.values %}
        <tr>
            <th scope="row">{{ vs.label }}</th>
            <td>
                <span class="current_value">{{ v.text }}</span>
                <span class="value_input_form"></span>
            </td>
            <td>
                <a href="{{ v.edit}}/form" class="edit btn btn-mini btn-warning">edit</a></td>
            <td><a href="{{ v.edit}}" class="delete btn btn-mini btn-danger">delete</a></td>
        </tr>
        {% endfor %}
        {% endfor %}
    </tbody>
</table>
</div>

<form method="post" action="content/item/{{ item.id }}/metadata" id="bulk_add" class="well form-inline">
    <input type="hidden" name="items" value="|">
    <h3>Add Metadata to Item</h3>
    <select name="attribute_id">
        <option value="">select an attribute:</option>
        {% for att in atts %}
        <option value="{{ att.id }}">{{ att.name }}</option>
        {% endfor %}
    </select>
    <span id="att_input_form"></span>
    <input type="submit" value="add metadata">
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
