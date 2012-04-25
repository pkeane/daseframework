{% extends "framework/bootstrap.tpl" %}

{% block content %}

<div class="controls">
	view items |
	<a href="set/list">view sets</a> |
	<a href="admin/create">create content</a> |
	<a href="set/form">create a set</a>
</div>
<h3>Items</h3>
<table class="table table-striped" id="items">
	<tr>
		<th></th>
		<th>thumbnail</th>
		<th>name</th>
		<th>title</th>
		<th>created</th>
		<th>created by</th>
		<th>file</th>
		<th>edit</th>
		<th>json</th>
	</tr>
	{% for item in items %}
	<tr>
		<td>
			<input type="checkbox" name="item[]" value="{{ item.id }}">
		</td>
		<td class="thumb">
			<a href="item/{{ item.id }}"><img src="{{ item.thumbnail_url }}"></a>
		</td>
		<td>
			{{ item.name }}
		</td>
		<td>
			{{ item.title }}
		</td>
		<td>
			{{ item.created|date("Y-m-d") }}
		</td>
		<td>
			{{ item.created_by }}
		</td>
		<td>
			<a href="{{ item.file_url }}">{{ item.file_url }}</a>
		</td>
		<td>
			<a href="item/{{ item.id }}/edit">edit</a>
		</td>
		<td>
			<a href="{{ item.url }}.json">json</a>
		</td>
	</tr>
	{% endfor %}
</table>
<div id="toggle_check" class="toggle_check">
<a href="#">check/uncheck all</a>
</div>
<div class="controls">
	<a href="set/form">create a set</a>
</div>
	<select name="set_id">
		<option value="">add checked items to set:</option>
		{foreach item=s from=$sets }}
		<option value="{{ s.id }}">{{ s.title }}</option>
		{/foreach }}
	</select>
	<input type="submit" value="add all">
</form>


{% endblock %}
