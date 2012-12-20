<?php $config = ManiaLib\Application\Config::getInstance(); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title><?php echo $config->manialink; ?></title>
		<style type="text/css">
		
			body {
				background: #111111;
				color: #ffffff;
				font-family: Verdana, Arial, Helvetica, sans-serif;
				font-size: 12px;
				line-height: 15px;
			}
			
			#frame {
				width: 640px;
				margin: 75px auto;
			}
			
			h1 {
				color: #66ccff;
				text-align: center;
				margin-bottom: 50px;
			}
			
			p {
				text-align: justify;
			}
			
			a, a:visited {
				color: #66ccff;
				text-decoration: underline;
			}
			
			a:hover, a:active {
				color: #ffffff;
			}
		</style>
	</head>
	<body>
		<div id="frame">
			<h1><?php echo $config->manialink; ?></h1>
			<p>
			The page your are trying to access is a Manialink for Maniaplanet. 
			You can only view it using the in-game browser.
			</p>
			
			<p>
			To access it, <a href="maniaplanet:///:<?php echo $config->manialink; ?>">click here</a> or 
			launch Maniaplanet and go to the <b><?php echo $config->manialink; ?></b> Manialink.
			</p>
		</div>
	</body>
</html>
