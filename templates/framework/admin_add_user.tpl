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
            <h2>Add User</h2>
            <dl class="well container dl-horizontal fix">
                <dt>name</dt>
                <dd>{{ record.name }}</dd>
                <dt>eid</dt>
                <dd>{{ record.eid }}</dd>
                <dt>email</dt>
                <dd>{{ record.email }}</dd>
                <dt>title</dt>
                <dd>{{ record.title }}</dd>
                <dt>unit</dt>
                <dd>{{ record.unit }}</dd>
                <dt>phone</dt>
                <dd>{{ record.phone }}</dd>
            </dl>
            {% if user %}
            <h3>{{ user.name }} is already registered</h3>
            {% endif %}
            <form method="post" action="admin/users">
                <input type="hidden" name="eid" value="{{ record.eid }}">
                <input type="submit" value="add {{ record.name }}">
            </form>
        </div>
    </div>
</div>
{% endblock %}
