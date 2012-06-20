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
                <label for="lastname">last name or UT EID:</label>
                <input type="text" name="lastname" value="{{ lastname }}">
                <input type="submit" value="search">
            </form>
            <h3>Results for {{ lastname }} as Last Name</h3>
            <ul class="results">
                {% for person in results %}
                <li><a href="admin/add_user_form/{{ person.eid }}">{{ person.name }} : {{ person.eid }} ({{ person.unit }})</a></li>
                {% endfor %}
            </ul>
            <h3>Results for {{ lastname }} as UT EID</h3>
            <ul class="results">
                {% for person in results_eid %}
                <li><a href="admin/add_user_form/{{ person.eid }}">{{ person.name }} : {{ person.eid }} ({{ person.unit }})</a></li>
                {% endfor %}
            </ul>
        </div>
    </div>
</div>

{% endblock %}
