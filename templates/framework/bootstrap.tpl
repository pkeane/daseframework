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
        <link href="www/css/colorbox.css" rel="stylesheet">
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
        <script src="www/js/jquery.colorbox.js"></script>
        <script src="www/js/bootstrap-dropdown.js"></script>
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
                        <li {% if request.handler == 'home' %}class="active"{% endif %}><a href="home">Home</a></li>
                        {% if request.user.is_admin %}
                        <li {% if request.handler == 'admin' %}class="active"{% endif %}><a href="admin">Admin</a></li>
                        {% endif %}
                        <li {% if request.handler == 'content' %}class="active"{% endif %}><a href="content/items">Content</a></li>
                    </ul>
                    {% if request.user %}
                    <div class="navbar-text pull-right" id="login">
                    <a href="login/{{ request.user.eid }}" class="btn btn-mini btn-inverse delete">logout {{ request.user.eid }}</a>
                    </div>
                    {% endif %}
                </div>
            </div>
        </div>
        {% endblock %}

        <div class="container">
            {% block subheader %}{% endblock %}
            {% block main %}
            {% if msg %}<h3 class="msg">{{ msg }}</h3>{% endif %}
            {% block content %}

            <h1>default content</h1>

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
