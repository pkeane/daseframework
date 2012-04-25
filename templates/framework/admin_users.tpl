{% extends "framework/bootstrap.tpl" %}

{% block content %}
<div class="container">
    <div class="row">
        <div class="span2">
            <ul class="nav nav-pills nav-stacked">
                <li><a href="admin">User Settings</a></li>   
                <li class="active"><a href="admin/users">List Users</a></li>
                <li><a href="admin/directory">Add a User</a></li> 
            </ul>
        </div>
        <div class="span6">
            <h2>Users</h2>
            <table class="table" id="user_privs">
                {% for u in users %}
                <tr>
                    <td>{{ u.name }}</td>
                    <td>
                        {% if u.is_admin %}
                        <a href="{{ app_root }}/admin/user/{{ u.id }}/is_admin" data-method="delete" class="btn btn-mini btn-danger">[remove privileges]</a>
                        {% else %}
                        <a href="{{ app_root }}/admin/user/{{ u.id }}/is_admin" data-method="put" class="btn btn-mini btn-info">[grant privileges]</a>
                        {% endif %}
                    </td>
                </tr>
                {% endfor %}
            </table>
        </div>
    </div>
</div>

{% endblock %}
