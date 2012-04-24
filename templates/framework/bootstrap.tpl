<!DOCTYPE html>
<html lang="en">
	<head>
        <base href="{{ app_root }}/">
		<meta charset="utf-8">
        {% block headmeta %}
        {% endblock %}


        <title>{% block title %}{{ main_title }}{% endblock %}</title>

		<!-- Le styles -->
		<link href="www/css/bootstrap.css" rel="stylesheet">
		<link href="www/css/bootstrap-responsive.css" rel="stylesheet">
		<link href="www/css/local.css" rel="stylesheet">

		<!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
		<!--[if lt IE 9]>
		<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->

		{% block headlinks %}{% endblock %}

		{% block headjs %}
		<!--
		<script src="http://code.jquery.com/jquery-latest.min.js"></script>
		<script src="http://code.jquery.com/ui/1.8.19/jquery-ui.min.js"></script>
		-->
		<script src="www/js/jquery.js"></script>
		<script src="www/js/jquery-ui.js"></script>
		{% endblock %}

		{% block head %}{% endblock %}
		<script src="www/js/script.js"></script>
	</head>

	<body>

		{% block header %}
		<div class="navbar navbar-fixed-top">
			<div class="navbar-inner">
				<div class="container">
                    <a class="brand" href="#">{{ main_title }}</a>
					<ul class="nav">
						<li class="active"><a href="home">Home</a></li>
                        {% if request.user %}
						{% if request.user.is_admin %}
						<li><a href="admin">admin</a></li>
						{% endif %}
                        <li id="login"><a href="login/{{ request.user.eid }}" class="delete">logout {{ request.user.eid }}</a></li>
						{% endif %}
					</ul>
				</div>
			</div>
		</div>
		{% endblock %}

		<div class="container">
			{% block main %}
            {% if msg %}<h3 class="msg">{{ msg }}</h3>{% endif %}
			{% block content %}

			<!-- Main hero unit for a primary marketing message or call to action -->
			<div class="hero-unit">
                <h1>{{ main_title }}</h1>
				<p>This is a template for a simple marketing or informational website. It includes a large callout called the hero unit and three supporting pieces of content. Use it as a starting point to create something more unique.</p>
				<p><a class="btn btn-primary btn-large">Learn more &raquo;</a></p>
			</div>

			<!-- Example row of columns -->
			<div class="row">
				<div class="span4">
					<h2>Heading</h2>
					<p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod. Donec sed odio dui. </p>
					<p><a class="btn" href="#">View details &raquo;</a></p>
				</div>
				<div class="span4">
					<h2>Heading</h2>
					<p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod. Donec sed odio dui. </p>
					<p><a class="btn" href="#">View details &raquo;</a></p>
				</div>
				<div class="span4">
					<h2>Heading</h2>
					<p>Donec sed odio dui. Cras justo odio, dapibus ac facilisis in, egestas eget quam. Vestibulum id ligula porta felis euismod semper. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus.</p>
					<p><a class="btn" href="#">View details &raquo;</a></p>
				</div>
			</div>

			<div class="row">
				<div class="span6">
					<h2>Heading</h2>
					<p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod. Donec sed odio dui. </p>
					<p><a class="btn" href="#">View details &raquo;</a></p>
				</div>
				<div class="span6">
					<h2>Heading</h2>
					<p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod. Donec sed odio dui. </p>
					<p><a class="btn" href="#">View details &raquo;</a></p>
				</div>
			</div>

			{% endblock %}

			<hr>
			<footer>
			{% block footer %}
			<a href="http://www.utexas.edu">The University of Texas at Austin</a>
			|
			<a href="http://www.utexas.edu/cola/information-technology/">Liberal Arts ITS</a>
			{% endblock %}
			</footer>

			{% endblock %}
		</div> <!-- /container -->

	</body>
</html>
