{% extends "framework/bootstrap.tpl" %}

{% block content %}

<div class="row">
    <div class="pull-right">
        <a href="content/create" class="btn btn-primary">create content</a>
        <a href="content/attributes" class="btn btn-primary">manage attributes</a>
    </div>
</div>

<h3>Items ({{ items|length }}) <small><a href="content/items">table</a> | <a href="content/items/thumbnails">thumbnails</a></h3>
<table class="table table-condensed table-striped" id="items">
    <tr>
        <th></th>
        <!--
        <th>thumbnail</th>
        <th>name</th>
        -->
        <th>title</th>
        <th>type</th>
        <th>created</th>
        <th>created by</th>
        <th>file</th>
        <th>view</th>
        <th>edit</th>
        <th>json</th>
    </tr>
    {% for item in items %}
    <tr>
        <td>
            {{ loop.index }}. <input type="checkbox" name="item[]" value="{{ item.id }}">
        </td>
        <!--
        <td class="thumb">
            <a href="content/item/{{ item.id }}"><img src="{{ item.thumbnail_url }}"></a>
        </td>
        <td>
            {{ item.name }}
        </td>
        -->
        <td>
            {{ item.title }}
        </td>
        <td>
            {{ item.type }}
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
<div id="toggle_check" class="toggle_check">
    <a href="#">check/uncheck all</a>
</div>
<div class="container">
    <form method="post" action="content/items/metadata" id="bulk_add" class="well form-inline">
        <input type="hidden" name="items" value="|">
        <h4>Add Metadata to Checked Items</h4>
        <select name="attribute_id">
            <option value="">select an attribute:</option>
            {% for att in atts %}
            <option value="{{ att.id }}">{{ att.name }}</option>
            {% endfor %}
        </select>
        <span id="att_input_form"></span>
        <input type="submit" value="add metadata">
    </form>
</div>


{% endblock %}
