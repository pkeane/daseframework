{% extends "framework/bootstrap.tpl" %}

{% block content %}
<div>
	<div class="controls">
		<a href="item/{{ item.id }}">view item</a>
	</div>
	<h1>Edit Item</h1>
	<form action="content/item/{{ item.id }}/edit" method="post">
		<label for="title">title</label>
		<input type="text" name="title" value="{{ item.title }}"/>
		<label for="body">body</label>
		<textarea name="body">{{ item.body }}</textarea>

		<p>
		<input type="submit" value="update"/>
		</p>
	</form>
	{% if item.file_url %}
	<h1>Swap in File</h1>
	{% else %}
	<h1>Add a File</h1>
	{% endif %}
	<form action="item/{{ item.id }}/swap" method="post" enctype="multipart/form-data">
		<p>
		<label for="uploaded_file">select a file</label>
		<input type="file" name="uploaded_file"/>
		{% if item.file_url %}
		<input type="submit" value="swap in file"/>
		{% else %}
		<input type="submit" value="add file"/>
		{% endif %}
		</p>
	</form>
	{% if item.file_url %}
	<img src="{{ item.thumbnail_url }}">
	{% endif %}
</div>
	<div class="controls">
		<form action="item/{{ item.id }}" method="delete">
			<input type="submit" value="delete item">
		</form>
	</div>
	<div class="clear"></div>

{% endblock %}
