{% extends "framework/bootstrap.tpl" %}

{% block content %}
<div class="container">
    <p>
    <a href="content/item/{{ item.id }}/edit" class="btn btn-warning">edit item</a>
    </p>

<table class="table table-striped table-bordered table-condensed">
    <tbody>
        <tr><th scope="row">Name</th><td>{{ item.name }}</td></tr>
        <tr><th scope="row">Title</th><td>{{ item.title }}</td></tr>
        <tr><th scope="row">Body</th><td>{{ item.body }}</td></tr>
        <tr><th scope="row">Type</th><td>{{ item.type }}</td></tr>
        <tr><th scope="row">Url</th><td>{{ item.url }}</td></tr>
        <tr><th scope="row">File Url</th><td>{{ item.file_url }}</td></tr>
        <tr><th scope="row">Thumbnail Url</th>
            <td>
                <img src="{{ item.thumbnail_url }}">
                {{ item.thumbnail_url }}
            </td>
        </tr>
        <tr><th scope="row">View Url</th><td>{{ item.view_url }}</td></tr>
        <tr><th scope="row">File Path</th><td>{{ item.file_path }}</td></tr>
        <tr><th scope="row">Thumbnail Path</th><td>{{ item.thumbnail_path }}</td></tr>
        <tr><th scope="row">View Path</th><td>{{ item.view_path }}</td></tr>
        <tr><th scope="row">Filesize</th><td>{{ item.filesize }}</td></tr>
        <tr><th scope="row">File Ext</th><td>{{ item.file_ext }}</td></tr>
        <tr><th scope="row">Mime</th><td>{{ item.mime }}</td></tr>
        <tr><th scope="row">Width</th><td>{{ item.width }}</td></tr>
        <tr><th scope="row">Height</th><td>{{ item.height }}</td></tr>
        <tr><th scope="row">Lat</th><td>{{ item.lat }}</td></tr>
        <tr><th scope="row">Lng</th><td>{{ item.lng }}</td></tr>
        <tr><th scope="row">Created</th><td>{{ item.created }}</td></tr>
        <tr><th scope="row">Created By</th><td>{{ item.created_by }}</td></tr>
        <tr><th scope="row">Updated</th><td>{{ item.updated }}</td></tr>
        <tr><th scope="row">Updated By</th><td>{{ item.updated_by }}</td></tr>
    </tbody>
</table>

</div>

{% endblock %}
