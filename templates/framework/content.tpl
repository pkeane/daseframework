{% extends "framework/bootstrap.tpl" %}

{% block subheader %}

<ul class="nav nav-pills content-nav">
    <li {% if nav == 'view' %}class="active"{% endif %}>
    <a href="content">View</a>
    </li>
    <li {% if nav == 'create' %}class="active"{% endif %}>
    <a href="content/create">Create Content</a>
    </li>
    <li {% if nav == 'attributes' %}class="active"{% endif %}>
    <a href="content/attributes">Manage Attributes</a>
    </li>
    <li class="dropdown" id="history">
    <a class="dropdown-toggle" data-toggle="dropdown" href="#history">History <b class="caret"></b></a>
    <ul class="dropdown-menu">
        {% for uri,title in request.user.data_array.history %}
        <li><a href="{{ uri }}">{{ title }}</a></li>
        {% endfor %}
    </ul>
    </li>
    <form action="content/items" class="navbar-form pull-right" method="get">
        <input type="text" name="q" value="{{ q }}">
        <select name="type">
            <option value="">all types</option>
            {% for ty in types %}
            <option value="{{ ty }}" {%if type == ty %}selected{% endif %}>{{ ty }}</option>
            {% endfor %}
        </select>
        <input type="submit" value="search" class="btn btn-primary">
    </form>
</ul>

{% endblock %}

