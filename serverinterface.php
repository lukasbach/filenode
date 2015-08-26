<?php
session_start();

$config = json_decode(file_get_contents("configuration.json"), true);

if(isset($_GET["path"]) AND isset($_GET["getDirectoryContent"]) AND $_GET["sortfor"] AND $_GET["sortdirection"]) {
  $path = $config["base_path"] . "/" . $_GET["path"];
  $sortfor = $_GET["sortfor"];
  $sortdirection = $_GET["sortdirection"];

  // example/path/ => example/path
  $pathChars = strlen($path); 
  $pathLastChar = substr($path, ($pathChars - 1), 1);
  if($pathLastChar == "/") {
    $path=substr($path, 0, -1);
  }

  // read config, if path is allowed
  // blacklist
  foreach($config["viewfiles_blacklist"] AS $forbiddenPath) {
    if(pathInPath($path, $config["base_path"] . "/" . $forbiddenPath) AND $forbiddenpath != "") {
      echo '{"error":"forbiddenpath"}';
      exit;
    }
  }
  // whitelist
  /*foreach($config["viewfiles_whitelist"] AS $allowedPath) {
    if(!pathInPath($path, $config["base_path"] . "/" . $allowedPath) AND $allowedPath != "") {
      echo '{"error":"forbiddenpath"}';
      exit;
    }
  }*/

  // Scan Path
  $scan = scandir($path);

  $folderlistFiles = array();
  $folderlistFolders = array();
  foreach($scan AS $item) {
      if($item == "." OR $item == ".." OR $item == "")
          continue;
      
      $thisItem = array(
          "name" => $item,
          "isdir" => is_dir($path ."/". $item),
          "filesize" => !is_dir($path ."/". $item) ? filesize($path ."/". $item) : false,
          "lastmodtimest" => !is_dir($path ."/". $item) ? filemtime($path ."/". $item) : false,
          "lastmod" => !is_dir($path ."/". $item) ? date ("F d Y H:i:s", filemtime($path ."/". $item)) : false
      );
      
      if(is_dir($path ."/". $item))
          $folderlistFolders[] = $thisItem;
      else
          $folderlistFiles[] = $thisItem;
  }

  // sort
  $folderlistFolders = sortDirList($folderlistFolders, "name", $sortdirection);
  $folderlistFiles = sortDirList($folderlistFiles, $sortfor, $sortdirection);

  // Merge the arrays into final array and output it
  echo json_encode(array_merge($folderlistFolders, $folderlistFiles));
  exit;

} elseif(isset($_GET["changesettings"]) AND isset($_POST["settings"])) {
  $oldsettings = json_decode($_POST["settings"]);

  if(!$_SESSION["loggedIn"]) {
    echo "notloggedin";
    exit;
  }

  $settings = [
  "base_path" => $oldsettings->base_path,
  "title" => $oldsettings->title,

  "color_scheme" => $oldsettings->color_scheme,

  "imagepreview" => $oldsettings->imagepreview ? "true" : "false",
  "imagepreview_prerender_thumbnails" => $oldsettings->imagepreview_prerender_thumbnails ? "true" : "false",

  /*"viewfiles_blacklist" => ["FreezeImage","BootStrapRedesign/bootstrap-3.3.1/docs"],

  "users" => [
      [
        "name" => "admin",
        "password" => "21232f297a57a5a743894a0e4a801fc3",
        "type" => "admin"
      ]
    ]*/
  ];

  // Add user login
  if(isset($oldsettings->users)) {
    $settings["users"][0] = [
      "name" => $oldsettings->users[0]->name,
      "password" => md5($oldsettings->users[0]->password),
      "type" => "admin"
      ];
  } else {
    $settings["users"][0] = [
      "name" => $config["users"][0]["name"],
      "password" => $config["users"][0]["password"],
      "type" => "admin"
      ];
  }

  // Add blacklist
  $settings["viewfiles_blacklist"] = [];
  foreach($oldsettings->viewfiles_blacklist AS $blacklistelement) {
    if($blacklistelement != "") {
      $settings["viewfiles_blacklist"][] = $blacklistelement;
    }
  }

  file_put_contents("configuration.json", json_encode($settings));
  echo "true";

} elseif(isset($_GET["login"]) AND isset($_POST["user"]) AND isset($_POST["psw"])) {
  $user = $_POST["user"];
  $psw = $_POST["psw"];

  foreach($config["users"] AS $userInList) {
    if($userInList["name"] == $user AND $userInList["password"] == md5($psw)) {
      echo 'true';
      $_SESSION["loggedIn"] = true;
      $_SESSION["username"] = $user;
      exit;
    }
  }

  echo 'false';
  exit;
} elseif(isset($_GET["logout"])) {
  $_SESSION["loggedIn"] = false;
  $_SESSION["username"] = false;
} elseif(isset($_GET["imgthumbnail"]) AND isset($_GET["path"])) {
  echo thumbnail($_GET["path"]);
  exit;
} elseif(isset($_GET["readtextfile"]) AND isset($_GET["path"])) {
  echo file_get_contents($_GET["path"]);
}


function pathInPath($path1, $path2) { //path2 in path1?
  $path1Pieces = explode("/", $path1);
  $path2Pieces = explode("/", $path2);

  if(count($path1Pieces) < count($path2Pieces)) {
    return false;
  }

  /*for($i = 0; $i <= count($path2); $i++) {
    if($path1Pieces[$i] != $path2Pieces[$i]) {
      return false;
    }
  }

  return true;*/

  for($i = 0; $i < count($path2Pieces); $i++) {
    if($path1Pieces[$i] != $path2Pieces[$i]) {
      return false;
    }
  }

  return true;
}

function sortDirList($array, $sortfor, $sortdirection) {
  usort($array, function($a, $b) use ($sortfor, $sortdirection) {
    if($sortfor == "name") {
      if($sortdirection == "desc") {
      return strcmp($a['name'], $b['name']);
      } else {
      return strcmp($b['name'], $a['name']);
      }
    } else {
      if($sortdirection == "desc") {
        return $b[$sortfor] - $a[$sortfor];
      } else {
        return $a[$sortfor] - $b[$sortfor];
      }
    }
  });
  return $array;
}


// http://www.php-einfach.de/codeschnipsel_932.php
function thumbnail($imgfile, $speicherordner="thumbnails/", $filenameOnly=true) 
   { 
   //Max. Größe des Thumbnail (Höhe und Breite) 
   $thumbsize = 600; 

   //Dateiname erzeugen 
   $filename = basename($imgfile); 

   //Fügt den Pfad zur Datei dem Dateinamen hinzu 
   //Aus ordner/bilder/bild1.jpg wird dann ordner_bilder_bild1.jpg 
   if(!$filenameOnly) 
      { 
      $replace = array("/","\\","."); 
      $filename = str_replace($replace,"_",dirname($imgfile))."_".$filename; 
      } 

   //Schreibarbeit sparen 
   $ordner = $speicherordner; 

   //Speicherordner vorhanden 
   if(!is_dir($ordner)) 
      return false; 

   //Wenn Datei schon vorhanden, kein Thumbnail erstellen 
   if(file_exists($ordner.$filename)) 
      return $ordner.$filename; 

   //Ausgansdatei vorhanden? Wenn nicht, false zurückgeben 
   if(!file_exists($imgfile)) 
      return false; 



   //Infos über das Bild 
   $endung = strrchr($imgfile,"."); 

   list($width, $height) = getimagesize($imgfile); 
   $imgratio=$width/$height; 

   //Ist das Bild höher als breit? 
   if($imgratio>1) 
      { 
      $newwidth = $thumbsize; 
      $newheight = $thumbsize/$imgratio; 
      } 
   else 
      { 
      $newheight = $thumbsize; 
      $newwidth = $thumbsize*$imgratio; 
      } 

   //Bild erstellen 
   //Achtung: imagecreatetruecolor funktioniert nur bei bestimmten GD Versionen 
   //Falls ein Fehler auftritt, imagecreate nutzen 
   if(function_exists("imagecreatetruecolor")) 
     $thumb = imagecreatetruecolor($newwidth,$newheight);  
   else 
      $thumb = imagecreate ($newwidth,$newheight); 

   if($endung == ".jpg") 
      { 
      imageJPEG($thumb,$ordner."temp.jpg"); 
      $thumb = imagecreatefromjpeg($ordner."temp.jpg"); 

      $source = imagecreatefromjpeg($imgfile); 
      } 
   else if($endung == ".gif") 
      { 
      imageGIF($thumb,$ordner."temp.gif"); 
      $thumb = imagecreatefromgif($ordner."temp.gif"); 

      $source = imagecreatefromgif($imgfile); 
      } 

   imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height); 

   //Bild speichern 
   if($endung == ".png") 
      imagepng($thumb,$ordner.$filename); 
   else if($endung == ".gif") 
      imagegif($thumb,$ordner.$filename); 
   else 
      imagejpeg($thumb,$ordner.$filename,100); 


   //Speicherplatz wieder freigeben 
   ImageDestroy($thumb); 
   ImageDestroy($source); 


   //Pfad zu dem Bild zurückgeben 
   return $ordner.$filename; 
} 


?>