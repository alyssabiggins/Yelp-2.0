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

  $query = "select rest_id,$field from ratings sort by rest_id";
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
      $count++;
      $avgs[$curr] = ( $avgs[$curr] + $rate ) / $count;
    } else {
      $count = 1;
      $curr = $rest_id;
      $avgs[$curr] = $rate;
    }
  }
  disconnectFromDB($result,$dbc);

  return $avgs;
}


// Displays the table of restaurants
function displaySearchResults() {

  $pricemin = isset($_GET['price']) ? $_GET['price'] : 0;
  $ratingmin = isset($_GET['rating']) ? $_GET['rating'] : 0;

  $dbc = connectToDB("bigginsa");


  $pred = isset($_GET['type']) ? "'$_GET['type']'" : '1';
  $query = "select * from restaurants,categories ";
  $query = $query . "where r_id=restaurant_id and " . $pred;

  $result = performQuery($dbc, $query);

  $avgprices = getAvgs("price");
  $avgrating = getAvgs("rating");



  ?>
  <table>
  	<tr>
  		<th>Restaurant</th>
   	</tr>
  <?php



  while ( @extract( mysqli_fetch_array($result, MYSQLI_ASSOC) ) ) {
  // inside here you can refer to the table's columns as $columname
  // so you can get $r_id and do $avgprices[$r_id] to find that
  // restaurant's corresponding average price rating
  // so inside the while loop is where each row is made and outside is the
  // table and table header tags


   /*

  $key = "key=AIzaSyA81dAYsREb_3wFTKxDdmoXqJdcSHWQTxc";
  $geocoderequest = "$geocodeURL$address" . "&" . $key;
  $geocodeURL = "https://maps.googleapis.com/maps/api/geocode/xml?";

  //$address = get address of restaurant
  $address = "address=" . urlencode($address);

  $xml = new SimpleXMLElement( file_get_contents( $geocoderequest ) );
  if ($xml->status != 'OK'){
	die("No good.);
	header( "Location: ///address.php?status=$xml->error_message");
   }
  $location = getLocation($xml);
  $latitude = (float)$location["latitude"];
  $longitude = (float)$location["longitude"];

   */
	$valid = true;

    if($avgprices[$r_id] < $_GET['price']){
    	$valid = false;
    }
    if($avgrating[$r_id] < $_GET['rating']){
    	$valid = false;
    }
    if ( $valid ) {

    	echo "<tr>
        	  	<td>
            	'Name: GET RESTAURANT NAME' <br>
            	'Price: ' $avgprices[$r_id]<br>
            	'Rating: ' $avgrating[$r_id] <br>
            	'Type of food served here: ' $pred <br>
      			'Location: ' $location
            	</td>
          	</tr>"
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

?>
