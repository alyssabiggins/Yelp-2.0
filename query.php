<?php
	include('../include/dbconn.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<title>Yelp</title>

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




















