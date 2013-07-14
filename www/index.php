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
    <body class="user-auth">
        
        <header class="clearfix">
            <h1>Leafblower</h1>
            <nav>
				<a href="list.php" title="List profiles">
					<img src="images/list-profiles.png" alt="List profile">
				</a>
			</nav>
        </header>

        <main>
            
			<form id="frmLogin" method="post" action="">
				<fieldset>
					<legend>Authentication Required</legend>
					<div class="field-wrapper clearfix">
						<label for="txtUsername">Username:</label>
						<input type="text" value="" placeholder="Your username" name="txtUsername" id="txtUsername" />
					</div>
					<div class="field-wrapper clearfix">
						<label for="txtPassword">txtPassword:</label>
						<input type="password" value="" placeholder="******" name="txtPassword" id="txtPassword" />
					</div>
					<div class="field-wrapper clearfix">
						<a href="/forgotten-password" title="Forgotten password?" class="forgotten-password">Forgotten password?</a>
						<input type="submit" name="btnSubmit" value="Login" />
					</div>
				</fieldset>
			</form>
			
        </main>

        <footer>
            <p class="powered">Leafblower by <a href="http://www.rdrkt.com" target="_blank" title="rdrkt">rdrkt.com</a></p>
        </footer>

        
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="js/vendor/jquery-1.9.1.min.js"><\/script>')</script>
        <script src="js/plugins.js"></script>
        <script>
            //document.location = 'list.php'; // temp till auth is added
        </script>
        <script src="js/blocksManager.js"></script>
        <script src="js/main.js"></script>
    </body>
</html>
