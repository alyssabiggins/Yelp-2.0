<?php
	include('../include/dbconn.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<title>Yelp</title>
	<link rel = "stylesheet" href = "yelp.css"/>


  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootswatch/3.3.4/superhero/bootstrap.min.css">
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>

</head>

<body>
<?php
	displaySearchResults();
?>
</body>

</html>

<?php
// Gets the average amounts for the given field (either the rating or the price)
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



// Displays the table of restaurants
function displaySearchResults() {

  $pricemax = isset($_GET['price']) ? $_GET['price'] : 5;
  $ratingmin = isset($_GET['rating']) ? $_GET['rating'] : 0;

  $location = isset($_GET['location']) ? $_GET['location'] : NULL;
  $coords = getCoords($location);

  $category = getCategory();
  //print_r($category);
  $avgprices = getAvgs("price");
  $avgrating = getAvgs("rating");

  $dbc = connectToDB("bigginsa");
  $pred = "";
  if (isset( $_GET['type']) && $_GET['type'] !== '0'){
  	$type = $_GET['type'];
  	$pred = "r_id in (select restaurant_id from Categories where Category = '$type')";
  } else {
  	$pred = "1";
  }
  $query = "select * from restaurants where " . $pred ;
  $result = performQuery($dbc, $query);


  ?>

  <div id="sidebar-wrapper"><?php displayWelcomePage(); ?></div>
  <div id= "page-content-wrapper">
  <table class = "container-fluid">
  	<tr>
  		<th><h3>Matching Restaurant(s)</h3></th>
   	</tr>
  <?php
  while ( @extract( mysqli_fetch_array($result, MYSQLI_ASSOC) ) ) {
	$valid = true;

	//$rCoords = getCoords($address);
	//$distance = haversineGreatCircleDistance($coords["lat"],$coords["long"],
    								//$rCoords["lat"],$rCoords["long"]);


  $directions = getDirections($location,$address);
  $distance = $directions->route->leg->distance->text;

    if ($distance>32000) {
      $valid = false;
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
              <a href = 'http://cscilab.bc.edu/~bigginsa/yelp/project/restpage.php?rid=$R_ID'> Click Here To Find Out More About $name</a>
            	<br><br></td>
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
function displayWelcomePage(){
?>
<fieldset class = "sidebar-nav">
      <legend class="side-legend">Welcome to Yelp 2.0: Boston Edition </legend>
      <form method = "get" action = "query.php">
        <label>Search for a restaurant by maximum price: </label>
        <select name = 'price' >
        		<option value = '5'> --Select One-- </option>
                <option value = '1'>$</option>
                <option value = '2'>$$</option>
                <option value = '3'>$$$</option>
                <option value = '4'>$$$$</option>
        </select><br><br>
        <label>Search for a restaurant by minimum rating: </label>
        <select name = 'rating' >
        		<option value = '0'> --Select One--</option>
                <option value = '1'>*</option>
                <option value = '2'>**</option>
                <option value = '3'>***</option>
                <option value = '4'>****</option>
                <option value = '5'>*****</option>
        </select><br><br>
        <label>Search for a restaurant by type of food: </label>
        <select name = 'type' >
        		<option value = '0'>--Select One-- </option>
                <option value = 'Italian'>Italian</option>
                <option value = 'Mexican'>Mexican</option>
                <option value = 'American'>American</option>
                <option value = 'Indian'>Indian</option>
                <option value = 'Mediterranean'>Mediterranean</option>
                <option value = 'Chinese'>Chinese</option>
                <option value = 'Korean'>Korean</option>
                <option value = 'Thai'>Thai</option>
                <option value = 'Japanese'>Japenese</option>
                <option value = 'Puerto Rican'>Puerto Rican</option>
                <option value = 'Dessert'>Dessert</option>
                <option value = 'Kosher'>Kosher</option>
                <option value = 'Soul Food'>Soul Food</option>
                <option value = 'Wings'>Wings</option>
                <option value = 'Pizza'>Pizza</option>
                <option value = 'Breakfast/Brunch'>Breakfast/Brunch</option>
                <option value = 'BBQ'>BBQ</option>
        </select><br><br>
        <label>Search for a restaurant by location: </label>
        <input type = 'text' name = 'location'>
        <br><br>
        <input type = "submit" name = "go">
      </form>
    </fieldset>
<?php
}



















