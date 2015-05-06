<!DOCTYPE html>
<html lang="en">
<head>
     <meta charset="utf-8" />
     <title>Yelp 2.0</title>
     <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootswatch/3.3.4/superhero/bootstrap.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
</head>
<body>
<?php
displayWelcomePage();
?>
</body>
</html>

<?php
function displayWelcomePage(){
?>
<fieldset>
      <legend>Welcome to Yelp 2.0: Boston Edition </legend>
      <form method = "get" action = "query.php">
        <label>Search for a restaurant by maximum price: </label>
        <select name = 'price' >
        		<option value = '5'> --Select One--</option>
                <option value = '1'>$</option>
                <option value = '2'>$$</option>
                <option value = '3'>$$$</option>
                <option value = '4'>$$$$</option>
        </select><br><br>
        <label>Search for a restaurant by minimum rating: </label>
        <select name = 'rating' >
        		<option value = '0'>--Select One-- </option>
                <option value = '1'>*</option>
                <option value = '2'>**</option>
                <option value = '3'>***</option>
                <option value = '4'>****</option>
                <option value = '5'>*****</option>
        </select><br><br>
        <label>Search for a restaurant by type of food: </label>
        <select name = 'type' >
        		<option value = '0'> --Select One--</option>
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
        <input type = "submit" name = "go" value = "Submit">
    </form>
</fieldset>
<?php
}

