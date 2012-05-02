<!DOCTYPE HTML>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <base href="{{ module_root }}"/>
		<title>DASe Framework Admin</title>
        <link rel="stylesheet" type="text/css" href="../../www/css/bootstrap.css">
        <link rel="stylesheet" type="text/css" href="../../www/css/local.css">
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js"></script>
        <script src="../../www/js/script.js"></script>
	</head>
	<body>
        <div class="navbar navbar-fixed-top">
            <div class="navbar-inner">
                <div class="container">
                    <a class="brand" href="#">Dase Framework Admin</a>
                    {% if request.user %}
                    <div class="navbar-text pull-right" id="login">
                    <a href="login/{{ request.user.eid }}" class="btn btn-mini btn-inverse delete">logout {{ request.user.eid }}</a>
                    </div>
                    {% endif %}
                </div>
            </div>
        </div>
		<div class="container">
			<div class="content">
				<ul id="tasks" class="unstyled">
                    <li<h3><a href="cache" class="delete btn btn-warning">delete cache</a></h3></li>
                    <li><h3><a href="log" class="delete btn btn-warning">delete log</a></h3></li>
				</ul>
			</div>
		</div>
	</body>
</html>
