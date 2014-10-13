<!DOCTYPE HTML>
<html>
	<head>
		<title>RT Fan Awards</title>

		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="stylesheet" href="http://127.0.0.1/HTML/RTAwards/css/styles.css" />
		<link rel="stylesheet" href="http://127.0.0.1/HTML/RTAwards/css/bootstrap.min.css">
		<link rel="stylesheet" href="http://127.0.0.1/HTML/RTAwards/css/bootstrap-theme.min.css">
		<link href='http://fonts.googleapis.com/css?family=Exo+2:400,500,600,900,700italic' rel='stylesheet' type='text/css'>

		<!--<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js" type="text/javascript"></script>-->
		<script src="http://127.0.0.1/HTML/RTAwards/js/jquery/jquery.min.js" type="text/javascript"></script>
		<script src="http://127.0.0.1/HTML/RTAwards/js/bootstrap/bootstrap.min.js"></script>
		<script src="http://127.0.0.1/HTML/RTAwards/js/transit/jquery.transit.min.js"></script>
		<script src="http://127.0.0.1/HTML/RTAwards/js/javascript.js"></script>
		
		<?php
			session_start();
			if(isset($_SESSION['email']) === false){
				$_SESSION['email'] = 'null';
			}
			$connect = mysqli_connect("localhost", "root", "");
			mysqli_select_db($connect, "rtawards");
			
			$stateConnect = new mysqli("localhost", "root", "", "rtawards");
			
			$errors = "";
			
			//********** Clean URL **********\\
			function cleanURL(){
				ob_start(); // ensures anything dumped out will be caught
				$url = 'http://127.0.0.1/HTML/RTAwards/index.php#movies';
				
				// clear out the output buffer
				while (ob_get_status()) 
				{
				    ob_end_clean();
				}
				
				// no redirect
				header( "Location: $url" );
			}
			
			if(isset($_GET['function']) === true){
				switch($_GET['function']){
					case "vote":
						vote();
						cleanURL();
					break;
					
					case "auth":
						auth();
						cleanURL();
					break;
					
					case "register":
						register();
					break;
					
					case "logout":
						logout();
						cleanURL();
					break;
				}
			}
			
			//********** Logout Code **********\\
			function logout(){
				$_SESSION['email'] = 'null';
			}
			
			
			//********** Register Code **********\\
			function register(){
				global $connect, $stateConnect;
				
				$stage = 0;
				$salt = "No Data";
				$salt = getdate();
				$salt = $salt[0];
				$user['email'] = "No Data";
				$user['authCode'] = "No Data";
				
				$userAuthCode = "No data";
				
				if(isset($_GET['stage']) === true){
					$stage = $_GET['stage'];
				}
				
				if(isset($_GET['email']) === true){
					$user['email'] = $_GET['email'];
				}
				
				if(isset($_GET['authCode']) === true){
					$userAuthCode = $_GET['authCode'];
				}
				
				$authCode = sha1($salt . $user['email']);
				$authCode = strtoupper(substr($authCode, 0, 6));
				
				$subject = 'RT Fan Awards Registration';
				
				$headers = "From: no-reply@noahhuppert.com\r\n";
				$headers .= "CC: noahhuppert@gmail.com\r\n";
				$headers .= "MIME-Version: 1.0\r\n";
				$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
				
				$message = "<html><head> <style>#emailBbody{background: url('http://127.0.0.1/HTML/RTAwards/img/pageBackground.jpg') repeat-x;}#emailHeader{width: 100%; margin-top: 25px; text-align: center; font-size: 44px; font-weight: bold; color: #cd2727; font-family: 'Exo 2', sans-serif;}#emailPage{max-width: 1000px; margin: auto; background: #FFFFFF; padding: 50px; padding-top: 10px; font-weight: 700; font-size: 20px; color: #cd2727; font-family: 'Exo 2', sans-serif;}#emailCode{width: 110px;height: 50px;line-height: 50px;margin: auto;margin-top: 20px;font-weight: 700; font-size: 28px; color: #000000; font-family: 'Exo 2', sans-serif;border-style: solid;border-width: 1px;border-color: #95a5a6;border-radius: 5px;}#emailFooter{font-size: 12px;opacity: 0.5;}</style></head><body id='emailBbody'><div id='emailHeader'>Rooster Teeth Fan Awards</div><div id='emailPage'>Thank you for signing up for the RT Fan Awards<br>In order to complete your registration you must first verify your email by entering in the code below into the desinated areaon the site.<br><div id='emailCode'>" . $authCode . "</div>STAGE: " . $stage . "<br>After your email has been verfied you can vote for your favorite movies, games and more!<hr><div id='emailFooter'>If you did not sign up for the RT Fan Awards do not worry, no other information has been entered and your email will not beable to be used until it has been verified.</div></div></body></html>";
				if(strcmp($stage, 0) == 0){
					$sql = "INSERT INTO `rtawards`.`users` (`userEmail`, `userAuthCode`) VALUES ('" . $user['email'] . "', '" . $authCode . "')";
					$query = mysqli_query($connect, $sql);
					mail($user['email'], $subject, $message, $headers);
					ob_start(); // ensures anything dumped out will be caught
					$url = 'http://127.0.0.1/HTML/RTAwards/index.php?email=' . $user['email'] . '&stage=1'; // this can be set based on whatever
					
					// clear out the output buffer
					while (ob_get_status()) 
					{
					    ob_end_clean();
					}
					
					// no redirect
					header( "Location: $url" );
				} elseif(strcmp($stage, 1) == 0){
					$dbUserAuthCode = "";
					$foo;
					$sql = "SELECT `userAuthCode` FROM `users` WHERE `userEmail`=?";
					
					$statement = $stateConnect->prepare($sql);
					
					$statement->bind_param('s',$user['email']);
					
					$statement->execute();
					
					$statement->bind_result($dbUserAuthCode);
					
					if($statement->fetch() && strcmp($userAuthCode, $dbUserAuthCode) == 0){
						unset($sql);
						unset($statement);
						$sql = "UPDATE `users` SET `userAuthCode`=? WHERE `userEmail`=?";
						$statement = $stateConnect->prepare($sql);
						
						$statement->bind_param('ss', $foo = "auth", $user['email']);
						
						$statement->execute();
					}
				}
				
			}
			
			//********** Login Code *********\\
			function auth(){
				global $stateConnect, $errors;
				
				$email = "No Data";
				$user['email'] = "No Data";
				$user['authCode'] = "No Data";
				
				if(isset($_GET['email']) === true){
					$email = $_GET['email'];
				}
				
				$sql = "SELECT `userEmail`, `userAuthCode` FROM `users` WHERE `userEmail`=?";
					
				$statement = $stateConnect->prepare($sql);
				
				$statement->bind_param('s', $email);
				
				$statement->execute();
				
				$statement->bind_result($user['email'], $user['authCode']);
				
				if($statement->fetch()){
					if(strcmp($user['authCode'], 'auth') == 0){
						$_SESSION['email'] = $user['email'];
					} else{
						$errors .= 'Please check your email for a verification code and enter it in the register form.';
					}
				} else{
					$errors .= 'Invalid email';
				}
			}
			
			//********** Check if user has voted on item code **********
			function checkItemVoteStatus($item){
				global $stateConnect;
				
				$itemVotes = 0;
				
				//Querying
				unset($statement);//Remove any prevous query data
				$sql = "SELECT `userEmail` FROM `votes` WHERE `itemName`=?";
					
				$statement = $stateConnect->prepare($sql);
				
				$statement->bind_param('s', $itemName);
				
				$statement->execute();
				
				$statement->bind_result($userEmail);
				
				while($statement->fetch()){//Count the number of results
					if(strcmp($userEmail, $_SESSION['email']) == 0){
						$itemVotes = $itemVotes + 1;	
					}
				}
				
				if(getItemVotes($item) == 0){
					return false;
				} elseif(getItemVotes($item) != 0){
					return true;
				}
			}
			
			//********** Voting Code **********\\
			function vote(){
				global $stateConnect;
				
				$voteItems = array();
				$voteItems[0] = "No Data";
				
				if(isset($_GET['votes']) === true){
					$voteItems = explode(',', $_GET['votes']);
				}
				
				foreach($voteItems as $value){
					if(strcmp($value, '') != 0){
						if(checkItemVoteStatus($value) === false){
							addVote($value);
						} elseif(checkItemVoteStatus($value) === true){
							removeVote($value);
						}
					}
				}
				
			}
			
			
			//********** Add vote code **********\\
			function addVote($itemName){
				global $stateConnect;
				
				//Querying
				unset($statement);//Remove any prevous query data
				$sql = "INSERT INTO `votes`(`userEmail`, `itemName`) VALUES (?, ?)";
					
				$statement = $stateConnect->prepare($sql);
				
				$statement->bind_param('ss', $_SESSION['email'], $itemName);
				
				$statement->execute();
			}
			
			
			//********** Remove vote code **********\\
			function removeVote($itemName){
				global $stateConnect;
				
				//Querying
				unset($statement);//Remove any prevous query data
				$sql = "DELETE FROM `votes` WHERE `itemName`=? AND `userEmail`=?";
					
				$statement = $stateConnect->prepare($sql);
				
				$statement->bind_param('ss', $itemName, $_SESSION['email']);
				
				$statement->execute();
			}
			
			
			//********** Get Item Votes Code **********\\
			function getItemVotes($itemName){
				global $stateConnect;
				
				$itemVotes = 0;
				
				//Querying
				unset($statement);//Remove any prevous query data
				$sql = "SELECT `userEmail` FROM `votes` WHERE `itemName`=?";
					
				$statement = $stateConnect->prepare($sql);
				
				$statement->bind_param('s', $itemName);
				
				$statement->execute();
				
				while($statement->fetch()){//Count the number of results
					$itemVotes = $itemVotes + 1;
				}
				
				return $itemVotes;
			}
			
			
			//********** Show Catagory Items Code **********\\
			function showCatagory($catagory){
				global $stateConnect;
				
				$items = array();
				$item = array();
				$results['itemName'] = "No Data";
				$results['itemDesc'] = "No Data";
				$results['itemLink'] = "No Data";
				
				//Querying
				unset($statement);//Remove any prevous query data
				$sql = "SELECT `itemName`, `itemDesc`, `itemLink` FROM `items` WHERE `itemCatagory`=?";
					
				$statement = $stateConnect->prepare($sql);
				
				$statement->bind_param('s', $catagory);
				
				$statement->execute();
				
				$statement->bind_result($results['itemName'], $results['itemDesc'], $results['itemLink']);
				
				while($statement->fetch()){//Putting all query results in array
					array_push($items, array("name" => $results['itemName'], "desc" => $results['itemDesc'], "link" => $results['itemLink']));
				}
				
				foreach($items as $value){//Displaying items
					foreach($value as $subKey => $subValue){//Puting all results for current item in local array
						$item[$subKey] = $subValue;
					}
					$item['votes'] = getItemVotes($item['name']);
					
					if(checkItemVoteStatus($item['name']) === true){//Make item display as voted or not
						$voteBoxPrefix = "voted";
					} else{
						$voteBoxPrefix = "";
					}
					
					//Displaying actual item
					echo "
					<div class='option' data-link='" . $item['link'] . "'>
						<div class='content'>
							" . $item['desc'] . "
						</div>
						<div class='title'>
							" . $item['name'] . "
						</div>
						<div class='votes'>
							<span class='glyphicon glyphicon-user'></span> " . $item['votes'] . "
						</div>
						
					<div class='voteBox " . $voteBoxPrefix . "' data-item='" . $item['name'] . "'>
						<span class='glyphicon glyphicon-star'></span> Vote
					</div>
						
					</div>
					";
				}
			
			}
		?>
	</head>
	<body onload="init();">
		<div id="userWrapper">
			<div id="errors" class="hidden">
				<?php
					if(strcmp($errors, '') != 0){
						echo "<div id='errors'>" . $errors . "</div>";
					}
				?>
			</div>
			<div id="loginContent">
				<div id="loginLinks">
					<?php
						if(isset($_SESSION['email']) === true){
							if(strcmp($_SESSION['email'], 'null') != 0){
								echo "<span class='hidden'>" . $_SESSION['email'] . "</span><form class='loginLink' id='logout'><input type='text' name='function' value='logout' class='hidden'><input type='submit' value='Logout'></form>";
							} else{
								echo "<span class='loginLink' id='login'>Login</span> or <span class='loginLink' id='register'>Register</span>";
							}
						} else{
							echo "<span class='loginLink' id='login'>Login</span> or <span class='loginLink' id='register'>Register</span>";
						}
					?>
				</div>
				<form id="loginForm">
					<label>Email:</label>
					<input type='text' name='email' placeholder='Email'>
					<input type='text' name='function' class='hidden' value='auth'>
					<input type='submit' value="Login">
				</form>
				<form id="registerForm">
					<?php
						if(isset($_GET['stage']) === true){
							if($_GET['stage'] == 0){
								echo "<label>Email:</label><input type='text' name='email' id='rEmail' placeholder='Email'>";	
							}
							if($_GET['stage'] == 1){
								echo "<label>Auth Code:</label><input style='display: block !important;' type='text' name='authCode' id='rAuthCode'><input type='text' name='stage' value='1' class='hidden'><input type='text' name='email' class='hidden' value='" . $_GET['email'] . "'>";	
							}
						} else{
							echo "<label>Email:</label><input type='text' name='email' id='rEmail' placeholder='Email'>";
						}
					?>
					
					<input type='text' name='function' class='hidden' value='register'>
					<input type='submit' value="Register">
				</form>
			</div>
		</div>
		
		<header>
			Rooster Teeth Fan Awards
		</header>
		<div id="navPlaceholder" style="height: 0px; max-width: 1000px;">
			&nbsp;
		</div>
		<div id="navContainer">
			<div id="headerNavBar">
				<div class="navLink">
					<a href="#movies">Movies</a>
				</div>
				<div class="navLink">
					<a href="#games">Games</a>
				</div>
				<div class="navLink">
					<a href="#indieGames">Indie Games</a>
				</div>
				<div class="navLink">
					<a href="#dlc">DLC</a>
				</div>
				<div class="navLink">
					<a href="#internetVid">Internet Videos</a>
				</div>
				<div class="navLink">
					<a href="#tvShow">TV Show</a>
				</div>
			</div>
		</div>
			
		<div id="page">
			
			<div id="movies" >
				<div class="catagory" style="margin-top: 0 !important;">
					<span class="glyphicon glyphicon-film"></span> Movies
				</div>
				<div class="catagoryPageContent">
					
					<?php
						showCatagory('Movies');
					?>
				</div>
			</div>
			
			<div id="games">
				<div class="catagory">
					<span class="glyphicon glyphicon-tower"></span> Games
				</div>
				<div class="catagoryPageContent">
					
					<?php
						showCatagory('Games');
					?>
				</div>
			</div>
			
			<div id="indieGames">
				<div class="catagory">
					<span class="glyphicon glyphicon-send"></span> Indie Games
				</div>
				<div class="catagoryPageContent">
					
					<?php
						showCatagory('Indie Games');
					?>
				</div>
			</div>
			
			<div id="dlc">
				<div class="catagory">
					<span class="glyphicon glyphicon-shopping-cart"></span> DLC
				</div>
				<div class="catagoryPageContent">
					
					<?php
						showCatagory('DLC');
					?>
				</div>
			</div>
			
			<div id="internetVid">
				<div class="catagory">
					<span class="glyphicon glyphicon-play-circle"></span> Internet Video
				</div>
				<div class="catagoryPageContent">
					
					<?php
						showCatagory('Internet Video');
					?>
				</div>
			</div>
			
			<div id="tvShows">
				<div class="catagory">
					<span class="glyphicon glyphicon-picture"></span> TV Shows
				</div>
				<div class="catagoryPageContent">
					
					<?php
						showCatagory('Tv Show');
					?>
				</div>
			</div>
			
			<form>
				<input type='text' name="function" class="hidden" value="vote">
				<input type="text" name="votes" id="votes" class="hidden">
				<!--<input type='text' name='unvotes' id="unvotes" class='hidden'>-->
				<br><br><br>
				<?php
					if(isset($_SESSION['email']) === true){
						if(strcmp($_SESSION['email'], 'null') != 0){
							echo "<input type='submit' value='Vote'>";
						} else{
							echo "<input type='submit' disabled='disabled' class='inActive' title='Please login' value='Vote'>";
						}
					} else{
						echo "<input type='submit' disabled='disabled' class='inActive' title='Please login' value='Vote'>";
					}
				?>
			</form>
		</div>
		
		<div id="footer">
			All code on this domain is created by <a href="http://www.noahhuppert.com">Noah Huppert</a><br>
			<br>
			<span id="backgroundTextNotice">The background of this page was created by Rooster Teeth. I do not take <strong style="font-weight: bolder; font-size: 16px;">any credit</strong> for it.</span>
		</div>
		<div id="imageCredit">
			DID I MENTION I DIDN'T MAKE THE BACKGROUND?
			<button id="cancleImageCred">Yea
				<div id="imgHint">
					<span class="glyphicon glyphicon-arrow-up">Hint, Click me!</span>
				</div>
			</button>
		</div>
	</body>
</html>