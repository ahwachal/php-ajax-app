<?php
/*
  Name:         Andrew Wachal
  Class:        CS 316
  Professor:    Linton
  Date:         12/8/19
  Assignment:   Project 4
  Directions:   run with php P4.php
*/
echo "<!DOCTYPE html>";
echo "<html>";
$jsonURL = "https://www.cs.uky.edu/~paul/public/P4_Sources.json";
$jsonSourceString = file_get_contents($jsonURL);        //get contents from URL

//if file_get_contents failed
if ($jsonSourceString == false){
  echo "<p>Error: failed to read content from $jsonURL</p><br>";
  endHTML();
  exit(1);
}

$sources = json_decode($jsonSourceString);              //decode the json

//if json_decode failed
if (json_last_error() != JSON_ERROR_NONE){
  echo "<p>Error while decoding $jsonURL</p><br>";
  endHTML();
  exit(1);
}
$title = $sources->title;

//html title and header
echo "
<head>
<title>$title</title>
</head>
<body>
<h1>$title</h1>";

$databases = $sources->sources;           //array of all the database objects

//if sourcedata and fielddata are set, try to display report
if(isset($_GET['sourcedata']) && isset($_GET['fielddata'])){
  $checkName = $_GET['sourcedata'];
  $checkField = $_GET['fielddata'];
  $nameFound = false;
  $fieldFound = false;
  $database;

  //make sure sourcedata exists
  foreach($databases as $databaseString){
    if($databaseString->name == $checkName){
      $nameFound = true;
      $database = $databaseString;
    }
  }
  if($nameFound == false){
    echo "<p>Error: sourcedata not found by the name $checkName </p><br>";
    endHTML();
    exit(1);
  }
  $searchfield;

  //make sure fielddata exists
  foreach($databases as $databaseString){
    $databaseFields = $databaseString->searchfields;
    foreach($databaseFields as $field){
      if($field == $checkField){
        $fieldFound = true;
        $searchfield = $field;
      }
    }
  }
  if($fieldFound == false){
    echo "<p>Error: fielddata not found by the name $checkField </p><br>";
    endHTML();
    exit(1);
  }

  //if they both exist in the array, display the report
  reportData($database, $searchfield);
  endHTML();
}

//if both sourcedata and fielddata are not set, display the form
else{
  displayForm($databases);
  endHTML();
}
?>



<?php

//display the form with select inputs for sourcedata, fielddata, and findorsort
//display a text input for whattofind
function displayForm($databases){
  echo "<form action='P4.php' method='get'>
        <h3>Source Data:</h3>
        <select name='sourcedata'>";

    //dynamically fill the options for sourcedata select input
    foreach($databases as $databaseString){
        $databaseName = $databaseString->name;
        echo "<option>$databaseName</option>\n";
    }
    echo "</select>
      <br><h3>Field Data:</h3>
          <select name='fielddata'>";

    //dynamically fill the options for fielddata select input
    foreach($databases as $databaseString){
      $databaseFields = $databaseString->searchfields;
      foreach($databaseFields as $field){
        echo "<option>$field</option>\n";
      }
    }

    //findorsort select input and whattofind text input and submit button
    echo "</select>
      <br><h3>Find or Sort:</h3>
          <select name='findorsort'>
            <option value='find'>find</option>
            <option value='sort'>sort</option>
          </select><br>
        <h3>What to Find</h3>
          <input name='whattofind' type='text'><br>
          <br><input type='submit'>
     </form>";
}

//open URL from sourcedata, report the values for the keys from groupfields
function reportData($database, $searchfield){
  $databaseURL = $database->url;

  //open URL and make sure there was no error
  $urlFile = file_get_contents($databaseURL);
  if ($urlFile == false){
    echo "<p> Error: failed to read content from $databaseURL </p><br>";
    endHTML();
    exit(1);
  }

  //decode json and make sure there was no error
  $fileDecode = json_decode($urlFile);
  if (json_last_error() != JSON_ERROR_NONE){
    echo "<p>Error while decoding $databaseURL</p><br>";
    endHTML();
    exit(1);
  }
  $groupfield1 = $database->groupfields[0];         //first groupfields key entry
  $groupfield2 = $database->groupfields[1];         //second groupfields object

  //print values corresponding to first key entry
  echo "<h4>$groupfield1: </h4><p>";
  foreach($fileDecode->$groupfield1 as $comments){
    checkisset($comments, "comments");
    echo "$comments<br>";
  }

  //print sub-objects for the second groupfields object
  echo "</p><h4>$groupfield2: </h4><p>";
  foreach ($fileDecode->$groupfield2 as $objects) {
    checkisset($objects, "objects");
    foreach($objects as $key => $info){
      checkisset($key, "key");
      checkisset($info, "info");
      echo "<b>$key</b>: $info<br>";
    }
    echo "<br>";
  }
  echo "</p>";
}

//check if an object has been set
function checkisset($object, $name){
  if (isset($object) == false){
    echo "<p>Error: Missing an expected field in $name</p>";
  }
}

//print the ending tags to the html
function endHTML(){
  echo "</body>";
  echo "</html>";
}
?>
