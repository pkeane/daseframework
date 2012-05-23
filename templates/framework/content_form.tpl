{% extends "framework/content.tpl" %}

{% block content %}

	<h3>Create Content</h3>
	<form action="content/create" method="post" class="well form-horizontal" enctype="multipart/form-data">
    <div class="control-group">
        <label class="control-label" for="input-title">Title</label>
        <div class="controls">
            <input type="text" class="span10" name="title" id="input-title">
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="input-body">Body</label>
        <div class="controls">
            <textarea class="span10" rows="5" name="body" id="input-body"></textarea>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="input-type">Type</label>
        <div class="controls">
            <select name="type">
                <option value="">select one:</option>
                <option>type</option>
                {% for type in types %}
                {% if type != 'type' %} 
                <option>{{ type }}</option>
                {% endif %}
                {% endfor %}
            </select>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="input-file">Attached File</label>
        <div class="controls">
            <input type="file" name="uploaded_file"/>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="input-preferred_sernum">Preferred Serial Number</label>
        <div class="controls">
            <input type="text" class="span1" name="preferred_sernum" id="input-preferred_sernum">
        </div>
    </div>

    <div class="controls"><input type="submit" value="submit" class="btn btn-primary"></div>
	</form>

    <div class="well">
        <a id="csv" href="content/csv/form" class="btn btn-primary btn-small">create multiple items by CSV upload</a>
    </div>

{% endblock %}
