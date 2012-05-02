{% if 'text' == att.input_type %}
<input type="text" name="value_text">
{% elseif 'select' == att.input_type %}
<select name="value_text">
    <option value="">select one:</option>
    {% for v in att.values %}
    <option>{{ v }}</option>
    {% endfor %}
</select>
{% endif %}

