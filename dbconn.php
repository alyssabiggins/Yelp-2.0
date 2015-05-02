<?php
function connectToDB($dbname){
	$dbc= @mysqli_connect("localhost", "bigginsa", "n7zaXgD9", $dbname) or
					die("Connect failed: ". mysqli_connect_error());
	return $dbc;
}
function disconnectFromDB($dbc, $result){
	//mysqli_free_result($result);
	mysqli_close($dbc);
}

function performQuery($dbc, $query){
	//echo "My query is >$query< <br>";
	$result = mysqli_query($dbc, $query) or die("BAD QUERY" . mysqli_error($dbc));
	return $result;
}
?>
