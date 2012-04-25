{% extends "framework/bootstrap.tpl" %}

{% block content %}

<div class="container">
    <div class="row">
        <div class="span2">
            <ul class="nav nav-pills nav-stacked">
                <li><a href="admin">User Settings</a></li>   
                <li><a href="admin/users">List Users</a></li>
                <li class="active"><a href="admin/directory">Add a User</a></li> 
            </ul>
        </div>
        <div class="span6">
            <h2>Find User in UT Directory</h2>
            <form>
                <label for="lastname">last name:</label>
                <input type="text" name="lastname" value="{{ lastname }}">
                <input type="submit" value="search">
            </form>
            <ul class="results">
                {% for person in results %}
                <li><a href="admin/add_user_form/{{ person.eid }}">{{ person.name }} : {{ person.eid }} ({{ person.unit }})</a></li>
                {% endfor %}
            </ul>
        </div>
    </div>
</div>

{% endblock %}
