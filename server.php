<?php
			session_start();
			if(isset($SESSION['email']) === false){
				$SESSION['email'] = 'null';
			}
			$connect = mysqli_connect("localhost", "root", "");
			mysqli_select_db($connect, "rtawards");
			
			$stateConnect = new mysqli("localhost", "root", "", "rtawards");
			
			if(isset($_GET['function']) === true){
				switch($_GET['function']){
					case "vote":
						vote();
					break;
					
					case "auth":
						auth();
					break;
					
					case "register":
						register();
					break;
					
					case "logout":
						logout();
					break;
				}
			}
			
			function logout(){
				global $SESSION;
				$SESSION['email'] = 'null';
			}
			
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
				
				$message = "
				<html>
					<head>
						  <style>
							#emailBbody{
								background: url('http://127.0.0.1/HTML/RTAwards/img/pageBackground.jpg') repeat-x;
							}
							
							#emailHeader{
								width: 100%;
							  	margin-top: 25px;
							  	text-align: center;
							  	font-size: 44px;
							  	font-weight: bold;
							  	color: #cd2727;
							  	font-family: 'Exo 2', sans-serif;
							}
							
							#emailPage{
								max-width: 1000px;
							  	margin: auto;
							  	background: #FFFFFF;
							  	padding: 50px;
							  	padding-top: 10px;
							  	font-weight: 700;
							  	font-size: 20px;
							  	color: #cd2727;
							  	font-family: 'Exo 2', sans-serif;
							}
							
							#emailCode{
								width: 110px;
								height: 50px;
								line-height: 50px;
								margin: auto;
								margin-top: 20px;
								font-weight: 700;
							  	font-size: 28px;
							  	color: #000000;
							  	font-family: 'Exo 2', sans-serif;
								border-style: solid;
								border-width: 1px;
								border-color: #95a5a6;
								border-radius: 5px;
							}

							#emailFooter{
								font-size: 12px;
								opacity: 0.5;
							}
						</style>
					</head>
					<body id='emailBbody'>
						<div id='emailHeader'>Rooster Teeth Fan Awards</div>
						<div id='emailPage'>
							Thank you for signing up for the RT Fan Awards<br>
							In order to complete your registration you must first verify your email by entering in the code below into the desinated area
							on the site.
							<br>
							<div id='emailCode'>
								" . $authCode . "
							</div>
							STAGE: " . $stage . "
							<br>
							After your email has been verfied you can vote for your favorite movies, games and more!
							<hr>
							<div id='emailFooter'>
								If you did not sign up for the RT Fan Awards do not worry, no other information has been entered and your email will not be
								able to be used until it has been verified.
							</div>
						</div>
					</body>
				</html>
				";
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
					
					/*$sql = "SELECT * FROM `users` WHERE `userEmail`='" . $user['email'] . "'";
					 $query = mysqli_query($connect, $sql);
					if ($query -> num_rows > 0) {//Gets all items for catagory
						var_dump($query);
						while($row = mysqli_fetch_array($query)) {
							if(strcmp($userAuthCode, $row['userAuthCode']) == 0){
								$sql = "UPDATE `users` SET userAuthCode`='auth' WHERE 'userEmail'='" . $user['email'] . '"';
								$query = mysqli_query($connect, $sql);
							}
						}
					}*/
				}
				
			}
			
			function auth(){
				global $connect, $stateConnect, $SESSION;
				
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
						$SESSION['email'] = $user['email'];
					} else{
						echo "<script>function errors(){error('Please check your email for a verification code and enter it in the register form.');}</script>";
					}
				} else{
					echo "<script>function errors(){error('Invalid email');}</script>";
				}
				
				/*$sql = "SELECT * FROM `users` WHERE `userEmail`='" . $email . "'";
				$query = mysqli_query($connect, $sql);
				if ($query -> num_rows > 0) {
					while ($row = mysqli_fetch_array($query)) {
						if(strcmp($row['userAuthCode'], 'auth') == 0){
							$SESSION['email'] = $row['userEmail'];
						} else{
							echo "<script>function errors(){error('Please check your email for a verification code and enter it in the register form.');}</script>";
						}
					}
				} else{
					echo "<script>function errors(){error('Invalid email');}</script>";
				}*/
			}
			
			function vote(){
				global $connect, $stateConnect, $SESSION;
				
				
				
				$voteItems;
				$voteItems[0] = "No Data";
				$voteItemsText = "No Data";
				$voteItemsData['null'] = "No Data";
				
				$unVoteItems;
				$unVoteItems[0] = "No Data";
				$unVoteItemsText = "No Data";
				$unVoteItemsData['null'] = "No Data";
				
				$itemVotes = 0;
				
				$userVotes = "";
				
				if(isset($_GET['votes']) === true){
					$voteItemsText = $_GET['votes'];
					$voteItems = explode(",", $voteItemsText);
				}
				
				if(isset($_GET['unvotes']) === true){
					$unVoteItemsText = $_GET['unvotes'];
					$unVoteItems = explode(",", $unVoteItemsText);
				}
				
				
				//********** Voting **********\\
				foreach($voteItems as $value){//Get pre-existing data
					unset($statement);
					$sql = "SELECT `itemVotes` FROM `items` WHERE `itemName`=?";
					
					$statement = $stateConnect->prepare($sql);
					
					$statement->bind_param('s', $value);
					
					$statement->execute();
					
					$statement->bind_result($itemVotes);
					
					if($statement->fetch()){
						$voteItemsData[$value] = $itemVotes;
					}
					
					$itemVotes = 0;
				}
				
				foreach($voteItemsData as $key=>$value){//Add vote Counts
					unset($statement);
					$sql = "UPDATE `items` SET `itemVotes`=? WHERE `itemName`=?";
					
					$statement = $stateConnect->prepare($sql);
					
					$finalItemVotes = $value + 1;
					
					$statement->bind_param('is', $finalItemVotes, $key);
					
					$statement->execute();
				}
				
				//Add vote for user
				//Get user Data
				unset($statement);
				$sql = "SELECT `userVotes` FROM `users` WHERE `userEmail`=?";
				
				$statement = $stateConnect->prepare($sql);
				
				$statement->bind_param('s', $SESSION['email']);
				
				$statement->execute();
				
				$statement->bind_result($userVotes);
				
				if($statement->fetch()){
					$userVotes = $userVotes;
				}
				
				//Set user data
				unset($statement);
				$sql = "UPDATE `users` SET `userVotes`=? WHERE `userEmail`=?";
				
				$statement = $stateConnect->prepare($sql);
				
				foreach($voteItems as $value){
					$userVotes .= ',' . $value;
				}
				
				$statement->bind_param('is', $userVotes, $SESSION['email']);
				
				$statement->execute();
				
				
				//********** Un voting **********\\
				foreach($unVoteItems as $value){//Get pre-existing data
					unset($statement);
					$sql = "SELECT `itemVotes` FROM `items` WHERE `itemName`=?";
					
					$statement = $stateConnect->prepare($sql);
					
					$statement->bind_param('s', $value);
					
					$statement->execute();
					
					$statement->bind_result($itemVotes);
					
					if($statement->fetch()){
						$voteItemsData[$value] = $itemVotes;
					}
					
					$itemVotes = 0;
				}
				
				foreach($unVoteItemsData as $key=>$value){//Add vote Counts
					//Remove vote from item
					unset($statement);
					$sql = "UPDATE `items` SET `itemVotes`=? WHERE `itemName`=?";
					
					$statement = $stateConnect->prepare($sql);
					
					$finalItemVotes = $value - 1;
					
					$statement->bind_param('is', $finalItemVotes, $key);
					
					$statement->execute();
					
					//Remove vote from user
					//Get user Data
					/*unset($statement);
					$sql = "SELECT `userVotes` FROM `users` WHERE `userEmail`=?";
					
					$statement = $stateConnect->prepare($sql);
					
					$statement->bind_param('s', $SESSION['email']);
					
					$statement->execute();
					
					$statement->bind_result($userVotes);
					
					if($statement->fetch()){
						$userVotes = $userVotes;
					}*/
					
					//Set user data
					unset($statement);
					$sql = "UPDATE `users` SET `userVotes`=? WHERE `userEmail`=?";
					
					$statement = $stateConnect->prepare($sql);
					
					foreach($unVoteItems as $value){
						$userVotes = str_replace(',' . $value, '', $userVotes);
					}
					
					$statement->bind_param('is', $userVotes, $SESSION['email']);
					
					$statement->execute();
				}
				
				/*foreach($voteItems as $value){//Get pre-existing data
					$sql = "SELECT `itemVotes` FROM `items` WHERE `itemName`='" . $value . "'";
					$query = mysqli_query($connect, $sql);
					if ($query -> num_rows > 0) {
						while ($row = mysqli_fetch_array($query)) {
							$voteItemsData[$value] = $row['itemVotes'];
						}
					}
				}
				
				foreach($voteItemsData as $key=>$value){//Add vote Counts
					$sql = "UPDATE `items` SET `itemVotes`='" . ($value + 1) . "' WHERE `itemName`='" . $key . "'";
					$query = mysqli_query($connect, $sql);
				}*/
			}
			
			function showCatagory($catagory){
				global $connect, $stateConnect, $SESSION;
				
				$voteclass = "";
				
				$items;
				$itemCounter = 0;
				
				$userVotesText = "";
				$userVotes[0] = "No Data";
				
				unset($statement);
				$sql = "SELECT `userVotes` FROM `users` WHERE `userEmail`=?";
				
				$statement = $stateConnect->prepare($sql);
				
				$statement->bind_param('s', $SESSION['email']);
				
				$statement->execute();
				
				$statement->bind_result($userVotesText);
				
				if($statement->fetch()){
					$userVotes = explode(',', $userVotesText);
				}
				
				$sql = "SELECT * FROM `items` WHERE `itemCatagory`='" . $catagory . "'";
				$query = mysqli_query($connect, $sql);
				if ($query -> num_rows > 0) {//Gets all items for catagory
					while ($row = mysqli_fetch_array($query)) {
						$items[$itemCounter]['name'] = $row['itemName'];
						$items[$itemCounter]['desc'] = $row['itemDesc'];
						$items[$itemCounter]['link'] = $row['itemLink'];
						$items[$itemCounter]['catagory'] = $row['itemCatagory'];
						$items[$itemCounter]['votes'] = $row['itemVotes'];
						
						//Display Item
						if(is_array($userVotes) === true){
							if(in_array($items[$itemCounter]['name'], $userVotes) === true){
								$voteclass = "voted";
							}	
						}
						
						echo "
						<div class='option' data-link='" . $items[$itemCounter]['link'] . "'>
							<div class='content'>
								" . $items[$itemCounter]['desc'] . "
							</div>
							<div class='title'>
								" . $items[$itemCounter]['name'] . "
							</div>
							<div class='votes'>
								<span class='glyphicon glyphicon-user'></span> " . $items[$itemCounter]['votes'] . "
							</div>
							
						<div class='voteBox " . $voteclass . "' data-item='" . $items[$itemCounter]['name'] . "'>
							<span class='glyphicon glyphicon-star'></span> Vote
						</div>
							
						</div>
						";
						
						$itemCounter = $itemCounter + 1;
					}
				}
				
				/*
				 <div class='option' data-link="http://www.imdb.com/title/tt0993846/">
					<div class='content'>
						Based on the true story of Jordan Belfort, from his rise to a wealthy stockbroker living the high life to his fall involving crime, corruption and the federal government.
					</div>
					<div class='title'>
						The Wolf of Wall Street
					</div>
					<div class='votes'>
						<span class="glyphicon glyphicon-user"></span> 147
					</div>
				</div>
				<div class='voteBox' data-item="1">
					<span class='glyphicon glyphicon-star'></span> Vote
				</div>
				 */
			}
		session_write_close();
		echo "\$_GET Vars: <br>";
		foreach($_GET as $key=>$value){
			echo $key . " => " . $value;
		}
		
		echo "<br>\$SESSION Vars: <br>";
		foreach($SESSION as $key=>$value){
			echo $key . " => " . $value;
		}
		echo "<br>" . session_id();
		?>