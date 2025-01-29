<?php
    $unitname=$_GET['unit'];
    $requestfrom=$_GET['requestfrom'];
    if($requestfrom=="optimistic"){
?>
<!DOCTYPE html>
<html>
   <head>
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <title>Resource Reader</title>
      <!-- Flipbook StyleSheet -->
      <link href="css/dflip.css" rel="stylesheet" type="text/css">
      <!-- Icons Stylesheet -->
      <link href="css/themify-icons.css" rel="stylesheet" type="text/css">
   </head>
   <body>
     <div id="flipbookContainer"></div>

      <!-- jQuery 1.9.1 or above -->
      <script src="js/libs/jquery.min.js" type="text/javascript"></script>
      <!-- Flipbook main Js file -->
      <script src="js/dflip.min.js" type="text/javascript"></script>
      <script>
     //best to start when the document is loaded
    jQuery(document).ready(function () {

    //uses source from online(make sure the file has CORS access enabled if used in cross domain)
    var pdf = '<?php echo $unitname; ?>';

    var options = {height: 900, duration: 800,  enableDownload: false, backgroundColor: "#32b474"};

    var flipBook = $("#flipbookContainer").flipBook(pdf, options);

    });
    </script>

   </body>
</html>
<?php }else{?>
<!DOCTYPE html>
<html>
<head>
  <title>Unauthorize Acess</title>
</head>
<body>
    <h1>Not Found</h1>
</body>
</html>
<?php }?>