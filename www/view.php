<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title></title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width">

        <link rel="stylesheet" href="css/normalize.min.css">
        <link rel="stylesheet" href="css/main.css">
        <link rel="stylesheet" href="css/template.css">
        <link rel="stylesheet" href="css/mediaqueries.css">

        <script src="js/vendor/modernizr-2.6.2-respond-1.1.0.min.js"></script>
    </head>
    <body class="view-profile">
        
        <div id="loading-overlay"></div>
		<img id="loading-gif" src="images/loading.gif" alt="Loading..." title="Loading..." />

        <header class="clearfix">
			<a class="title" href="http://leafblower.rdrkt.com" target="_blank"><img src="images/leafblower-logo.png" alt="Leafblower" /></a>
            <nav>
				<a href="list.php" title="List profile" class="list-profiles">
					List profile
				</a>
			</nav>
        </header>

        <main class="clearfix">
            
        </main>

        <footer>
            <p class="powered">Leafblower by <a href="http://www.rdrkt.com" target="_blank" title="rdrkt">rdrkt.com</a></p>
        </footer>

        
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
        <script>            window.jQuery || document.write('<script src="js/vendor/jquery-1.9.1.min.js"><\/script>')</script>
        <script src="js/vendor/d3.3.1.5-min.js"></script>
        <script src="js/plugins.js"></script>
        <script src="http://leafblower.rdrkt.com:8080/socket.io/socket.io.js"></script>
		
		<?php #Needs automating ?>
        <script src="js/blocks/countingBeanstalkd.js"></script>
        <script src="js/blocks/countingMongodb.js"></script>
        <script src="js/blocks/geoCheckIns.js"></script>
        <script src="js/blocks/memory.js"></script>
        <script src="js/blocks/tasks.js"></script>
		<script src="js/blocks/mongoServerStats.js"></script>
		<script src="js/blocks/mongoSlowQuery.js"></script>
		
        <script src="js/blocksController.js"></script>
        <script src="js/main.js"></script>
    </body>
</html>
