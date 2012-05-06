{% extends "framework/bootstrap.tpl" %}

{% block content %}
<div class="container">
<div class="row">
    <div class="pull-right">
        <a href="content/create" class="btn btn-primary">create content</a>
        <a href="content/attributes" class="btn btn-primary">manage attributes</a>
    </div>
</div>

        <h3>Edit Attribute ({{ att.ascii_id }})</h3>
    <form action="content/attribute/{{ att.id }}/edit" method="post" class="well form-horizontal">
        <div class="control-group">
            <label class="control-label" for="input-name">Name</label>
            <div class="controls">
                <input type="text" class="span4" name="name" value="{{ att.name }}" id="input-name">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="input-applies_to_type">Applies To Type</label>
            <div class="controls">
                <input type="text" class="span4" name="applies_to_type" value="{{ att.applies_to_type }}" id="input-applies_to_type">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="input-values_item_type">Values Item Type</label>
            <div class="controls">
                <input type="text" class="span4" name="values_item_type" value="{{ att.values_item_type }}" id="input-values_item_type">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="input-values_json">Defined Values</label>
            <div class="controls">
                <textarea class="span4" name="values" rows="{{ att.values|length }}" id="input-values_json">{{ valstring }}</textarea>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="input-input_type">Input Type</label>
            <div class="controls">
                <select name="input_type" class="span2" id="input-input_type">
                    <option {% if att.input_type == 'text' %}selected{% endif %}>text</option>
                    <option {% if att.input_type == 'textarea' %}selected{% endif %}>textarea</option>
                    <option {% if att.input_type == 'radio' %}selected{% endif %}>radio</option>
                    <option {% if att.input_type == 'checkbox' %}selected{% endif %}>checkbox</option>
                    <option {% if att.input_type == 'select' %}selected{% endif %}>select</option>
                    <option {% if att.input_type == 'dynamic' %}selected{% endif %}>dynamic</option>
                </select>
            </div>
        </div>




        <div class="controls"><input type="submit" value="submit" class="btn btn-primary"></div>
    </form>

    <form action="content/attribute/{{ att.id }}" class="well form-horizontal" method="delete">
        <input class="btn btn-danger" type="submit" value="delete attribute">
    </form>
</div>


{% endblock %}
