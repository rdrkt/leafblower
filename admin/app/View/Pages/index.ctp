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

        <script src="js/vendor/modernizr-2.6.2-respond-1.1.0.min.js"></script>
    </head>
    <body>
        
        <header class="clearfix">
			<h1>Leafblower</h1>
            <nav>
				<a href="#add-profile" title="Add profile">
					<img src="images/add-profile.png" alt="Add profile">
				</a>
				<a href="#list-profiles" title="List profile">
					<img src="images/list-profiles.png" alt="List profile">
				</a>
			</nav>
        </header>

        <main class="clearfix">
            
			<aside>
				<ul id="block-menu">
				</ul>
			</aside>
			
			<section id="add-profile">
				<h2>Fill in the form to create a profile</h2>
				<form name="frm-add-profile">
					<label for="txtProfileName">Profile Name</label>
					<input type="text" value="" id="txtProfileName" name="txtProfileName" />
					<label for="txtProfileName">Profile Name</label>
					<textarea id="txtProfileDescription" name="txtProfileDescription">
				</form>
			</section>
			
			<section id="list-profiles" class="active">
				<ul></ul>
			</section>
			
			<section id="update-profile">
				<form name="frm-add-profile">
					<label for="txtProfileName"></label>
					
					<input type="hidden" name="hdnProfileId" id="hdnProfileId" value="">
				</form>
			</section>
						
        </main>

        <footer>
            <p class="powered">Leafblower by <a href="http://www.rdrkt.com" target="_blank" title="RDRKT">RDRKT.com</a></p>
        </footer>
        
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
        <script>            window.jQuery || document.write('<script src="js/vendor/jquery-1.9.1.min.js"><\/script>')</script>
        <script src="js/plugins.js"></script>
        <script src="js/main.js"></script>
    </body>
</html>
