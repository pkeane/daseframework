
{% block content %}

<form action="content/items/{{ item.id }}/swap" class="well form-horizontal" method="post" enctype="multipart/form-data">
    {% if item.file_url %}
    <h3>Swap-In File</h3>
    <input type="file" name="uploaded_file"/>
    <input type="hidden" value="{{ not_set }}" name="not_set">
    <input type="hidden" value="{{ page }}" name="page">
    <input type="hidden" value="{{ q }}" name="q">
    <input type="hidden" value="{{ att }}" name="att">
    <input type="hidden" value="{{ val }}" name="val">
    <input type="hidden" value="{{ type }}" name="type">
    <input type="hidden" value="{{ max }}" name="max">
    <input type="hidden" value="{{ num }}" name="num">
    <input type="hidden" value="{{ display }}" name="display">
    <input type="submit" class="btn btn-warning" value="swap in file"/>
    {% else %}
    <h3>Add File</h3>
    <input type="file" name="uploaded_file"/>
    <input type="hidden" value="{{ not_set }}" name="not_set">
    <input type="hidden" value="{{ page }}" name="page">
    <input type="hidden" value="{{ q }}" name="q">
    <input type="hidden" value="{{ att }}" name="att">
    <input type="hidden" value="{{ val }}" name="val">
    <input type="hidden" value="{{ type }}" name="type">
    <input type="hidden" value="{{ max }}" name="max">
    <input type="hidden" value="{{ num }}" name="num">
    <input type="hidden" value="{{ display }}" name="display">
    <input type="submit" class="btn btn-warning" value="add file"/>
    {% endif %}
</form>

{% if item.file_url %}
<form action="content/items/{{ item.id }}/files" class="well form-horizontal" method="delete">
    <input type="hidden" value="{{ not_set }}" name="not_set">
    <input type="hidden" value="{{ page }}" name="page">
    <input type="hidden" value="{{ q }}" name="q">
    <input type="hidden" value="{{ att }}" name="att">
    <input type="hidden" value="{{ val }}" name="val">
    <input type="hidden" value="{{ type }}" name="type">
    <input type="hidden" value="{{ max }}" name="max">
    <input type="hidden" value="{{ num }}" name="num">
    <input type="hidden" value="{{ display }}" name="display">
    <input type="submit" class="btn btn-danger" value="delete file"/>
    {% else %}
</form>
{% endif %}

{% endblock %}
