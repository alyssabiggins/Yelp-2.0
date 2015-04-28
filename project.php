<!DOCTYPE html>
<html lang="en">
<head>
     <meta charset="utf-8" />
     <title>Yelp 2.0</title>
</head>
<body>
</body>
</html>
function displayWelcomePage(){
    <fieldset>
      <legend>Welcome to Yelp 2.0: Boston Edition </legend>
      <form method = "get" action = "query.php">
        <label>Search for a restaurant by price: </label>
        <select name = 'price' size ='4'>
                <option value = '1'>$</option>
                <option value = '2'>$$</option>
                <option value = '3'>$$$</option>
                <option value = '4'>$$$$</option>
        <select><br>
        <label>Search for a restaurant by rating: </label>
        <select name = 'rating' size ='5'>
                <option value = '1'>*</option>
                <option value = '2'>**</option>
                <option value = '3'>***</option>
                <option value = '4'>****</option>
                <option value = '5'>*****</option>
        <select><br>
        <label>Search for a restaurant by type of food: </label>
        <select name = 'type' size = '17'>
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
        </select><br>
        <label>Search for a restaurant by location: </label>
        <input type = 'text' name = 'location'>
        <br>
        <insert type = "submit" name = "go">
    
}
