<?php
/*-------------------v1.0 changes for calender-------------------------*/
// ini_set('display_errors', 0);
// ini_set('display_startup_errors', 0);
// error_reporting(0);

/*--////////////////---------v1.0 changes for calender-----------------*/

//var_dump($_POST);

include "config.php";
global $pdo;

/*****************************************
 * Calling function according to the requirement to speed up
 * ****************************************
 */
if(isset($_POST['loginProcess'])) {
    loginlogic();
}elseif(isset($_POST['registerationProcess'])){
    registerationlogic();
}elseif(isset($_POST['submitBtnRegister'])){
    getAResetLink();
}elseif(isset($_POST['resetPassEmail'])){
    resetPasword();
}else if(isset($_POST['page']) && $_POST['page']=='addNewResource'){
    addNewResource();
}if(isset($_POST['page']) && $_POST['page']=='updateResource'){
    updateResouce();
}else if(isset($_POST['page'])&& $_POST['page']=='addNewUser'){
    addNewUser();
}else if(isset($_POST['page'])&& $_POST['page']=='updateUser'){
    updateUser();
}else if(isset($_POST['updateMeetingNew'])){
    updateMeetingNew();
}else if(isset($_POST['date']) && isset($_POST['time']) && isset($_POST['agenda'])  && isset($_POST['counter'])){
    addANewMeeting();
}
/*-------------------v1.0 changes for calender-------------------------*/
else if(isset($_POST['page'])&& $_POST['page']=='getCalenderDetails'){
    getCalenderEvents();
}else if(isset($_POST['page']) && $_POST['page']=='addANewEvent'){
    addNewEvent();
}else if(isset($_POST['page']) && $_POST['page']=='updateEvent'){
    updateEvent();
}
/*--////////////////---------v1.0 changes for calender-----------------*/

/*****************************************
 * LOGIN LOGIC
 * ****************************************
 */

function loginlogic() {
    //include ("config.php");
    global $pdo;
    //var_dump($_POST);
    //exit(0);
    $msg = "";
    $username = trim($_POST['Email']);
    $password = md5(trim($_POST['Password']));
    if($username != "" && $password != "") {
        try {
            $query = "select * from `user` where `email`=:username and `password`=:password";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam('username', $username, PDO::PARAM_STR);
            $stmt->bindValue('password', $password, PDO::PARAM_STR);
            $stmt->execute();
            $row   = $stmt->fetch(PDO::FETCH_ASSOC);
            $count = $stmt->rowCount();
            // echo var_dump($row);
            if($count == 1 && !empty($row)) {
                if($row['verified']==0){
                    $msg = "Your account is not approved by admin. Please contact your admin to get it approved!";
                }else{
                    $name=$row['first name'].' '.$row['last name'];
                    $userId=$row["id"];
                    if (!isset($_SESSION)) {
                        session_start();
                    }
                    if($row["role"]==1){
                        $newquery = "select newuser from `user` where `newuser`=1";
                        $newstmt = $pdo->prepare($newquery);
                        $newstmt->execute();
                        $newrow   = $newstmt->fetchAll(PDO::FETCH_ASSOC);
                        $newcount = $newstmt->rowCount();
                        $_SESSION['newusers']=$newcount;
                    }
                    $_SESSION['currentSession']=1;
                    $_SESSION['role']=$row["role"];
                    $_SESSION['userid']=$row ["id"];
                    $_SESSION['name']=$name;
                    $msg = "Log in Success!";
                    // var_dump($_SESSION);
                    // exit(0);
                    $URL="/portal?page=home";
                    echo "<script type='text/javascript'>document.location.href='{$URL}';</script>";
                    echo '<META HTTP-EQUIV="refresh" content="0;URL=' . $URL . '">';
                }
            } else {
                $msg = "Invalid username and password!";
            }
        } catch (PDOException $e) {
            echo "Error : ".$e->getMessage();
        }
    } else {
        $msg = "Both fields are required!";
    }
    echo $msg;
}
/*****************************************
 * Register Student
 * ****************************************
 */
function registerationlogic(){
    global  $pdo;
    $FirstName= $_POST['FirstName'];
    $LastName = $_POST['LastName'];
    $Email = $_POST['Email'];
    $Password= $_POST['Password'];
    $verified=0;
    $Password=md5($Password);
    $role=$_POST['role'];
    //var_dump($_POST);
    //exit(0);
    if(isset($_POST['Page'])){
        $verified=1;
        $query = "select * from `user` where `email`=:username";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam('username', $Email, PDO::PARAM_STR);
        $stmt->execute();
        $count = $stmt->rowCount();
        if ($count!=1) {
            $query  = "INSERT INTO `user` ( `first name`, `last name`, `email`, `password`, `role`, `verified`) VALUES (:firstname, :lastname, :email, :password, :role, :verified)";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam('firstname', $FirstName, PDO::PARAM_STR);
            $stmt->bindParam('lastname', $LastName, PDO::PARAM_STR);
            $stmt->bindParam('email', $Email, PDO::PARAM_STR);
            $stmt->bindParam('password', $Password, PDO::PARAM_STR);
            $stmt->bindParam('role', $role, PDO::PARAM_STR);
            $stmt->bindParam('verified', $verified, PDO::PARAM_STR);
            $stmt->execute();
            echo 'Success: User Added Successfully!';
        }else{
            echo 'Error: Email Already Registered!';
        }
    }else{
        //echo "I came here";
        $query = "select * from `user` where `email`=:username";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam('username', $Email, PDO::PARAM_STR);
        $stmt->execute();
        $count = $stmt->rowCount();
        
        if ($count==0) {
            // echo "value is:".$role;
            // print_r($_POST);
            // ini_set('display_errors', 1);
            // ini_set('display_startup_errors', 1);
            // error_reporting(E_ALL);
            // exit(0);
            /***Insert Query***/
            $query  = "INSERT INTO `user` ( `first name`, `last name`, `email`, `password`, `role`, `verified`,`newuser`) VALUES (:firstname, :lastname, :email, :password, :role, :verified,1)";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam('firstname', $FirstName, PDO::PARAM_STR);
            $stmt->bindParam('lastname', $LastName, PDO::PARAM_STR);
            $stmt->bindParam('email', $Email, PDO::PARAM_STR);
            $stmt->bindParam('password', $Password, PDO::PARAM_STR);
            $stmt->bindParam('role', $_POST['Role'], PDO::PARAM_STR);
            $stmt->bindParam('verified', $verified, PDO::PARAM_STR);
            $stmt->execute();
            echo 'Success: Contact office to approve your request!';
        }else{
            echo 'Error: Email Already Registered!';
        }
    }

    
}
/*****************************************
 * 'Forgot password'
 * ****************************************
 */
function getAResetLink() {
    global $pdo;
    if( $_POST['email'] != "") {
        try {
            $url="https://www.brightbeginningsfdcc.com.au/portal";
            $query = "select * from `user` where `email`=:username";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam('username', $_POST['email'], PDO::PARAM_STR);
            $stmt->execute();
            $row   = $stmt->fetch(PDO::FETCH_ASSOC);
            $count = $stmt->rowCount();
            //echo $count;
            if($count == 1 && !empty($row)) {
                //check if already a record exists
                $query3 = "select * from `passreset` where `email`=:username";
                $stmt3 = $pdo->prepare($query3);
                $stmt3->bindParam('username', $_POST['email'], PDO::PARAM_STR);
                $stmt3->execute();
                $row3   = $stmt3->fetch(PDO::FETCH_ASSOC);
                $count2 = $stmt3->rowCount();
                $tokenId=$row3['id'];
                if($count2 == 0 ) {
                    //token generation
                    $token = bin2hex(random_bytes(50));
                    //get user and insert the details in token table
                    $query2 = "INSERT INTO `passreset`( `userid`, `email`, `token`)VALUES (:userid,:email, :token )";
                    $stmt2 = $pdo->prepare($query2);
                    $stmt2->bindParam('userid', $row['id'], PDO::PARAM_STR);
                    $stmt2->bindParam('email', $_POST['email'], PDO::PARAM_STR);
                    $stmt2->bindParam('token', $token, PDO::PARAM_STR);
                    $stmt2->execute();
                }else{
                    $query4 = "DELETE FROM `passreset` WHERE `id`=:tid";
                    $stmt4 = $pdo->prepare($query4);
                    $stmt4->bindParam('tid', $tokenId, PDO::PARAM_STR);
                    $stmt4->execute();
                    //token generation
                    $token = bin2hex(random_bytes(50));
                    //get user and insert the details in token table
                    $query2 = "INSERT INTO `passreset`( `userid`, `email`, `token`)VALUES (:userid,:email, :token )";
                    $stmt2 = $pdo->prepare($query2);
                    $stmt2->bindParam('userid', $row['id'], PDO::PARAM_STR);
                    $stmt2->bindParam('email', $_POST['email'], PDO::PARAM_STR);
                    $stmt2->bindParam('token', $token, PDO::PARAM_STR);
                    $stmt2->execute();
                }
                //send an email with the reset link
                $to = $_POST['email'];
                $subject = "[Bright Beginnings Family Day Care]Password Reset";
                $txt  = '<html><body>';
                $txt='<p>Dear user,</p>';
                $txt.='<p>Please click on the following link to reset your password.</p>';
                $txt.='<p>-------------------------------------------------------------</p>';
                $txt.='<p><a href="'.$url.'/reset-password.php?key='.$token.'&email='.$_POST['email'].'&action=reset" target="_blank">
                '.$url.'/reset-password.php?key='.$token.'&email='.$_POST['email'].'&action=reset</a></p>';
                $txt.='<p>-------------------------------------------------------------</p>';
                $txt.='<p>Please be sure to copy the entire link into your browser.
The link will expire after 1 day for security reason.</p>';
                $txt.='<p>If you did not request this forgotten password email, no action
is needed, your password will not be reset. However, you may want to log into
your account and change your security password as someone may have guessed it.</p>';
                $txt.='<p>Thanks,</p>';
                $txt.='<p>Bright Beginnings Family Day Care Team</p>';
                $txt .= '</body></html>';

                $msg = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                      <strong>Success!</strong> If you supplied a correct email address then an email should have been sent to you.
                    </div>';
                echo $msg;
                header( "refresh:2;url=login.php" );
            }else {
                $msg = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                      <strong>Success!</strong> If you supplied a correct email address then an email should have been sent to you.
                    </div>';
                echo $msg;
                header( "refresh:2;url=login.php" );
            }
        }catch (PDOException $e) {
            echo "Error : ".$e->getMessage();
        }
    }
}
/*****************************************
 * 'Confirm Reset Password'
 * ****************************************
 */
function resetPasword(){
    global $pdo;
    $Password=md5($_POST['Password']);
    $query2 = "SELECT * FROM  `user` WHERE email=:email";
    $stmt2 = $pdo->prepare($query2);
    $stmt2->bindParam('email', $_POST['resetPassEmail'], PDO::PARAM_STR);
    $stmt2->execute();
    $row2   = $stmt2->fetch(PDO::FETCH_ASSOC);

    //var_dump($row2);
    if($row2["password"]==$Password){
        echo "New password cannot be same as old one";
    }else {
        //echo "cool update it";
        $sql = "UPDATE user SET password=? WHERE email=?";
        $stmt= $pdo->prepare($sql);
        $result = $stmt->execute([$Password,$_POST['resetPassEmail']]);

        $query = "DELETE FROM passreset
                           WHERE email=?";
        $stmt = $pdo->prepare($query);
        $result = $stmt->execute([$_POST['resetPassEmail']]);
        echo 'Success: Your password have been reset successfully!';
    }
}
/*****************************************
 * 'Notification Bar'
 * ****************************************
 */
function notificationBar(){
?>
<li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                <i class="fas fa-bell fa-fw"></i>
                                <?php if($_SESSION['newusers']!=0){?>
                                <!-- Counter - Alerts -->
                                <span class="badge badge-danger badge-counter"><?php echo  $_SESSION['newusers'];?></span>
                                <?php }  ?>
                            </a>
                            <!-- Dropdown - Alerts -->
                            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="alertsDropdown">
                                <h6 class="dropdown-header">
                                    Alerts Center
                                </h6>
                                <?php if($_SESSION['newusers'] !=0){?>
                                <a class="dropdown-item d-flex align-items-center" href="./?page=pendingUsers">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-success">
                                            <i class="fas fa-user text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                    <?php echo  $_SESSION['newusers'];?> New Users are still pending!
                                    </div>
                                </a>
                                <?php
                                }else{
                                    ?>
                                    <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-success">
                                            <i class="fas fa-check text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                    You're caught up with everything!
                                    </div>
                                </a>

                                <?php
                                }
                                ?>
                            </div>
                    </li>

<?php
}
/*****************************************
 * 'User Option'
 * ****************************************
 */
function userOption(){
    $a=' 
    <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">';
    $b='<img class="img-profile rounded-circle"
                                    src="img/undraw_profile.svg">
                            </a>
                            <!-- Dropdown - User Information -->
                           <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                <!--<a class="dropdown-item" href="#">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Profile
                                </a>
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Settings
                                </a>
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Activity Log
                                </a>
                                -->
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="?page=logout" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Logout
                                </a>
                            </div>
                        </li>';


    return $a.' '.$_SESSION['name'].' '.$b;
}
/*****************************************
 * 'Get all resources'
 * ****************************************
 */
function getAllResources(){
    global $pdo;
    try {
        $query = "select * from `Resources`";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $row   = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error : ".$e->getMessage();
    }
    return $row;
}
/*****************************************
 * Link Generation
 * ****************************************
 */
function linkGeneration($title,$source){
    $var1='<div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                            <a href="'.$source.'" target="_blank">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Link</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">';

    $var2='</div>
                                        </div>
                                        <div class="col-auto">
                                            ';
    $var3='<i class="fas fa-link fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                                </a>
                            </div>
                        </div>
                            ';
    $link=$var1.$title.$var2.$var3;
    return $link;
}
/*****************************************
 * 'File Generation'
 * ****************************************
 */
function fileGeneration($title,$version,$source,$category){
    $var1='<div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                            <a href="?page=viewResource&source=home&res='.$source.'" target="_blank">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Resource</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">';
    $var2=' Version(';

    $var3=') <br>
                                        <h6><b>Category:</b> ';

    $var='</h6></div>
                                        </div>
                                        <div class="col-auto">
                                            ';
    $var4='<i class="fas fa-file fa-2x text-gray-300"></i>
                                            <br>
                                            
                                            ';
    $var5=' </div>
                                    </div>
                                </div>
                            </div>
                            </a>
                        </div>';
    $file=$var1.$title.$var2.$version.$var3.$category.$var.$var4.$var5;
    return $file;
}

/*****************************************
 * 'Add New Resource'
 * ****************************************
 */
function addNewResource(){
    global $pdo;
    $category= $_POST ["category"];
    //var_dump($_POST);
    $role=$_POST['addResourceRole'];
    if(isset($_FILES['resource'])){
        $title=$_POST['resTitle'];
        $version= $_POST['version'];
        $type="file";
        $target_dir = "books/resources/";
        $fileNameFinal = basename($_FILES['resource']["name"]);
        $sourceA= $_FILES['resource']["tmp_name"];
        $date = new DateTime();
        $dest=$target_dir.$date->getTimestamp().'.pdf';
        move_uploaded_file($sourceA, $dest);
        $source= trim($dest,"books/");
    }else{
        $title=$_POST['resTitle'];
        $version= "N/A";
        $type="link";
        $source=$_POST['resource'];
    }
    try {
        $query  = "INSERT INTO `Resources`( `title`, `category`, `version`, `type`, `source`,`role`) VALUES (:title,:category, :resversion, :restype, :source,:role)";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam('title', $title, PDO::PARAM_STR);
        $stmt->bindParam('category', $category, PDO::PARAM_STR);
        $stmt->bindParam('resversion', $version, PDO::PARAM_STR);
        $stmt->bindParam('restype', $type, PDO::PARAM_STR);
        $stmt->bindParam('source', $source, PDO::PARAM_STR);
        $stmt->bindParam('role', $role, PDO::PARAM_STR);
        $stmt->execute();
        $response['message'] = 'Form data submitted successfully!';
        header('Location: /portal/?page=addNewResource&message=success');
        exit;
    }catch (Exception $e) {
        echo $e;
    }
}

/*****************************************
 * 'update or Edit a resource'
 * ****************************************
 */
function updateResouce(){
    global $pdo;
    if($_POST["category"]!="nothing"){
        $category=$_POST["category"];
    }
    $id=$_POST["id"];
    $title=$_POST['resTitle'];
    $version= $_POST['version'];
    $type="file";
    $role= $_POST['addResourceRole'];
    $mystring='resources/';
    if($_POST["fileChanged"]==1){
        unlink("books/".$_POST["keepResource"]);
        $target_dir = "books/resources/";
        $fileNameFinal = basename($_FILES['resource']["name"]);
        $sourceA= $_FILES['resource']["tmp_name"];
        $date = new DateTime();
        $dest=$target_dir.$date->getTimestamp().$fileNameFinal;
        move_uploaded_file($sourceA, $dest);
        $source= trim($dest,"books/");
    }else{
        $source=$_POST["keepResource"];
    }
    include('config.php');
    try {
        if($_POST["category"]!="nothing"){
            $category=$_POST["category"];
            $query =
                    "UPDATE `Resources` SET `title`=:title,`category`=:category,`version`=:resVersion,`type`=:resType,`source`=:resSource,`role`=:assignRole WHERE `rid`=:id";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam('title', $title, PDO::PARAM_STR);
            $stmt->bindParam('category', $category, PDO::PARAM_STR);
            $stmt->bindParam('resVersion', $version, PDO::PARAM_STR);
            $stmt->bindParam('resType', $type, PDO::PARAM_STR);
            $stmt->bindParam('resSource', $source, PDO::PARAM_STR);
            $stmt->bindParam('id', $id, PDO::PARAM_STR);
            $stmt->bindParam('assignRole', $role, PDO::PARAM_STR);
            $stmt->execute();

        }else {
            $query =
                    "UPDATE `Resources` SET `title`=:title,`version`=:resVersion,`type`=:resType,`source`=:resSource,`role`=:assignRole WHERE `rid`=:id";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam('title', $title, PDO::PARAM_STR);
            $stmt->bindParam('resVersion', $version, PDO::PARAM_STR);
            $stmt->bindParam('resType', $type, PDO::PARAM_STR);
            $stmt->bindParam('resSource', $source, PDO::PARAM_STR);
            $stmt->bindParam('assignRole', $role, PDO::PARAM_STR);
            $stmt->bindParam('id', $id, PDO::PARAM_STR);
            $stmt->execute();
        }
        $URL="/portal?page=editResource&status=edited";
        echo "<script type='text/javascript'>document.location.href='{$URL}';</script>";
        echo '<META HTTP-EQUIV="refresh" content="0;URL=' . $URL . '">';
    }catch (Exception $e) {
        echo $e;
    }
}

/*****************************************
 * 'Add New meeting'
 * ****************************************
 */
function addANewMeeting(){

    echo '<style>
label{
    display: block;
    padding: 20px;
    background: #fff;
    color: #999;
    border-bottom: 2px solid #f0f0f0;
}
</style>';
    
    
    global $pdo;
    $errMsg = '';
    $valid = 1;
    // Get the submitted form data
    $name=$_POST['name'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $agenda = $_POST['agenda'];
    $counter=$_POST['counter'];

    $newDate = date("Y-m-d", strtotime($date));
    //echo "Date is: ".$newDate."<br/>";
    if(empty($date)){
        $valid = 0;
        $errMsg .= '<br/>Please enter date.';
    }

    if(empty($time)){
        $valid = 0;
        $errMsg .= '<br/>Please enter time.';
    }

    if(empty($agenda)){
        $valid = 0;
        $errMsg .= '<br/>Please enter time.';
    }


    // Check whether submitted data is not empty
    if($valid == 1){
        $target_dir = "books/resources/";
        $uploadOk = 1;
        $fileArray;
        for ($i=0; $i <$_POST['counter'] ; $i++) {
            if(isset($_FILES["file".$i]))
            {
                $titleArry["$i"]=$_POST["resname".$i];
                $filename="file".$i;
                $fileNameFinal = basename($_FILES["$filename"]["name"]);
                $date = new DateTime();
                $source= $_FILES["$filename"]["tmp_name"];
                $date = new DateTime();
                $dest=$target_dir.$date->getTimestamp().'.pdf';

                move_uploaded_file($source, $dest);
                $fileArray["$i"]= trim($dest,"books/");
            }
        }
        //echo 'no of links are:'.$_POST['linkCounter'] ;
        if ($_POST['linkCounter']>1){
            //var_dump($_POST['link1']);
            for ($j=1; $j <$_POST['linkCounter'] ; $j++) {
                //linkTitle
                $linkTitle["$j"]=$_POST['linkTitle'.$j];
                $linksArr["$j"]=$_POST['link'.$j];
            }
        }
    }

    if (isset($linksArr)){
        $links=serialize($linksArr);
    }
    if (isset($fileArray)) {
        $filesArr = serialize($fileArray);
    }
    if (isset($titleArry)) {
        $titleArr=serialize($titleArry);
    }
    if (isset($linkTitle)) {
        $linkTitle=serialize($linkTitle);
    }



    // Include the database config file
    // echo"<br/>";
    // var_dump($filesArr);
    // echo"<br/>";
    // var_dump($titleArr);
    $type="meeting";

    try {
        //echo '<pre>';
        //echo $type;
        //echo '</pre>';
        //
        //echo '<pre>';
        //echo $newDate;
        //echo '</pre>';
        //
        //echo '<pre>';
        //echo $time;
        //echo '</pre>';
        //
        //echo '<pre>';
        //echo $agenda;
        //echo '</pre>';
        //
        //echo '<pre>';
        //echo $filesArr;
        //echo '</pre>';
        //
        //echo '<pre>';
        //echo $titleArr;
        //echo '</pre>';
        //
        echo '<pre>';
        if (isset($links)){
            echo $links;
        }
        echo '</pre>';
        //var_dump($links);
        if (isset($links) && isset($filesArr)){
            $query  = "INSERT INTO `meetings`(`name`,`type`, `date`, `time`, `agenda`, `files`, `titles`, `serializedLinks`,`linksTitle`) VALUES (:name,:mtype,:dateOf,:timeOf, :agenda, :files, :titles, :links,:linksTitle)";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam('name', $name, PDO::PARAM_STR);
            $stmt->bindParam('mtype', $type, PDO::PARAM_STR);
            $stmt->bindParam('dateOf', $newDate, PDO::PARAM_STR);
            $stmt->bindParam('timeOf', $time, PDO::PARAM_STR);
            $stmt->bindParam('agenda', $agenda, PDO::PARAM_STR);
            $stmt->bindParam('files', $filesArr, PDO::PARAM_STR);
            $stmt->bindParam('titles', $titleArr, PDO::PARAM_STR);
            $stmt->bindParam('links', $links, PDO::PARAM_STR);
            $stmt->bindParam('linksTitle', $linkTitle, PDO::PARAM_STR);
            $stmt->execute();
            $response['message'] = 'Form data submitted successfully!';
        }elseif (isset($links)){
            $query  = "INSERT INTO `meetings`(`name`,`type`, `date`, `time`, `agenda`, `serializedLinks`,`linksTitle`) VALUES (:name,:mtype,:dateOf,:timeOf, :agenda, :links,:linksTitle)";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam('name', $name, PDO::PARAM_STR);
            $stmt->bindParam('mtype', $type, PDO::PARAM_STR);
            $stmt->bindParam('dateOf', $newDate, PDO::PARAM_STR);
            $stmt->bindParam('timeOf', $time, PDO::PARAM_STR);
            $stmt->bindParam('agenda', $agenda, PDO::PARAM_STR);
            $stmt->bindParam('links', $links, PDO::PARAM_STR);
            $stmt->bindParam('linksTitle', $linkTitle, PDO::PARAM_STR);
            $stmt->execute();
            $response['message'] = 'Form data submitted successfully!';
        }elseif (isset($filesArr)){
            $query  = "INSERT INTO `meetings`(`name`,`type`, `date`, `time`, `agenda`, `files`, `titles`) VALUES (:name,:mtype,:dateOf,:timeOf, :agenda, :files, :titles)";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam('name', $name, PDO::PARAM_STR);
            $stmt->bindParam('mtype', $type, PDO::PARAM_STR);
            $stmt->bindParam('dateOf', $newDate, PDO::PARAM_STR);
            $stmt->bindParam('timeOf', $time, PDO::PARAM_STR);
            $stmt->bindParam('agenda', $agenda, PDO::PARAM_STR);
            $stmt->bindParam('files', $filesArr, PDO::PARAM_STR);
            $stmt->bindParam('titles', $titleArr, PDO::PARAM_STR);
            $stmt->execute();
            $response['message'] = 'Form data submitted successfully!';
        }else{
            $query  = "INSERT INTO `meetings`(`name`,`type`, `date`, `time`, `agenda`) VALUES (:name,:mtype,:dateOf,:timeOf, :agenda)";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam('name', $name, PDO::PARAM_STR);
            $stmt->bindParam('mtype', $type, PDO::PARAM_STR);
            $stmt->bindParam('dateOf', $newDate, PDO::PARAM_STR);
            $stmt->bindParam('timeOf', $time, PDO::PARAM_STR);
            $stmt->bindParam('agenda', $agenda, PDO::PARAM_STR);
            $stmt->execute();
            $response['message'] = 'Form data submitted successfully!';
        }

    }catch (Exception $e) {
        echo $e;
    }
    $URL="/portal?page=viewMeetings";
    echo "<script type='text/javascript'>document.location.href='{$URL}';</script>";
    echo '<META HTTP-EQUIV="refresh" content="0;URL=' . $URL . '">';
}

/*****************************************
 * update user
 * ****************************************
 */
function updateUser(){
    global $pdo;
    $id= $_POST['userId'];
    $FirstName= $_POST['FirstName'];
    $LastName = $_POST['LastName'];
    $Email = $_POST['Email'];
    $Password= $_POST['Password'];
    $role=$_POST['Role'];
    $Password=md5($Password);



    if($_POST['passwordSetting']=='keep' || $_POST['passwordSetting']=='nothing'){
        $data = [
                'firstname'=>$FirstName,
                'lastname'=>$LastName,
                'email'=>$Email,
                'role'=>$role,
                'id'=>$id
        ];
        $query  = "UPDATE user SET  `first name`= :firstname, `last name`= :lastname, `email`= :email , `role`= :role Where id=:id ";
        $stmt = $pdo->prepare($query);
        $stmt->execute($data);
    }else{
        $data = [
                'firstname'=>$FirstName,
                'lastname'=>$LastName,
                'email'=>$Email,
                'password'=>$Password,
                'role'=>$role,
                'id'=>$id
        ];
        $query  = "UPDATE user SET  `first name`= :firstname, `last name`= :lastname, `email`= :email, `password`= :password, `role`= :role Where id=:id ";
        $stmt = $pdo->prepare($query);
        $stmt->execute($data);
    }



}

/*****************************************
 * add new user
 * ****************************************
 */
function addNewUser(){
    global $pdo;
    $FirstName= $_POST['FirstName'];
    $LastName = $_POST['LastName'];
    $Email = $_POST['Email'];
    $Password= $_POST['Password'];
    $Password=md5($Password);
    $verified=1;
    $role=$_POST['Role'];
    $query = "select * from `user` where `email`=:username";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam('username', $Email, PDO::PARAM_STR);
    $stmt->execute();
    $count = $stmt->rowCount();
    if ($count!=1) {
        /***Insert Query***/
        $query  = "INSERT INTO `user` ( `first name`, `last name`, `email`, `password`, `role`, `verified`) VALUES (:firstname, :lastname, :email, :password, :role, :verified)";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam('firstname', $FirstName, PDO::PARAM_STR);
        $stmt->bindParam('lastname', $LastName, PDO::PARAM_STR);
        $stmt->bindParam('email', $Email, PDO::PARAM_STR);
        $stmt->bindParam('password', $Password, PDO::PARAM_STR);
        $stmt->bindParam('role', $role, PDO::PARAM_STR);
        $stmt->bindParam('verified', $verified, PDO::PARAM_STR);
        $stmt->execute();
        echo 'Success: User Added Successfully!';
    }else{
        echo 'Error: Email Already Registered!';
    }
}

/*****************************************
 * update meeting new
 * ****************************************
 */
function updateMeetingNew(){

    global $pdo;
    $date = $_POST['date'];
    $time = $_POST['time'];
    $agenda = $_POST['agenda'];
    $counter=$_POST['counter'];
    $name=$_POST['name'];
    $target_dir = "books/resources/";
    try{
        $meetingId=$_POST["meetingId"];
        $query = "SELECT * FROM `meetings` where `id`=:id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam('id', $meetingId, PDO::PARAM_STR);
        $stmt->execute();
        $row   = $stmt->fetch(PDO::FETCH_ASSOC);
        $titles=unserialize($row["titles"]);
        $files=unserialize($row["files"]);
        $links=unserialize($row["links"]);
        $linksTitle=unserialize($row["linksTitle"]);

        $filesCount=count((is_countable($files)?$files:[]));
        $linksCount=count((is_countable($links)?$links:[]));
        $newLinks=[];
    } catch (PDOException $e) {
        echo "Error : ".$e->getMessage();
    }
    $date = $_POST['date'];
    $time = $_POST['time'];
    $agenda = $_POST['agenda'];
    $counter=$_POST['counter'];
    $currentFileLocation=1;
    for ($new=0; $new <= 50; $new++) {

        if(isset($_POST['isItDeleted'.$new]) && $_POST['isItDeleted'.$new]==0){
            if(isset($_POST['isOld'.$new])&& $_POST['isOld'.$new]==1){
                // OLD FILE: File Changed and need to be added.
                if(isset($_FILES['file'.$new]) && $_POST['fileChanged'.$new]==1){

                    //echo 'OLD FILE: File Changed and need to be added. <br>';
                    $titleArry[$currentFileLocation]=$_POST["resname".$new];
                    $filename="file".$new;
                    $fileNameFinal = basename($_FILES["$filename"]["name"]);
                    $date = new DateTime();
                    $source= $_FILES["$filename"]["tmp_name"];
                    $date = new DateTime();
                    $dest=$target_dir.$date->getTimestamp().'.pdf';
                    move_uploaded_file($source, $dest);
                    $fileArray[ $currentFileLocation]= trim($dest,"books/");
                    $currentFileLocation++;
                }
                //OLD FILE: Keep old.
                elseif(!isset($_FILES['file'.$new]) && $_POST['fileChanged'.$new]==1){
                    if(!empty($oldFilesLinks[$new])){
                        $fileArray[$currentFileLocation]=$oldFilesLinks[$new];
                        $titleArry[$currentFileLocation]=$_POST["resname".$new];
                        $currentFileLocation++;
                    }
                }elseif($_POST['fileChanged'.$new]==0){
                    if(!empty($_POST['keepResource'.$new])){
                        //echo $_POST['keepResource'.$new];
                        $fileArray[$currentFileLocation]=$_POST['keepResource'.$new];
                        $titleArry[$currentFileLocation]=$_POST["resname".$new];
                        $currentFileLocation++;
                    }
                }
            }elseif(isset($_FILES['file'.$new]) && !isset($_POST['isOld'.$new]) ){
                if(isset($_FILES['file'.$new])){
                    //echo $new;
                    //echo 'New FILE: File Changed and need to be added. <br>';
                    $titleArry[ $currentFileLocation]=$_POST["resname".$new];
                    $filename="file".$new;
                    $fileNameFinal = basename($_FILES["$filename"]["name"]);
                    $date = new DateTime();
                    $source= $_FILES["$filename"]["tmp_name"];
                    $date = new DateTime();
                    $dest=$target_dir.$date->getTimestamp().$fileNameFinal;
                    move_uploaded_file($source, $dest);
                    $fileArray[ $currentFileLocation]= trim($dest,"books/");
                    $currentFileLocation++;
                }
            }
        }
    }

    for ($j=0; $j <$_POST['linkCounter'] ; $j++) {
        if(isset($_POST['linkTitle'.$j]))
        {
            $linksTitle["$j"]=$_POST['linkTitle'.$j];
            $links["$j"]=$_POST['link'.$j];
            $newLinks[$linksTitle["$j"]]=$_POST['link'.$j];
        }
    }

    $filesArr=serialize($fileArray);
    $titleArr=serialize($titleArry);
    $links=serialize($links);
    $linksTitle=serialize($linksTitle);
    $letsTry=serialize($newLinks);
    $postedDate=$_POST['date'];
    $newDate = date("Y-m-d", strtotime($postedDate));
    $type="meeting";
    try {
        $query  = "UPDATE `meetings` SET `name`=:name2, `type`=:mtype,`date`=:dateOf,`time`=:timeOf,`agenda`=:agenda,`files`=:files,`titles`=:titles,`serializedLinks`=:serializedLinks ,`linksTitle`=:serializedTitles WHERE `id` =:id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam('id', $meetingId, PDO::PARAM_STR);
        $stmt->bindParam('name2', $_POST['name'], PDO::PARAM_STR);
        $stmt->bindParam('mtype',$type, PDO::PARAM_STR);
        $stmt->bindParam('dateOf',$newDate , PDO::PARAM_STR);
        $stmt->bindParam('timeOf',$time, PDO::PARAM_STR);
        $stmt->bindParam('agenda',$agenda, PDO::PARAM_STR);
        $stmt->bindParam('files',$filesArr, PDO::PARAM_STR);
        $stmt->bindParam('titles',$titleArr, PDO::PARAM_STR);
        $stmt->bindParam('serializedLinks', $letsTry, PDO::PARAM_STR);
        $stmt->bindParam('serializedTitles', $linksTitle, PDO::PARAM_STR);
        $stmt->execute();
        $URL="/portal?page=updatingMeeting&status=edited";
        echo "<script type='text/javascript'>document.location.href='{$URL}';</script>";
        echo '<META HTTP-EQUIV="refresh" content="0;URL=' . $URL . '">';
    }catch (Exception $e) {
        echo $e;
    }
}
/*-------------------v1.0 changes for calender-------------------------*/
function getCalenderEvents(){
    global $pdo;
    $query = "SELECT * FROM `meetings`";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $row   = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($row as $value) {
        $newArr[]=array('id'=>$value['id'],'name'=>$value['name'],'description'=>$value['agenda'].'<br><br><a href="?page=viewMeetingDetails&id='.$value['id'].'" target="_blank" class="btn btn-primary">
                                                    <span class="text">View Details</span>
                                                </a>' ,'date'=>$value['date'],'badge'=>'Meeting','type'=>'birthday');
    }
    $query = "SELECT * FROM `events`";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $row   = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($row as $value) {
        $newArr[]=array('id'=>$value['id'],'name'=>$value['name'],'description'=>$value['description'] ,'date'=>$value['date'],'badge'=>$value['badge'],'type'=>$value['type']);
    }
    //echo '<pre>';
    //var_dump($newArr);
    //echo '</pre>';
    echo json_encode($newArr);
}
function addNewEvent(){
    global $pdo;
    $name=$_POST['name'];
    $desc=$_POST['desc'];
    $date=$_POST['date'];
    $category=$_POST['category'];
    $query  = "INSERT INTO `events`(`name`, `description`, `badge`, `date`, `type`) VALUES (:name2, :desc2, :badge, :date2, :category)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam('name2', $name, PDO::PARAM_STR);
    $stmt->bindParam('desc2', $desc, PDO::PARAM_STR);
    $stmt->bindParam('badge', $category, PDO::PARAM_STR);
    $stmt->bindParam('date2', $date, PDO::PARAM_STR);
    $stmt->bindParam('category', $category, PDO::PARAM_STR);
    $stmt->execute();
    echo 'Success: User Added Successfully!';
    //echo json_encode($name.' '.$desc.' '.$date.' '.$category);
}
function updateEvent(){
    global $pdo;
    $id=$_POST['id'];
    $name=$_POST['name'];
    $desc=$_POST['desc'];
    $date=$_POST['date'];
    $category=$_POST['category'];
    echo json_encode($id.' '.$name.' '.$desc.' '.$date.' '.$category);
    //UPDATE `events` SET `id`=[value-1],`name`=[value-2],`description`=[value-3],`badge`=[value-4],`date`=[value-5],`type`=[value-6] WHERE 1
    $query  = "UPDATE `events` SET `name`=:name2,`description`=:desc2,`badge`=:badge,`date`=:date2,`type`=:category Where id=:id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam('name2', $name, PDO::PARAM_STR);
    $stmt->bindParam('desc2', $desc, PDO::PARAM_STR);
    $stmt->bindParam('badge', $category, PDO::PARAM_STR);
    $stmt->bindParam('date2', $date, PDO::PARAM_STR);
    $stmt->bindParam('category', $category, PDO::PARAM_STR);
    $stmt->bindParam('id', $id, PDO::PARAM_STR);
    $stmt->execute();
    $URL="?page=editOrDeleteEvents&status=edited";
    echo "<script type='text/javascript'>document.location.href='{$URL}';</script>";
    echo '<META HTTP-EQUIV="refresh" content="0;URL=' . $URL . '">';

}
/*--////////////////---------v1.0 changes for calender-----------------*/
?>