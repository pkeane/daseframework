<!DOCTYPE html>
<html>
    <head>
        <base href="{{ app_root }}/">
        <meta charset="utf-8">
        <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
        <meta name="dataset-lat" content="{{ lat }}">
        <meta name="dataset-lng" content="{{ lng }}">
        <style type="text/css">
            html { height: 100% }
            body { height: 95%;padding: 0 }
            form {margin: 10px; background-color: #dde; padding: 10px; width: 600px;}
            #map_canvas { height: 90%; width: 100%; }
            div.latlng { margin-top: 8px; font-weight: bold; font-size: 11px; font-family: verdana, sans-serif;}
            div.latlng input { width: 144px; margin-right: 4px;}
        </style>
        <script src="www/js/jquery.js"></script>
        <script src="www/js/jquery-ui.js"></script>
        <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key={{ request.appSettings.google_maps_api_key }}&sensor=false"></script>
        <script src="www/js/daseframework.map.js"></script>
        </head>
        <body>
            <div id="map_canvas"></div>
            <div class="latlng">
                <form id="locform" action="{{ app_root }}content/item/{{ item.id }}/location" method="post">
                    Latitude: <input type="text" name="lat" value="{{ lat }}">
                    Longitude: <input type="text" name="lng" value="{{ lng }}">
                    <input type="submit" value="save location">
                </form>
            </div>
        </body>
    </html>


