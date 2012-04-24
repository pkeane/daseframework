<!doctype html>
<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <base href="{{ module_root }}/"/>
        <title>DASe {{ db }} Admin</title>
        <link rel="stylesheet" type="text/css" href="{{ module_root }}/css/style.css">
		<script type="text/javascript" src="scripts/jquery.js"></script> 
		<script type="text/javascript" src="scripts/dbadmin_jquery.js"></script>
	</head>
	<body>
		<div class="container">
			<div class="branding">
                {{ db }} Admin
			</div>
			<div class="content">

				<ul class="tableSet">
					{% for table in tables|keys %}
                    <li><a href="{{ table }}">{{ table }}</a>
					<ul>
						{% for col  in tables[table] %}
                        <li>{{ col }}</li>
						{% endfor %}
					</ul>
					</li>
					{% endfor %}
				</ul>

			</div>



		</div>
	</body>
</html>
