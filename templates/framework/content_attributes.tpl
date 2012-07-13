{% extends "framework/content.tpl" %}

{% block content %}
<div class="container">
    <h3>Metadata Attributes</h3>
    <table class="table table-striped table-condensed" id="atts">
        <tr>
            <th>name</th>
            <th>ascii_id</th>
            <th>applies to type</th>
            <th>values item type</th>
            <th>values json</th>
            <th>input type</th>
            <th></th>
        </tr>
        {% for att in atts %}
        <tr>
            <td>
                <a href="content/attribute/{{ att.id }}/edit">{{ att.name }}</a>
            </td>
            <td>
                {{ att.ascii_id }}
            </td>
            <td>
                {{ att.applies_to_type }}
            </td>
            <td>
                {{ att.values_item_type }}
            </td>
            <td>
                {{ att.values_json }}
            </td>
            <td>
                <a href="content/attribute/{{ att.id }}/input_form">{{ att.input_type }}</a>
            </td>
            <td>
                <a href="content/attribute/{{ att.id }}/edit" class="btn btn-warning">edit</a>
            </td>
        </tr>
        {% endfor %}
    </table>
    <form method="post" action="content/attributes" class="well form-inline">
        <h3>Add an Attribute</h3>
        <input type="text" name="name">
        <input type="submit" class="btn btn-primary" value="add">
    </form>
</div>
{% endblock %}
