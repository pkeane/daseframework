<form method="post" action="content/item/{{ item.id }}/metadata/{{ value.id }}">
{% if 'text' == att.input_type %}
<input type="text" value="{{ value.text }}" name="value_text">
{% elseif 'select' == att.input_type %}
<select name="value_text">
    <option value="">select one:</option>
    {% for v in att.values %}
    <option {%if value.text == v %}selected{% endif %}>{{ v }}</option>
    {% endfor %}
</select>
{% elseif 'textarea' == att.input_type %}
<textarea rows="4" name="value_text">{{ value.text }}</textarea>
{% endif %}
<input type="submit" value="update">
</form>
