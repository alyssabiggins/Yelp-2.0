<?php
include('../include/dbconn.php');

?>

<!DOCTYPE html>
<html lang="en">
<head>
     <meta charset="utf-8" />
     <title>Yelp 2.0</title>
     <link rel = "stylesheet" href = "yelp.css"/>
     <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootswatch/3.3.4/superhero/bootstrap.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
	<script type="text/javascript">
			function myYelpValidate() {
				var rating   = myYelpValidatePulldown("rating");
				var price    = myYelpValidatePulldown("price");

				if ( price && rating ) {
					return true;
				}
				return false;
			}
			function myYelpValidatePulldown(name) {
				var select = document.getElementById(name);
				var val = select.options[select.selectedIndex].value;
				var report = document.getElementById(name+"error");
				if ( val=="none" ) {
					report.innerHTML = "required field";
					return false;
				}
				report.innerHTML = "";
				return true;
			}
	</script>
</head>
<body>
<?php
	if ( isset($_POST['go']) ) {
		enterReview();
	}

	displayRestaurant();
?>
</body>
</html>

<?php

function enterReview() {
	$review = $_POST['review'];
	$rating = $_POST['rating'];
	$price  = $_POST['price'];
	$query = "";
	if ( $review === "" ) {
		$query = "insert into ratings (rest_id, rating, price)
				  values($_GET[rid], '$rating', '$price');";
	} else {
		$query = "insert into ratings (rest_id, rating, price, comment)
		          values($_GET[rid], '$rating', '$price', '$review');";
	}

	$dbc = connectToDB('bigginsa');
	performQuery($dbc, $query);
	mysqli_close($dbc);
	echo "Your rating has been successfully enered!";
}

function getAll($rid){

	$dbc = connectToDB('bigginsa');
	$query = "select * from ratings where rest_id = $rid";

	$result = performQuery($dbc, $query);
	/*$row = $result->fetch_assoc();
  echo "$row";*/
	$arrayComment = array();
  $arrayPrice = array();
  $arrayRating = array();

  $count = 0;
  while ( @extract( mysqli_fetch_array($result, MYSQLI_ASSOC) ) ) {

    if($comment === NULL){
      $arrayComment[$count] = "";
    } else {
      $arrayComment[$count] = "$comment";
    }
    $arrayRating[$count] = $rating . "/5";

    switch($price){
      case "1":
        $arrayPrice[$count] = "Very Cheap";
        break;
      case "2":
        $arrayPrice[$count] = "Cheap";
        break;
      case "3":
        $arrayPrice[$count] = "Average";
        break;
      case "4":
        $arrayPrice[$count] = "Expensive";
        break;
       case "5":
        $arrayPrice[$count] = "Very Expensive";
        break;
    }
    $count ++;
  }

  $array = array();
  $array["comment"] = $arrayComment;
  $array["price"] = $arrayPrice;
  $array["rating"] = $arrayRating;

  disconnectFromDB($dbc, $result);
  return $array;


}




function getCategory(){
  $dbc = connectToDB("bigginsa");
  $query = "select * from Categories;";

  $result = performQuery($dbc, $query);
  $array = array();


  while ( @extract( mysqli_fetch_array($result, MYSQLI_ASSOC) ) ) {

    if(array_key_exists($Restaurant_ID, $array)){
      $array[$Restaurant_ID] = $array[$Restaurant_ID] . ", $Category";
    } else {
      $array[$Restaurant_ID] = "$Category";
    }

  }
  disconnectFromDB($dbc, $query);
  return $array;

}

function getAvgs( $field ) {
  $dbc = connectToDB("bigginsa");
  $query = "select rest_id,$field from ratings order by rest_id";
  $result = performQuery($dbc, $query);
  $avgs = array();
  $curr = -1;
  $count = 0;
  $rate;
  while ( @extract( mysqli_fetch_array($result, MYSQLI_ASSOC) ) ) {
    if ( $field === "rating" ) {
      $rate = intval( $rating );
    } else {
      $rate = intval( $price );
    }
    if ( $curr === $rest_id ) {
      $avgs[$curr] = $avgs[$curr]*$count;
      $count++;
      $avgs[$curr] = ( $avgs[$curr] + $rate ) / $count;
    } else {
      $count = 1;
      $curr = $rest_id;
      $avgs[$curr] = $rate;
    }
  }
  disconnectFromDB($dbc, $result);
  return $avgs;
}

function haversineGreatCircleDistance(
  $latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000)
{
  // convert from degrees to radians
  $latFrom = deg2rad($latitudeFrom);
  $lonFrom = deg2rad($longitudeFrom);
  $latTo = deg2rad($latitudeTo);
  $lonTo = deg2rad($longitudeTo);

  $latDelta = $latTo - $latFrom;
  $lonDelta = $lonTo - $lonFrom;

  $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
    cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
  return $angle * $earthRadius;
}

function getCoords($loc) {
  $key = "key=AIzaSyA81dAYsREb_3wFTKxDdmoXqJdcSHWQTxc";
  $geocodeURL = "https://maps.googleapis.com/maps/api/geocode/xml?";
  $address = "address=" . urlencode($loc);

  $geocoderequest = "$geocodeURL$address" . "&" . $key;
  $xml = new SimpleXMLElement( file_get_contents( $geocoderequest ) );
  if ($xml->status != 'OK'){
	die("No good.");
	//header( "Location: ///address.php?status=$xml->error_message");
   }
  $location = getLocation($xml);
  $latitude = (float)$location["latitude"];
  $longitude = (float)$location["longitude"];
  return array("lat" => $latitude, "long" => $longitude);

}



// Displays the table of restaurants
function displaySearchResults() {
  $pricemin = isset($_GET['price']) ? $_GET['price'] : 0;
  $ratingmin = isset($_GET['rating']) ? $_GET['rating'] : 0;

  $location = isset($_GET['location']) ? $_GET['location'] : NULL;
  $coords = getCoords($location);

  $category = getCategory();
  //print_r($category);
  $avgprices = getAvgs("price");
  $avgrating = getAvgs("rating");

  $dbc = connectToDB("bigginsa");
  $pred = "";
  if (isset( $_GET['type']) && $_GET['type'] !== ''){
  	$type = $_GET['type'];
  	$pred = "r_id in (select restaurant_id from Categories where Category = '$type')";
  } else {
  	$pred = "1";
  }
  $query = "select * from restaurants where " . $pred ;
  $result = performQuery($dbc, $query);


  ?>
  <div class = "container">
  <div id="sidebar"><?php displayWelcomePage() ?></div>
    <table>
  	<tr>
  		<th>Restaurant</th>
   	</tr>
  <?php
  while ( @extract( mysqli_fetch_array($result, MYSQLI_ASSOC) ) ) {
	$valid = true;

	$rCoords = getCoords($address);
	$distance = haversineGreatCircleDistance($coords["lat"],$coords["long"],
    								$rCoords["lat"],$rCoords["long"]);
    if ($distance>32000) {
      //$valid = false;
    }

    if(isset($_GET['price']) && $avgprices[$R_ID]> $_GET['price']){
    	$valid = false;
    }
    if(isset($_GET['rating']) && $avgrating[$R_ID] < $_GET['rating']){
    	$valid = false;
    }
    $avgPrice = intval($avgprices[$R_ID]);
    $avgRating = intval($avgrating[$R_ID]);
    if ( $valid ) {
    	echo "<tr>
        	  	<td>
            	Name: $name <br>
            	Price:  $avgPrice <br>
            	Rating:  $avgRating<br>
            	Type of food served here:  $category[$R_ID] <br>
      			Location:  $address <br>
      			Distance: $distance <br>
      			<a href = 'http://cscilab.bc.edu/~bigginsa/yelp/project/restpage.php'> Click Here To Find Out More About $name</a>
            	</td>
          	</tr>";
    }
  }
  disconnectFromDB($dbc,$result);
  ?>
  </table>
  </div>

  <?php
}

//function to get latitude and longitude of a restaurant
function getLocation($xml){
	$latitude  = $xml->result->geometry->location->lat;
    $longitude = $xml->result->geometry->location->lng;
    $location = array("latitude" => $latitude, "longitude" => $longitude);
    return ($location);
}

function getDirections($origin, $dest){
  $url='https://maps.googleapis.com/maps/api/directions/xml?';
  $url .= 'origin='.urlencode($origin);
  $url .= '&destination='.urlencode($dest);
  $url .= "&key=AIzaSyA81dAYsREb_3wFTKxDdmoXqJdcSHWQTxc";

  $xml = new SimpleXMLElement( file_get_contents( $url ) );

  //echo "URL: $url";

  if ($xml->status != 'OK'){
    die("No good.");
  }

  return $xml;



}

function showDirections($xml){
?>
  <div class = "directions">
  <table >
    <tr>
      <th>Directions</th>
    </tr>
<?php
  $leg = $xml->route->leg;
  foreach ($leg->step as $step) {
    $pos=strpos($step->html_instructions, 'Destination');
    echo "<tr><td>";
    if ($pos == false){
      echo $step->html_instructions. ' for '.$step->distance->text;
      echo '<br />';
    } else {
      echo $step->html_instructions;
      echo '<br />';
    }
    echo "</td></tr>";
  }
?>
</table>
</div>
<?php
}

function optionDirections(){
  ?>
  <div class = "directions">
  <fieldset>
    <legend>Get Directions </legend>
    <form method = "get">
    Enter your location
    <input type = "text" name = "directions"/><br>
    <input type = 'submit' name = "search" value = "Search" />
    <?php
    $get = $_GET['rid'];
    echo "<input type = 'hidden' name = 'rid' value = '$get'/>";
    ?>
    </form>
  </fieldset>
  </div>
  <?php
}


function displayRestaurant(){




  //$location = isset($_GET['location']) ? $_GET['location'] : NULL;
  //$coords = getCoords($location);

  $category = getCategory();

  $dbc = connectToDB("bigginsa");

  $pred = "";

  if (isset( $_GET['rid'])){
    $r_id = isset($_GET['rid']) ? $_GET['rid'] : "";
    $pred = "R_ID = '$r_id'";
  } else {
    $pred = "1";
  }
  $comratpri = getAll($r_id);

  $query = "SELECT * FROM `restaurants` WHERE " . $pred;


  $result = performQuery($dbc, $query);



  @extract( mysqli_fetch_array($result, MYSQLI_ASSOC));
    $directions = 0;
    if (isset($_GET['directions'])){
      $directions = getDirections($_GET['directions'],$address);
      $direct = showDirections($directions);
    } else{
      optionDirections();
    }
    echo "
    <h1>$name</h1>
    Address:  $address <br>
    Phone:  $phone<br>
    Website: <a href =$website> $name Website</a><br>
    Type of food served here:  $category[$R_ID] <br><br>";
  ?>
  <form id='review' method='post' onsubmit="return myYelpValidate()">
  Tell us about your experience here<br>
  Star Rating:<br>
  <select name = 'rating' id='rating'>
      <option value = 'none'>--Select One-- </option>
      <option value = '1'>*</option>
      <option value = '2'>**</option>
      <option value = '3'>***</option>
      <option value = '4'>****</option>
      <option value = '5'>*****</option>
  </select><span id="ratingerror"></span><br><br>
  Price:<br>
  <select name = 'price' id='price'>
      <option value = 'none'>--Select One-- </option>
      <option value = '1'>$</option>
      <option value = '2'>$$</option>
      <option value = '3'>$$$</option>
      <option value = '4'>$$$$</option>
  </select><span id="priceerror"></span><br><br>
  Comments?<br>
  <textarea name="review"></textarea><br>
  <input type = "submit" name = "go" value = "Submit your Review">
  </form><br><br>


  <table>
    <tr>
      <th>User Reviews</th>
    </tr>
  <?php

  @extract($comratpri);
  //print_r($comment);
  foreach($comment as $key => $value ) {
    echo "<tr>
          <td>
          Star Rating: $rating[$key] <br>
          Price Rating: $price[$key] <br>
          Comments: $value <br><br>
          </td>
          </tr>";
  }
  ?>
</table>

  <?php
  disconnectFromDB($dbc,$result);



}


