{% extends "framework/bootstrap.tpl" %}

{% block content %}
<div>
    <div class="controls">
        <a href="item/{{ item.id }}/edit">edit item</a> |
        <a href="item/{{ item.name }}.json">item json</a> |
        <a href="items">view items</a>
    </div>
    <h1>Item {{ item.name }}</h1>
    <div class="well container">
        <table class="span6 table table-striped table-bordered table-condensed"><tbody>
                <tr>
                    <tr><th scope="row">title</th>
                        <td>{{ item.title }}</td></tr>
                    <tr><th scope="row">name</th>
                        <td>{{ item.name }}</td></tr>
                    <tr><th scope="row">body</th>
                        <td>{{ item.body }}</td></tr>
                    {% if item.file_url %}
                    <tr><th scope="row">thumbnail</th>
                        <td><img src="{{ item.thumbnail_url }}"></td></tr>
                    <tr><th scope="row">file</th>
                        <td><a href="{{ item.file_url }}">{{ item.file_url }}</a></td></tr>
                    <tr><th scope="row">file mime type</th>
                        <td>{{ item.mime }}</td></tr>
                    <tr><th scope="row">file size</th>
                        <td>{{ item.filesize }}</td></tr>
                    {% endif %}
                    {% if item.width %}
                    <tr><th scope="row">width</th>
                        <td>{{ item.width }}</td></tr>
                    {% endif %}
                    {% if item.height %}
                    <tr><th scope="row">height</th>
                        <td>{{ item.height }}</td></tr>
                    {% endif %}

                    <tr><th scope="row">created</th>
                        <td>{{ item.created|date("Y-m-d") }}</td></tr>
                    <tr><th scope="row">created by</th>
                        <td>{{ item.created_by }}</td></tr>
                    <tr><th scope="row">updated</th>
                        <td>{{ item.updated|date("Y-m-d") }}</td></tr>
                    <tr><th scope="row">updated by</th>
                        <td>{{ item.updated_by }}</td></tr>

            </td></tr>
        </table>
    </div>
</div>

{% endblock %}
