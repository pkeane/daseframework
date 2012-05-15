
{% block content %}

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

<form method="post" action="content/items/{{ item.id }}/metadata" id="bulk_add" class="well form-inline">
    <input type="hidden" name="items" value="|">
    <h3>Add Metadata to Item</h3>
    <select name="attribute_id">
        <option value="">select an attribute:</option>
        {% for att in atts %}
        <option value="{{ att.id }}">{{ att.name }}</option>
        {% endfor %}
    </select>
    <span id="att_input_form"></span>
    <input type="submit" class="btn btn-primary" value="add metadata">
</form>

{% endblock %}
