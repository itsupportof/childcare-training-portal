<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if ($_SESSION['currentSession'] != 1 ) {
    header('Location: login.php', true, 301);
    exit();
}

/*****************************************
 * User Class
 * ****************************************
 */

 class User{
    ///////////////////////////////////////////
    //////////Logout//////////////
    /////////////////////////////////////////
    public function logout(){
        unset($_SESSION['currentSession']);
        unset($_SESSION['role']);
        unset($_SESSION['userid']);
        $URL="?page=home";
        echo "<script type='text/javascript'>document.location.href='{$URL}';</script>";
        echo '<META HTTP-EQUIV="refresh" content="0;URL=' . $URL . '">';

    }
    ///////////////////////////////////////////
    //////////view All Current Users//////////////
    /////////////////////////////////////////
    public function viewAllCurrentUsers(){
        global $pdo;
        try {
            $query = "select * from `user`  where `user`.`verified`=1";
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $row   = $stmt->fetchAll(PDO::FETCH_ASSOC);
            //var_dump($row);
            function getEducatorPassword($pdo, $id){
                //echo $id;
                $query = "select formPassword from formassignments  where formassignments.eduid=:id";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':id', $id, PDO::PARAM_STR);
                $stmt->execute();
                $row   = $stmt->fetch(PDO::FETCH_ASSOC);
                $count2 = $stmt->rowCount();
                if($count2==0){
                    $row["formPassword"]="-";
                }
                //var_dump($row);
                return $row["formPassword"];
            }
        } catch (PDOException $e) {
            echo "Error : ".$e->getMessage();
        }
        if (isset($_GET['status']) && $_GET['status']=="edited"){
            echo '<div class="alert alert-success" role="alert" id="userUpdationStatus" style="display: block;">
                    The user account is updated successfully
                </div>';
        }elseif (isset($_GET['status']) && $_GET['status']=="deleted"){
            echo '<div class="alert alert-danger" role="alert" id="userUpdationStatus" style="display: block;">
                    The user account is deleted successfully
                </div>';
        }
        ?>
        <h1 class="h3 mb-4 text-gray-800" style="text-align: center; padding-top: 30px;">Current Users</h1>
        <!-- Page Heading -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">You can edit,update and delete users through this panel</h6>
                    </div>
                    <div class="card-body border-bottom-success">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                <tr>
                                    <th style="">Id</th>
                                    <th>First Name</th>
                                    <th>Last Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tfoot>
                                <tr>
                                    <th style="">Id</th>
                                    <th>First Name</th>
                                    <th>Last Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Action</th>
                                </tr>
                                </tfoot>
                                <tbody>
                                <?php
                                foreach ($row as $data)
                                {
                                    ?>
                                    <tr>
                                        <td style=""><?php echo $data["id"];?></td>
                                        <td><?php echo $data["first name"];?></td>
                                        <td><?php echo $data["last name"];?></td>
                                        <td><?php echo $data["email"];?></td>
                                        <td><?php if($data["role"]=='1'){
                                                echo 'admin';
                                            }elseif($data["role"]=='2'){
                                                echo 'Educator';
                                            }else{
                                                echo 'Parent';
                                            } ?></td>

                                        <td>

                                            <a href="?page=editUser&user=<?php echo $data["id"];?>" class="btn btn-primary btn-circle btn-md" id="edit<?php echo $data["id"];?>">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <!-- Delete Button in Each Row -->
                                            <a href="javascript:void(0);" onclick="confirmDelete(<?php echo $data['id']; ?>)" class="btn btn-danger btn-circle btn-md">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        
                                        </td>
                                    </tr>
                                <?php }?>
                                </tbody>
                            </table>
                            <script>
    function confirmDelete(userID) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirect to the deletion URL
                window.location.href = `?page=deleteUser&source=allusers&user=${userID}`;
            }
        });
    }

    function deleteRecord() {
        // Your deletion code here (e.g., AJAX request to delete the record)
        console.log("Record deleted");
    }
</script>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <?php
    }

    ///////////////////////////////////////////
    //////////Edit specific User//////////////
    /////////////////////////////////////////
    ///
    public function editUser($userId){
    ?>
        <script type="text/javascript" src="js/jquery-3.6.0.min.js"></script>
        <script type="text/javascript">
            $(document).ready(function() {
                $('#updateform').submit(function(e) {
                    e.preventDefault();
                    $("#errorblock").css("display","none");
                    $("#messageblock").css("display","none");
                    var userId = $('#userId').val();
                    var first_name = $('#FirstName').val();
                    var last_name = $('#LastName').val();
                    var email = $('#Email').val();
                    var password = $('#Password').val();
                    var repeatPassword = $('#RepeatPassword').val();
                    var role=$("#selectrole option:selected").text();
                    var loc="abc";
                    var EmailregEx = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                    var nameRegEx=/^(?=.{1,50}$)[a-z]+(?:['_.\s][a-z]+)*$/i;
                    var errorCount=0;
                    $(".error").remove();

                    var validFirstName = nameRegEx.test(first_name);
                    if (first_name.length < 1) {
                        $('#FirstName').after('<div class="error" style="padding-top:10px; margin:0px;"><p class="error" style="color:red; font-size:12px;margin:0px;">This field is required</p></div>');
                        errorCount++;
                    }else if (!validFirstName) {
                        $('#FirstName').after('<div class="error" style="padding-top:10px; margin:0px;"><p class="error" style="color:red; font-size:12px;margin:0px;">Enter a valid First Name</p></div>');
                        errorCount++;
                    }

                    var validLastName = nameRegEx.test(last_name);
                    if (last_name.length < 1) {
                        $('#LastName').after('<div class="error" style="padding-top:10px;margin:0px;"><p class="error" style="color:red; font-size:12px;margin:0px;">This field is required</p></div>');
                        errorCount++;
                    }else if(!validLastName) {
                        $('#LastName').after('<div class="error" style="padding-top:10px;margin:0px;"><p class="error" style="color:red; font-size:12px;margin:0px;">Enter a valid Last Name</p></div>');
                        errorCount++;
                    }


                    if (email.length < 1) {
                        $('#Email').after('<div class="error" style="padding-top:10px;margin:0px;"><p style="color:red; font-size:12px;margin:0px;">This field is required</p></div>');
                        errorCount++;
                    } else {

                        var validEmail = EmailregEx.test(email);
                        if (!validEmail) {
                            $('#Email').after('<div class="error" style="padding-top:10px;"margin:0px;><p class="error" style="color:red; font-size:12px;margin:0px;">Enter a valid email</p></div>');
                            errorCount++;
                        }
                    }
                    if (password.length < 8 ) {
                        $('#passworderror').append('<div class="error" style="padding-top:10px;margin:0px;margin:0px;"><p class="error" style="color:red; font-size:12px;">Password must be at least 8 characters</p></div>');
                        errorCount++;
                    }else if(password!=repeatPassword){
                        $('#passworderror').append('<div class="error" style="padding-top:10px;margin:0px;margin:0px;"><p class="error" style="color:red; font-size:12px;">Password do not match.</p></div>');
                        errorCount++;
                    }
                    var role=$("#role option:selected").text();
                    if(role=="Select your account type"){
                        $('#fundingerror').after('<div class="error" id="sourceerror" style="padding-top:10px; margin:0px;"><p class="error" style="color:red; font-size:12px;margin:0px;">Please select one of the options</p></div>');
                        $('#fundingerror').remove();
                        errorCount++;
                    }else{
                        $('#sourceerror').remove();
                    }

                    $('#role').change(function(){
                        var role=$("#role option:selected").text();
                        if(role=="Select your account type"){
                            $('#sourceerror').remove();
                            $('#fundingerror').after('<div class="error" id="sourceerror" style="padding-top:10px; margin:0px;"><p class="error" style="color:red; font-size:12px;margin:0px;">Please select one of the options</p></div>');
                            $('#fundingerror').remove();
                        }else{
                            $('#sourceerror').remove();
                        }
                    });

                    if(errorCount==0){
                        var role=$("#role option:selected").val();
                        alert(role);
                        $.ajax({
                            url: "lib.php",
                            type: "POST",
                            data: {
                                page:'updateUser',
                                userId:userId,
                                FirstName: first_name,
                                LastName: last_name,
                                Email: email,
                                Password: password,
                                Role:role,
                            },
                            success: function(data){
                                //alert('successfully edited');
                                $('#userUpdationStatus').css('display','block');
                            }
                        });

                    }
                });

            });
        </script>
        <?php
        global $pdo;

        try {
            $query = "select * from `user` where `id`=:userId";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam('userId', $userId, PDO::PARAM_STR);
            $stmt->execute();
            $row   = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error : ".$e->getMessage();
        }

        ?>
        <!-- Page Heading -->
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <div class="p-5">
                    <div class="jumbotron bg-gray-200 border-bottom-success">
                        <div class="text-center">
                            <h1 class="h4 text-gray-900 mb-4">Update User Details !</h1>
                        </div>
                        <div class="alert alert-success" role="alert" id="userUpdationStatus" style="display: none">
                            The user account is updated successfully
                        </div>
                        <form class="user" id="updateform" method="post" action="">
                            <div class="form-group row">
                                <input type="text" class="form-control form-control-user" id="userId" name="firstname" value="<?php echo $row["id"]; ?>" style="display: none;">
                                <div class="col-sm-6 mb-3 mb-sm-0">
                                    <input type="text" class="form-control form-control-user" id="FirstName"
                                           placeholder="First Name" name="firstname" value="<?php echo $row["firstname"]; ?>">
                                </div>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control form-control-user" id="LastName"
                                           placeholder="Last Name" name="lastname" value="<?php echo $row["lastname"]; ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <input type="email" class="form-control form-control-user" id="Email"
                                       placeholder="Email Address" name="email" value="<?php echo $row["email"]; ?>">
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-6 mb-3 mb-sm-0">
                                    <input type="password" class="form-control form-control-user"
                                           id="Password" placeholder="Password" name="password" >
                                </div>
                                <div class="col-sm-6">
                                    <input type="password" class="form-control form-control-user"
                                           id="RepeatPassword" placeholder="Repeat Password" name="repeatpassword" v>
                                </div>

                                <div id="passworderror" style="margin-left: 20px;">

                                </div>
                                <div class="form-group">
                                    <select name="passwordSetting" class="dropdown mb-4 btn btn-primary dropdown-toggle">

                                        <option value="nothing">Select Password setting</option>
                                        <option value="keep">Keep same password</option>
                                        <option value="change">Change password</option>
                                    </select>
                                    <div>
                                        <span><b>Currently Selected Category:</b> </span> FDC Compliance                            </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <select class="browser-default custom-select form-select" id="role">
                                    <option value="funding" selected="">Select your account type</option>
                                    <option value="1" style="">Admin</option>
                                    <option value="2" style="">Educator</option>
                                    <option value="3">Parent</option>
                                </select>
                                <div ><br>Current role: <span style="color:green;"><?php if ($row["role"]==1){echo 'Admin';}elseif($row["role"]==2){echo 'Educator';}else{echo 'Parent';} ?></span></div>
                                <div id="fundingerror" style="margin-left: 20px;"></div>
                            </div>
                            <input type="submit" name="submitBtnLogin" id="submitBtnLogin" value="Update" class="btn btn-primary btn-user btn-block" />

                            <div id="messageblock" style="padding-top:10px; display: none; ">
                                <div class="card mb-4 py-3 border-left-success" style="padding-top:0px !important;padding-bottom:0px !important; ">
                                    <div class="card-body" style="color:#1cc88a" id="msg">
                                    </div>
                                </div>
                            </div>

                            <div id="errorblock" style="padding-top:10px; display: none; ">
                                <div class="card mb-4 py-3 border-left-danger" style="padding-top:0px !important;padding-bottom:0px !important; ">
                                    <div class="card-body" style="color:red" id="errormsg">
                                    </div>
                                </div>
                            </div>

                            <hr>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php
    }
    ///////////////////////////////////////////
    //////////Edit specific User//////////////
    /////////////////////////////////////////
    ///
    public function deleteUser($userId,$source){
        global $pdo;
        $sql = "Delete from user WHERE id=?";
        $stmt= $pdo->prepare($sql);
        $stmt->execute([$userId]);
        if($source=='allusers'){
            $URL="?page=currentUsers&status=deleted";
        }else{
            $URL="?page=pendingUsers&status=deleted";
        }
        echo "<script type='text/javascript'>document.location.href='{$URL}';</script>";
        echo '<META HTTP-EQUIV="refresh" content="0;URL=' . $URL . '">';
    }
    ///////////////////////////////////////////
    //////////Pending Users//////////////
    /////////////////////////////////////////
    ///
    public function pendingUsers(){
        if (isset($_GET['status']) && $_GET['status']=="accepted"){
            echo '<div class="alert alert-success" role="alert" id="userUpdationStatus" style="display: block;">
                    The user account is updated successfully
                </div>';
        }elseif (isset($_GET['status']) && $_GET['status']=="deleted"){
            echo '<div class="alert alert-danger" role="alert" id="userUpdationStatus" style="display: block;">
                    The user account is deleted successfully
                </div>';
        }
        global $pdo;
        try {
            $query = "select * from `user` where `verified`=0";
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $row   = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error : ".$e->getMessage();
        }
        ?>
        <h1 class="h3 mb-4 text-gray-800" style="text-align: center; padding-top: 30px;">Pending Users</h1>
        <!-- Page Heading -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">You can edit,update and delete users through this panel</h6>
                    </div>
                    <div class="card-body border-bottom-success">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                <tr>
                                    <th style="display:none;">Id</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tfoot>
                                <tr>
                                    <th style="display:none;">Id</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Action</th>
                                </tr>
                                </tfoot>
                                <tbody>
                                <?php
                                foreach ($row as $data)
                                {
                                    ?>
                                    <tr>
                                        <td style="display:none;"><?php echo $data["id"];?></td>
                                        <td><?php echo $data["first name"].' '.$data["last name"];?></td>
                                        <td><?php echo $data["email"];?></td>
                                        <td><?php if($data["role"]=='1'){
                                            echo 'admin';
                                        }elseif($data["role"]=='2'){
                                            echo 'Educator';
                                        }else{
                                            echo 'Parent';
                                        } ?></td>

                                        <td>
                                            <a href="javascript:void(0);" onclick="confirmAccept(<?php echo $data['id']; ?>)" class="btn btn-success btn-circle btn-md" id="accept<?php echo $data["id"];?>">
                                                <i class="fas fa-check"></i>
                                            </a>

                                            <a href="?page=editUser&user=<?php echo $data["id"];?>" class="btn btn-primary btn-circle btn-md" id="edit<?php echo $data["id"];?>">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="javascript:void(0);" onclick="confirmDelete(<?php echo $data['id']; ?>)" class="btn btn-danger btn-circle btn-md" id="reject<?php echo $data["id"];?>">
                                                <i class="fas fa-trash"></i>
                                            </a>

                                        </td>
                                    </tr>
                                <?php }?>
                                </tbody>
                            </table>
                            <script>
                                function confirmDelete(meetingID) {
                                    Swal.fire({
                                        title: 'Are you sure?',
                                        text: "You won't be able to revert this!",
                                        icon: 'warning',
                                        showCancelButton: true,
                                        confirmButtonColor: '#d33',
                                        cancelButtonColor: '#3085d6',
                                        confirmButtonText: 'Yes, delete it!'
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            // Redirect to the deletion URL
                                            window.location.href = `?page=deleteUser&source=pendingUser&user=${meetingID}`;
                                        }
                                    });
                                }
                                function confirmAccept(meetingID) {
                                    Swal.fire({
                                        title: 'Are you sure?',
                                        text: "You won't be able to revert this!",
                                        icon: 'success',
                                        showCancelButton: true,
                                        confirmButtonColor: '#3bdd33',
                                        cancelButtonColor: '#3085d6',
                                        confirmButtonText: 'Yes, Accept the user!'
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            // Redirect to the deletion URL
                                            window.location.href = `?page=acceptUser&user=${meetingID}`;
                                        }
                                    });
                                }
                            </script> 
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <?php
    }
    ///////////////////////////////////////////
    //////////Accept Users//////////////
    /////////////////////////////////////////
    public function acceptUser($userid){
        global $pdo;
        $verified=1;
        try {
            $sql = "UPDATE user SET verified=? WHERE id=?";
            $stmt= $pdo->prepare($sql);
            $stmt->execute([$verified,$userid]);

        } catch (PDOException $e) {
            echo "Error : ".$e->getMessage();
        }
        $URL="?page=pendingUsers&status=accepted";
        echo "<script type='text/javascript'>document.location.href='{$URL}';</script>";
        echo '<META HTTP-EQUIV="refresh" content="0;URL=' . $URL . '">';
    }
    ///////////////////////////////////////////
    //////////Add new User//////////////
    /////////////////////////////////////////
     public function addNewUser(){
        ?>
         <script type="text/javascript" src="js/jquery-3.6.0.min.js"></script>
         <script type="text/javascript">
             $(document).ready(function() {

                 $('#registrationform').submit(function(e) {
                     e.preventDefault();
                     $("#errorblock").css("display","none");
                     $("#messageblock").css("display","none");
                     var first_name = $('#FirstName').val();
                     var last_name = $('#LastName').val();
                     var email = $('#Email').val();
                     var password = $('#Password').val();
                     var repeatPassword = $('#RepeatPassword').val();
                     var EmailregEx = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                     var nameRegEx=/^(?=.{1,50}$)[a-z]+(?:['_.\s][a-z]+)*$/i;
                     var errorCount=0;
                     var page="adminPanel";
                     var role=$("#myselect option:selected").text();
                     $(".error").remove();

                     var validFirstName = nameRegEx.test(first_name);
                     if (first_name.length < 1) {
                         $('#FirstName').after('<div class="error" style="padding-top:10px; margin:0px;"><p class="error" style="color:red; font-size:12px;margin:0px;">This field is required</p></div>');
                         errorCount++;
                     }else if (!validFirstName) {
                         $('#FirstName').after('<div class="error" style="padding-top:10px; margin:0px;"><p class="error" style="color:red; font-size:12px;margin:0px;">Enter a valid First Name</p></div>');
                         errorCount++;
                     }

                     var validLastName = nameRegEx.test(last_name);
                     if (last_name.length < 1) {
                         $('#LastName').after('<div class="error" style="padding-top:10px;margin:0px;"><p class="error" style="color:red; font-size:12px;margin:0px;">This field is required</p></div>');
                         errorCount++;
                     }else if(!validLastName) {
                         $('#LastName').after('<div class="error" style="padding-top:10px;margin:0px;"><p class="error" style="color:red; font-size:12px;margin:0px;">Enter a valid Last Name</p></div>');
                         errorCount++;
                     }


                     if (email.length < 1) {
                         $('#Email').after('<div class="error" style="padding-top:10px;margin:0px;"><p style="color:red; font-size:12px;margin:0px;">This field is required</p></div>');
                         errorCount++;
                     } else {

                         var validEmail = EmailregEx.test(email);
                         if (!validEmail) {
                             $('#Email').after('<div class="error" style="padding-top:10px;"margin:0px;><p class="error" style="color:red; font-size:12px;margin:0px;">Enter a valid email</p></div>');
                             errorCount++;
                         }
                     }
                     if (password.length < 8 ) {
                         $('#passworderror').append('<div class="error" style="padding-top:10px;margin:0px;margin:0px;"><p class="error" style="color:red; font-size:12px;">Password must be at least 8 characters</p></div>');
                         errorCount++;
                     }else if(password!=repeatPassword){
                         $('#passworderror').append('<div class="error" style="padding-top:10px;margin:0px;margin:0px;"><p class="error" style="color:red; font-size:12px;">Password do not match.</p></div>');
                         errorCount++;
                     }
                     var role=$("#role option:selected").text();
                     if(role=="Select your account type"){
                         $('#fundingerror').after('<div class="error" id="sourceerror" style="padding-top:10px; margin:0px;"><p class="error" style="color:red; font-size:12px;margin:0px;">Please select one of the options</p></div>');
                         $('#fundingerror').remove();
                         errorCount++;
                     }else{
                         $('#sourceerror').remove();
                     }

                     $('#role').change(function(){
                         var role=$("#role option:selected").text();
                         if(role=="Select your account type"){
                             $('#sourceerror').remove();
                             $('#fundingerror').after('<div class="error" id="sourceerror" style="padding-top:10px; margin:0px;"><p class="error" style="color:red; font-size:12px;margin:0px;">Please select one of the options</p></div>');
                             $('#fundingerror').remove();
                         }else{
                             $('#sourceerror').remove();
                         }
                     });

                     if(errorCount==0){
                         var role=$("#role option:selected").val();
                         $.ajax({
                             url: "lib.php",
                             type: "POST",
                             data: {
                                 page:'addNewUser',
                                 FirstName: first_name,
                                 LastName: last_name,
                                 Email: email,
                                 Password: password,
                                 Page:page,
                                 Role:role
                             },
                             success: function(data){
                                 var a = data.includes("Success");
                                 if (a) {
                                     $("#messageblock").css("display","block");
                                     $('#msg').html(data);
                                     $('#registrationform').find('input').val('')
                                 } else {
                                     $("#errorblock").css("display","block");
                                     $('#errormsg').html(data);
                                 }

                             }
                         });

                     }
                 });

             });
         </script>
         <!-- Page Heading -->
         <div class="row">
             <div class="col-md-6 offset-md-3">
                 <div class="p-5">
                     <div class="jumbotron bg-gray-200 border-bottom-success">
                         <div class="text-center">
                             <h1 class="h4 text-gray-900 mb-4">Add New User</h1>
                         </div>
                         <form class="user" id="registrationform" method="post" action="">
                             <div class="form-group row">
                                 <div class="col-sm-6 mb-3 mb-sm-0">
                                     <input type="text" class="form-control form-control-user" id="FirstName"
                                            placeholder="First Name" name="firstname">
                                 </div>
                                 <div class="col-sm-6">
                                     <input type="text" class="form-control form-control-user" id="LastName"
                                            placeholder="Last Name" name="lastname">
                                 </div>
                             </div>
                             <div class="form-group">
                                 <input type="email" class="form-control form-control-user" id="Email"
                                        placeholder="Email Address" name="email">
                             </div>
                             <div class="form-group row">
                                 <div class="col-sm-6 mb-3 mb-sm-0">
                                     <input type="password" class="form-control form-control-user"
                                            id="Password" placeholder="Password" name="password">
                                 </div>
                                 <div class="col-sm-6">
                                     <input type="password" class="form-control form-control-user"
                                            id="RepeatPassword" placeholder="Repeat Password" name="repeatpassword">
                                 </div>
                                 <div id="passworderror" style="margin-left: 20px;">

                                 </div>
                             </div>
                             <div class="col-sm-6">
                                 <div class="form-group row">
                                     <select class="browser-default custom-select form-select" id="role">
                                         <option value="funding" selected="">Select your account type</option>
                                         <option value="1" style="">Admin</option>
                                         <option value="2" style="">Educator</option>
                                         <option value="3">Parent</option>
                                     </select>
                                     <div id="fundingerror" style="margin-left: 20px;"></div>
                                 </div>
                             </div>

                             <input type="submit" name="submitBtnLogin" id="submitBtnLogin" value="Register Account" class="btn btn-primary btn-user btn-block" />

                             <div id="messageblock" style="padding-top:10px; display: none; ">
                                 <div class="card mb-4 py-3 border-left-success" style="padding-top:0px !important;padding-bottom:0px !important; ">
                                     <div class="card-body" style="color:#1cc88a" id="msg">
                                     </div>
                                 </div>
                             </div>

                             <div id="errorblock" style="padding-top:10px; display: none; ">
                                 <div class="card mb-4 py-3 border-left-danger" style="padding-top:0px !important;padding-bottom:0px !important; ">
                                     <div class="card-body" style="color:red" id="errormsg">
                                     </div>
                                 </div>
                             </div>

                             <hr>
                         </form>
                     </div>
                 </div>
             </div>
         </div>
         <?php
     }

}


/*****************************************
 * Resources Class
 * ****************************************
 */
class Resources{
    ///////////////////////////////////////////
    //////////View All Resources//////////////
    /////////////////////////////////////////
    public function viewAllResources(){
        $row=getAllResources();
        $currentRole=$_SESSION['role'];
        //var_dump($row);
        $resCount=count($row);
        ?>
        <!-- Page Heading -->
        <style>
            label{
                padding: 20px;
                background: #fff;
                color: #999;
                border-bottom: 2px solid #f0f0f0;
            }
        </style>
        <h1 class="h3 mb-4 text-gray-800" style="text-align: center !important;">Resources</h1>
        <?php if($resCount==0){

            ?>
            <div class="card mb-4 py-3 border-left-danger" style="padding-top:0px !important;padding-bottom:0px !important; ">
                <div class="card-body" id="msg">
                    Resources are not available!
                </div>
            </div>
            <?php
            exit(0);
        }


        $cat1=$cat2=$cat3=$cat4=$cat5=$cat6=$cat7=$cat8=$cat9=$cat10=$cat11=$cat12=$cat13='';
        //echo "<pre>";
        //print_r($row);
        //echo "</pre>";
        for ($i=0; $i <$resCount ; $i++) {

            $resourcePolicy=$row[$i]["role"];

            $currentCat=$row[$i]["category"];
            //echo $row[$i]['category'];
            //echo $currentCat;
            if($currentRole==$resourcePolicy || $resourcePolicy=='23' || $currentRole=='1'){

                if($row[$i]["type"]=="link"){

                    $link=linkGeneration($row[$i]["title"],$row[$i]["source"]);
                    if( strcmp($currentCat, "FDC Compliance") == 0) {
                        $cat1 = $cat1 . $link;
                    } elseif(strcmp($currentCat, "Frameworks") == 0) {
                        $cat2 = $cat2 . $link;
                    }elseif(strcmp($currentCat, "Newsletters") == 0) {
                        $cat3 = $cat3 . $link;
                    }elseif(strcmp($currentCat, "Fact Sheets") == 0) {
                        $cat4 = $cat4 . $link;
                    }elseif(strcmp($currentCat, "FDC Insurance") == 0) {
                        $cat5 = $cat5 . $link;
                    }elseif(strcmp($currentCat, "Child Safe Standards") == 0) {
                        $cat6 = $cat6 . $link;
                    }elseif(strcmp($currentCat, "Online Safety") == 0) {
                        $cat7 = $cat7 . $link;
                    }elseif(strcmp($currentCat, "Educator Resources") == 0 || strcmp($currentCat, "General and Legal Forms") == 0) {
                        $cat8 = $cat8 . $link;
                    }elseif(strcmp($currentCat, "Educational Resources") == 0) {
                        $cat9 = $cat9 . $link;
                    }elseif(strcmp($currentCat, "COVID-19") == 0) {
                        $cat10 = $cat10 . $link;
                    }elseif(strcmp($currentCat, "Resources in other languages") == 0) {
                        $cat11 = $cat11 . $link;
                    }elseif(strcmp($currentCat, "Reportable Conduct Scheme") == 0) {
                        $cat12 = $cat12 . $link;
                    }elseif(strcmp($currentCat, "Safety Data") == 0) {
                        $cat13 = $cat13 . $link;
                    }

                } else{
                    $file=fileGeneration($row[$i]["title"],$row[$i]["version"],$row[$i]["source"],$row[$i]["category"]);
                    if(strcmp($currentCat, "FDC Compliance") == 0) {
                        $cat1 = $cat1 . $file;
                    } elseif(strcmp($currentCat, "Frameworks") == 0) {
                        $cat2 = $cat2 . $file;
                    }elseif(strcmp($currentCat, "Newsletters") == 0) {
                        $cat3 = $cat3 . $file;
                    }elseif(strcmp($currentCat, "Fact Sheets") == 0) {
                        $cat4 = $cat4 . $file;
                    }elseif(strcmp($currentCat, "FDC Insurance") == 0) {
                        $cat5 = $cat5 . $file;
                    }elseif(strcmp($currentCat, "Child Safe Standards") == 0) {
                        $cat6 = $cat6 . $file;
                    }elseif(strcmp($currentCat, "Online Safety") == 0) {
                        $cat7 = $cat7 . $file;
                    }elseif(strcmp($currentCat, "Educator Resources") == 0 || strcmp($currentCat, "General and Legal Forms") == 0) {
                        $cat8 = $cat8 . $file;
                    }elseif(strcmp($currentCat, "Educational Resources") == 0) {
                        $cat9 = $cat9 . $file;
                    }elseif(strcmp($currentCat, "COVID-19") == 0) {
                        $cat10 = $cat10 . $file;
                    }elseif(strcmp($currentCat, "Resources in other languages") == 0) {
                        $cat11 = $cat11 . $file;
                    }elseif(strcmp($currentCat, "Reportable Conduct Scheme") == 0) {
                        $cat12 = $cat12 . $file;
                    }elseif(strcmp($currentCat, "Safety Data") == 0) {
                        $cat13 = $cat13 . $file;
                    }
                }

            }

        }
        ?>

        
        <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700,200" rel="stylesheet" />
        <link href="https://use.fontawesome.com/releases/v5.0.6/css/all.css" rel="stylesheet">

        <div class="container-fluid mt-5" id="tabsforpc">
            <div class="row">
                <div class="col-md-12 ml-auto col-xl-12 mr-auto" >
                    <!-- Nav tabs -->
                    <div class="card">
                        <div class="card-header navbar navbar-expand-lg navbar-light bg-light" style="background: linear-gradient(to right,#ff5e62,#ff9966) !important;">
                            <ul class="nav nav-tabs justify-content-center" role="tablist" >
                                <li class="nav-item">
                                    <a class="nav-link active" data-toggle="tab" href="#atab1" role="tab" style="color: white !important;">
                                        <i class="fa fa-briefcase"></i> FDC Compliance
                                    </a>
                                </li>
                                <li class="nav-item" >
                                    <a class="nav-link" data-toggle="tab" href="#atab2" role="tab" style="color: white !important;">
                                        <i class="fa fa-archive"></i> Frameworks
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#atab9" role="tab" style="color: white !important;">
                                        <i class="fa fa-book"></i> Educational Resources
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#atab3" role="tab" style="color: white !important;">
                                        <i class="fa fa-envelope-open"></i> Newsletters
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#atab4" role="tab" style="color: white !important;">
                                        <i class="fa fa-window-maximize"></i> Fact Sheets
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#atab5" role="tab" style="color: white !important;">
                                        <i class="fa fa-sticky-note"></i> FDC Insurance
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#atab6" role="tab" style="color: white !important;">
                                        <i class="fa fa-child"></i> Child Safe Standards
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#atab12" role="tab" style="color: white !important;">
                                        <i class="fa fa-info-circle"></i> Reportable Conduct Scheme
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#atab7" role="tab" style="color: white !important;">
                                        <i class="fa fa-globe"></i> Online Safety
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#atab8" role="tab" style="color: white !important;">
                                        <i class="fa fa-window-restore"></i> General and Legal Forms
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#atab10" role="tab" style="color: white !important;">
                                        <i class="fa fa-puzzle-piece"></i> COVID-19
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#atab11" role="tab" style="color: white !important;">
                                        <i class="fa fa-american-sign-language-interpreting"></i> Resources in other languages
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#atab13" role="tab" style="color: white !important;">
                                        <i class="fa fa-fire-extinguisher"></i> Safety Data
                                    </a>
                                </li>


                            </ul>
                        </div>

                        <div class="card-body" style="background: linear-gradient(to right,#ff5e62,#ff9966) !important;">
                            <!-- Tab panes -->
                            <div class="tab-content text-center">
                                <div class="tab-pane active" id="atab1" role="tabpanel">
                                    <h3 style="color: white !important;">FDC Compliance</h3>
                                    <div class="row">
                                        <?php if(empty($cat1)){
                                            echo '<h4 style="color: white !important;">Resources are not available for this category.</h4>';
                                        }else{
                                            echo $cat1;
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="tab-pane" id="atab2" role="tabpanel">
                                    <h3 style="color: white !important;">Frameworks</h3>
                                    <div class="row">
                                        <?php if(empty($cat2)){
                                            echo '<h4 style="color: white !important;">Resources are not available for this category.</h4>';
                                        }else{
                                            echo $cat2;
                                        }?>
                                    </div>
                                </div>
                                <div class="tab-pane" id="atab9" role="tabpanel">
                                    <h3 style="color: white !important;">Educational Resources</h3>
                                    <div class="row">
                                        <?php
                                        if(empty($cat9)){
                                            echo '<h4 style="color: white !important;">Resources are not available for this category.</h4>';
                                        }else{
                                            echo $cat9;
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="tab-pane" id="atab3" role="tabpanel">
                                    <h3 style="color: white !important;">Newsletters</h3>
                                    <div class="row">
                                        <?php if(empty($cat3)){
                                            echo '<h4 style="color: white !important;">Resources are not available for this category.</h4>';
                                        }else{
                                            echo $cat3;
                                        }?>
                                    </div>
                                </div>
                                <div class="tab-pane" id="atab4" role="tabpanel">
                                    <h3 style="color: white !important;">Fact Sheets</h3>
                                    <div class="row">
                                        <?php if(empty($cat4)){
                                            echo '<h4 style="color: white !important;">Resources are not available for this category.</h4>';
                                        }else{
                                            echo $cat4;
                                        }?>
                                    </div>
                                </div>
                                <div class="tab-pane" id="atab5" role="tabpanel">
                                    <h3 style="color: white !important;">FDC Insurance</h3>
                                    <div class="row">
                                        <?php if(empty($cat5)){
                                            echo '<h4 style="color: white !important;">Resources are not available for this category.</h4>';
                                        }else{
                                            echo $cat5;
                                        }?>
                                    </div>
                                </div>
                                <div class="tab-pane" id="atab6" role="tabpanel">
                                    <h3 style="color: white !important;">Child Safe Standards</h3>
                                    <div class="row">
                                        <?php if(empty($cat6)){
                                            echo '<h4 style="color: white !important;">Resources are not available for this category.</h4>';
                                        }else{
                                            echo $cat6;
                                        }?>
                                    </div>
                                </div>
                                <div class="tab-pane" id="atab12" role="tabpanel">
                                    <h3 style="color: white !important;">Reportable Conduct Scheme</h3>
                                    <div class="row">
                                        <?php if(empty($cat12)){
                                            echo '<h4 style="color: orange;">Resources are not available for this category.</h4>';
                                        }else{
                                            echo $cat12;
                                        }?>
                                    </div>
                                </div>
                                <div class="tab-pane" id="atab7" role="tabpanel">
                                    <h3 style="color: white !important;">Online Safety</h3>
                                    <div class="row">
                                        <?php if(empty($cat7)){
                                            echo '<h4 style="color: white !important;">Resources are not available for this category.</h4>';
                                        }else{
                                            echo $cat7;
                                        }?>
                                    </div>
                                </div>
                                <div class="tab-pane" id="atab8" role="tabpanel">
                                    <h3 style="color: white !important;">General and Legal Forms</h3>
                                    <div class="row">
                                        <?php if(empty($cat8)){
                                            echo '<h4 style="color: white !important;">Resources are not available for this category.</h4>';
                                        }else{
                                            echo $cat8;
                                        }?>
                                    </div>
                                </div>
                                <div class="tab-pane" id="atab10" role="tabpanel">
                                    <h3 style="color: white !important;">COVID-19</h3>
                                    <div class="row">
                                        <?php if(empty($cat10)){
                                            echo '<h4 style="color: white !important;">Resources are not available for this category.</h4>';
                                        }else{
                                            echo $cat10;
                                        }?>
                                    </div>
                                </div>
                                <div class="tab-pane" id="atab11" role="tabpanel">
                                    <h3 style="color: white !important;">Resources in other languages</h3>
                                    <div class="row">
                                        <?php if(empty($cat11)){
                                            echo '<h4 style="color: white !important;">Resources are not available for this category.</h4>';
                                        }else{
                                            echo $cat11;
                                        }?>
                                    </div>
                                </div>
                                <div class="tab-pane" id="atab13" role="tabpanel">
                                    <h3 style="color: white !important;">Resources in other languages</h3>
                                    <div class="row">
                                        <?php if(empty($cat13)){
                                            echo '<h4 style="color: white !important;">Resources are not available for this category.</h4>';
                                        }else{
                                            echo $cat13;
                                        }?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <section class="accordion_two_section ptb-100" id="accordinforphones">
            <div class="container">
                <div class="row">
                    <div class="col-sm-6 accordionTwo">
                        <div class="panel-group" id="accordionTwoLeft">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a data-toggle="collapse" data-parent="#accordionTwoLeft" href="#collapseTwoLeftone" aria-expanded="false" class="collapsed">
                                            FDC Compliance
                                        </a>
                                    </h4>
                                </div>
                                <div id="collapseTwoLeftone" class="panel-collapse collapse" aria-expanded="false" role="tablist" style="height: 0px;">
                                    <div class="panel-body">
                                        <?php if(empty($cat1)){
                                            echo '<h4 style="color: orange;">Resources are not available for this category.</h4>';
                                        }else{
                                            echo $cat1;
                                        }?>
                                    </div>
                                </div>
                            </div>
                            <!-- /.panel-default -->
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a class="collapsed" data-toggle="collapse" data-parent="#accordionTwoLeft" href="#collapseTwoLeftTwo" aria-expanded="false">
                                            Frameworks
                                        </a>
                                    </h4>
                                </div>
                                <div id="collapseTwoLeftTwo" class="panel-collapse collapse" aria-expanded="false" role="tablist">
                                    <div class="panel-body">
                                        <?php if(empty($cat2)){
                                            echo '<h4 style="color: orange;">Resources are not available for this category.</h4>';
                                        }else{
                                            echo $cat2;
                                        }?>
                                    </div>
                                </div>
                            </div>
                            <!-- /.panel-default -->
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a class="collapsed" data-toggle="collapse" data-parent="#accordionTwoLeft" href="#collapseER" aria-expanded="false">
                                            Educational Resources
                                        </a>
                                    </h4>
                                </div>
                                <div id="collapseER" class="panel-collapse collapse" aria-expanded="false" role="tablist">
                                    <div class="panel-body">
                                        <?php
                                        if(empty($cat9)){
                                            echo '<h4 style="color: orange;">Resources are not available for this category.</h4>';
                                        }else{
                                            echo $cat9;
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <!-- /.panel-default -->
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a class="collapsed" data-toggle="collapse" data-parent="#accordionTwoLeft" href="#collapseNS" aria-expanded="false">
                                            Newsletters
                                        </a>
                                    </h4>
                                </div>
                                <div id="collapseNS" class="panel-collapse collapse" aria-expanded="false" role="tablist">
                                    <div class="panel-body">
                                        <?php if(empty($cat3)){
                                            echo '<h4 style="color: orange;">Resources are not available for this category.</h4>';
                                        }else{
                                            echo $cat3;
                                        }?>
                                    </div>
                                </div>
                            </div>
                            <!-- /.panel-default -->
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a class="collapsed" data-toggle="collapse" data-parent="#accordionTwoLeft" href="#collapseFS" aria-expanded="false">
                                            Fact Sheets
                                        </a>
                                    </h4>
                                </div>
                                <div id="collapseFS" class="panel-collapse collapse" aria-expanded="false" role="tablist">
                                    <div class="panel-body">
                                        <?php if(empty($cat4)){
                                            echo '<h4 style="color: orange;">Resources are not available for this category.</h4>';
                                        }else{
                                            echo $cat4;
                                        }?>
                                    </div>
                                </div>
                            </div>
                            <!-- /.panel-default -->
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a class="collapsed" data-toggle="collapse" data-parent="#accordionTwoLeft" href="#collapseTwoRightone" aria-expanded="false">
                                            FDC Insurance
                                        </a>
                                    </h4>
                                </div>
                                <div id="collapseTwoRightone" class="panel-collapse collapse" aria-expanded="false" role="tablist">
                                    <div class="panel-body">
                                        <?php if(empty($cat5)){
                                            echo '<h4 style="color: orange;">Resources are not available for this category.</h4>';
                                        }else{
                                            echo $cat5;
                                        }?>
                                    </div>
                                </div>
                            </div>
                            <!-- /.panel-default -->
                        </div>
                        <!--end of /.panel-group-->
                    </div>
                    <!--end of /.col-sm-6-->
                    <div class="col-sm-6 accordionTwo">
                        <div class="panel-group" id="accordionTwoRight">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a class="collapsed" data-toggle="collapse" data-parent="#accordionTwoRight" href="#collapseTwoRightTwo" aria-expanded="false">
                                            Child Safe Standards
                                        </a>
                                    </h4>
                                </div>
                                <div id="collapseTwoRightTwo" class="panel-collapse collapse" aria-expanded="false" role="tablist">
                                    <div class="panel-body">
                                        <?php if(empty($cat6)){
                                            echo '<h4 style="color: orange;">Resources are not available for this category.</h4>';
                                        }else{
                                            echo $cat6;
                                        }?>
                                    </div>
                                </div>
                            </div>
                            <!-- /.panel-default -->
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a class="collapsed" data-toggle="collapse" data-parent="#accordionTwoRight" href="#collapseRCS" aria-expanded="false">
                                            Reportable Conduct Scheme
                                        </a>
                                    </h4>
                                </div>
                                <div  id="collapseRCS" class="panel-collapse collapse in" aria-expanded="false" role="tablist">
                                    <div class="panel-body">
                                        <?php if(empty($cat12)){
                                            echo '<h4 style="color: orange;">Resources are not available for this category.</h4>';
                                        }else{
                                            echo $cat12;
                                        }?>
                                    </div>
                                </div>
                            </div>
                            <!-- /.panel-default -->
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a class="collapsed"  data-toggle="collapse" data-parent="#accordionTwoRight" href="#collapseOS" aria-expanded="false">
                                            Online Safety
                                        </a>
                                    </h4>
                                </div>
                                <div id="collapseOS" class="panel-collapse collapse in" aria-expanded="false" role="tablist">
                                    <div class="panel-body">
                                        <?php if(empty($cat7)){
                                            echo '<h4 style="color: orange;">Resources are not available for this category.</h4>';
                                        }else{
                                            echo $cat7;
                                        }?>
                                    </div>
                                </div>
                            </div>
                            <!-- /.panel-default -->
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a class="collapsed" data-toggle="collapse" data-parent="#accordionTwoRight" href="#collapseEduR" aria-expanded="false">
                                            Educator Resources
                                        </a>
                                    </h4>
                                </div>
                                <div id="collapseEduR" class="panel-collapse collapse in" aria-expanded="false" role="tablist">
                                    <div class="panel-body">
                                        <?php if(empty($cat8)){
                                            echo '<h4 style="color: orange;">Resources are not available for this category.</h4>';
                                        }else{
                                            echo $cat8;
                                        }?>
                                    </div>
                                </div>
                            </div>
                            <!-- /.panel-default -->
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a class="collapsed" data-toggle="collapse" data-parent="#accordionTwoRight" href="#collapseroil" aria-expanded="false">
                                            COVID-19
                                        </a>
                                    </h4>
                                </div>
                                <div id="collapsecovid" class="panel-collapse collapse in" aria-expanded="false" role="tablist">
                                    <div class="panel-body">
                                        <?php if(empty($cat10)){
                                            echo '<h4 style="color: orange;">Resources are not available for this category.</h4>';
                                        }else{
                                            echo $cat10;
                                        }?>
                                    </div>
                                </div>
                            </div>
                            <!-- /.panel-default -->
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a class="collapsed" data-toggle="collapse" data-parent="#accordionTwoRight" href="#collapseroil" aria-expanded="false">
                                            Resources in other languages
                                        </a>
                                    </h4>
                                </div>
                                <div id="collapseroil" class="panel-collapse collapse in" aria-expanded="false" role="tablist">
                                    <div class="panel-body">
                                        <?php if(empty($cat11)){
                                            echo '<h4 style="color: orange;">Resources are not available for this category.</h4>';
                                        }else{
                                            echo $cat11;
                                        }?>
                                    </div>
                                </div>
                            </div>
                            <!-- /.panel-default -->
                        </div>
                        <!--end of /.panel-group-->
                    </div>
                    <!--end of /.col-sm-6-->
                </div>
            </div>
            <!--end of /.container-->
        </section>


        <?php
    }

    ///////////////////////////////////////////
    //////////Read Resources//////////////
    /////////////////////////////////////////
    public function readResource(){
        if (isset($_GET['source'])=='home'){
            echo '<a href="?page=home" class="btn btn-secondary btn-icon-split">
                                        <span class="icon text-white-50">
                                            <i class="fas fa-arrow-left"></i>
                                        </span>
                                        <span class="text">Go back</span>
                                    </a>';
        }
        ?>
        <style>
            @media only screen and (max-width: 480px) {
                .containerIframe {
                //position: relative !important;
                    overflow: hidden !important;
                    width: 100% !important;
                    padding-top: 56.25% !important; /* 16:9 Aspect Ratio (divide 9 by 16 = 0.5625) */
                }

                /* Then style the iframe to fit in the container div with full height and width */
                .responsive-iframe {
                    position: absolute !important;
                    top: 0 !important;
                    left: 0 !important;
                    bottom: 0 !important;
                    right: 0 !important;
                    width: 100% !important;
                    height: 100% !important;
                }
            }
            .responsive-iframe{
                height: 1000px;
                width: 1200px;
            }
        </style>
        <p>
        <div class="containerIframe">
            <iframe class="responsive-iframe" style="" src="books/index.php?location=<?php echo trim($_GET['res'])?>&amp;res=bbfdcc" seamless="seamless" scrolling="no" frameborder="0" allowtransparency="true" allowfullscreen="true"></iframe>
        </div>
        </p>
        <?php
    }
    ///////////////////////////////////////////
    ///////////Add new Resources//////////////
    /////////////////////////////////////////
    public function addNewResource(){
    ?>
        <?php if(isset($_GET['message'])){?>
            <div class="card mb-4 py-3 border-left-success" style="padding-top:0px !important;padding-bottom:0px !important; ">
                <div class="card-body" id="msg">
                    New Resource is added successfully!
                </div>
            </div>
        <?php }
        ?>
        <!-- Page Heading -->
        <h1 class="h3 mb-4 text-gray-800" style="text-align: center; padding-top: 30px;">Add New Resources</h1>
        <div class="row">
            <div class="col-md-6">
                <div class="jumbotron bg-gray-200 border-bottom-success">
                    <h3 class="display-6">Upload File</h3>
                    <form id="addFile" action="lib.php" method="post" enctype="multipart/form-data">
                        <input type="text" class="form-control" name="page" value="addNewResource" style="display:none;">
                        <input type="text" class="form-control" name="type" value="file" style="display:none;">
                        <div class="form-group">
                            <label for="exampleInputEmail1" style="display: inline-block;
    margin-bottom: 0.5rem;box-sizing: border-box;">Resource Title</label>
                            <input type="text" class="form-control" id="fileTitle" placeholder="Enter title" name="resTitle">
                            <small id="emailHelp" class="form-text text-muted">This title will be shown to all of the users</small>
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">Version</label>
                            <input type="text" class="form-control" id="fileTitle" placeholder="Enter Version e.g. v1.0, v2.1" name="version">
                        </div>

                        <div class="form-group">

                            <select name="category" class="dropdown mb-4 btn btn-primary dropdown-toggle">

                                <option>Select the Category</option>
                                <option value="FDC Compliance">FDC Compliance</option>
                                <option value="Frameworks">Frameworks</option>
                                <option value="Educational Resources">Educational Resources</option>
                                <option value="Safety Data">Safety Data</option>
                                <option value="Newsletters">Newsletters</option>
                                <option value="Fact Sheets">Fact Sheets</option>
                                <option value="FDC Insurance">FDC Insurance</option>
                                <option value="Child Safe Standards">Child Safe Standards</option>
                                <option value="Reportable Conduct Scheme">Reportable Conduct Scheme</option>
                                <option value="Online Safety">Online Safety</option>
                                <option value="General and Legal Forms">General and Legal Forms</option>
                                <option value="COVID-19">COVID-19</option>
                                <option value="Resources in other languages">Resources in other languages</option>
                            </select>

                        </div>

                        <div class="form-group">

                            <select name="addResourceRole" class="dropdown mb-4 btn btn-primary dropdown-toggle">

                                <option>Who can view the resource?</option>
                                <option value="2">Educator only</option>
                                <option value="3">Parents only</option>
                                <option value="23">Both</option>
                            </select>

                        </div>

                        <div class="form-group">
                            <label for="exampleFormControlFile1">Upload Resource</label>
                            <input type="file" class="form-control-file" id="exampleFormControlFile1" name="resource">
                            <small id="emailHelp" class="form-text text-muted">Make sure to upload only PDF file. System will not accpet any file other than .pdf</small>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-success mb-2 btn-lg" class=".bg-gradient-success">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-md-6">
                <div class="jumbotron bg-gray-200 border-bottom-success">
                    <h3 class="display-6">Add Resource Link</h3>
                    <form id="addResource" action="lib.php" method="post">
                        <input type="text" class="form-control" name="page" value="addNewResource" style="display:none;">
                        <input type="text" class="form-control" name="type" value="link" style="display:none;">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Resource Title</label>
                            <input type="text" class="form-control" placeholder="Enter title" name="resTitle">
                            <small id="emailHelp" class="form-text text-muted">e.g. article,news, blog post etc. This type will be shown to all of the users</small>
                        </div>
                        <div class="form-group">
                            <label for="exampleFormControlFile1">Link</label>
                            <input type="text" class="form-control" id="fileTitle" placeholder="https://www.brightbeginningsfdcc.com.au/" name="resource">
                            <small id="emailHelp" class="form-text text-muted">You can add link here</small>
                        </div>
                        <div class="form-group">

                            <select name="category" class="dropdown mb-4 btn btn-primary dropdown-toggle">

                                <option>Select the Category</option>
                                <option value="FDC Compliance">FDC Compliance</option>
                                <option value="Frameworks">Frameworks</option>
                                <option value="Educational Resources">Educational Resources</option>
                                <option value="Safety Data">Safety Data</option>
                                <option value="Newsletters">Newsletters</option>
                                <option value="Fact Sheets">Fact Sheets</option>
                                <option value="FDC Insurance">FDC Insurance</option>
                                <option value="Child Safe Standards">Child Safe Standards</option>
                                <option value="Reportable Conduct Scheme">Reportable Conduct Scheme</option>
                                <option value="Online Safety">Online Safety</option>
                                <option value="General and Legal Forms">General and Legal Forms</option>
                                <option value="COVID-19">COVID-19</option>
                                <option value="Resources in other languages">Resources in other languages</option>
                            </select>

                        </div>
                        <div class="form-group">

                            <select name="addResourceRole" class="dropdown mb-4 btn btn-primary dropdown-toggle">

                                <option>Who can view the resource?</option>
                                <option value="2">Educator only</option>
                                <option value="3">Parents only</option>
                                <option value="23">Both</option>
                            </select>

                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-success mb-2 btn-lg" class=".bg-gradient-success
">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
<?php
    }
    ///////////////////////////////////////////
    //////////Edit or delete resources //////////////
    /////////////////////////////////////////
    public function editordeleteResource(){
    global $pdo;
        try {
            $query = "SELECT * FROM `Resources` ORDER BY `Resources`.`rid` DESC";
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $row   = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $resCount=count($row);
            //var_dump($row);
        } catch (PDOException $e) {
            echo "Error : ".$e->getMessage();
        }
if(isset($_GET['status']) && $_GET['status']=="deleted"){?>
    <div class="card mb-4 py-3 border-left-danger" style="padding-top:0px !important;padding-bottom:0px !important; ">
        <div class="card-body" id="msg">
            Resource is deleted!
        </div>
    </div>
<?php }elseif(isset($_GET['status'])&& $_GET['status']=="edited"){?>
            <div class="card mb-4 py-3 border-left-success" style="padding-top:0px !important;padding-bottom:0px !important; ">
                <div class="card-body" id="msg">
                    Resource is edited successfully!
                </div>
            </div>
        <?php }
?>
<h1 class="h3 mb-4 text-gray-800" style="text-align: center; padding-top: 30px;">Change Resources</h1>

<!-- Page Heading -->
<div class="row">
    <div class="col-sm-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">You can edit,update and delete resources through this panel</h6>
            </div>
            <div class="card-body border-bottom-success">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                        <tr>
                            <th style="">Id</th>
                            <th>Type</th>
                            <th>Title</th>
                            <th>Version</th>
                            <th>Category</th>
                            <th>Permission</th>
                            <th>File Name/Link</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tfoot>
                        <tr>
                            <th style="">Id</th>
                            <th>Type</th>
                            <th>Title</th>
                            <th>Version</th>
                            <th>Category</th>
                            <th>Permission</th>
                            <th>File Name/Link</th>
                            <th>Action</th>
                        </tr>
                        </tfoot>
                        <tbody>
 <?php
                        for ($i=0; $i <$resCount ; $i++) {
                            ?>
                            <tr>
                                <td style=""><?php echo $row[$i]["rid"];?></td>
                                <td><?php echo $row[$i]["type"];?></td>
                                <td><?php echo $row[$i]["title"];?></td>
                                <td><?php echo $row[$i]["version"];?></td>
                                <td><?php echo $row[$i]["category"];?></td>
                                <td>
                                    <?php if($row[$i]["role"]==2){
                                        echo 'Educator';
                                    }elseif ($row[$i]["role"]==3){
                                        echo 'Parent';
                                    }elseif ($row[$i]["role"]==23){
                                        echo 'Educators and parents';
                                    }else{
                                        echo 'role not assigned yet';
                                    }?>

                                </td>
                                <td><?php echo $row[$i]["source"];?></td>
                                <td>
                                    <!--look in lib file for implementation of if isset($_GET['action']=='editResouce'){}-->
                                    <a href="?page=editingResource&id=<?php echo $row[$i]["rid"];?>" class="btn btn-primary btn-circle btn-md">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <a href="javascript:void(0);" onclick="confirmDelete(<?php echo $row[$i]['rid']; ?>)" class="btn btn-danger btn-circle btn-md">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php }?>

                        </tbody>
                    </table>
                    <script>
                        function confirmDelete(sourceID) {
                            Swal.fire({
                                title: 'Are you sure?',
                                text: "You won't be able to revert this!",
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#d33',
                                cancelButtonColor: '#3085d6',
                                confirmButtonText: 'Yes, delete it!'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    // Redirect to the deletion URL
                                    window.location.href = `?page=deleteAResource&id=${sourceID}`;
                                }
                            });
                        }
                    </script>
                </div>
            </div>
        </div>
    </div>

</div>
        <?php

    }
    ///////////////////////////////////
    /// edit a resouce///////////
    /// ///////////////////////////
    function editAResource(){
        global $pdo;
        if(isset($_GET['message'])){?>
            <div class="card mb-4 py-3 border-left-success" style="padding-top:0px !important;padding-bottom:0px !important; ">
                <div class="card-body" id="msg">
                    Resource is edited successfully!
                </div>
            </div>
        <?php }

        $resId=$_GET['id'];
        try {
            $query = "select * from `Resources` where `rid`=:Id";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam('Id', $resId, PDO::PARAM_STR);
            $stmt->execute();
            $row   = $stmt->fetch(PDO::FETCH_ASSOC);
            //var_dump($row);
        } catch (PDOException $e) {
            echo "Error : ".$e->getMessage();
        }
        ?>
        <script type="text/javascript" src="js/jquery-3.6.0.min.js"></script>
        <script>
            $(document).ready(function() {
                $('#keepIt').click(function(e) {
                    $("#keepIt").css('display','none');
                    $("#deleteNowRes").css('display','none');
                    $("#keepItButtons").append('<p id="keepItText">Your current file will be kept. If you want to change then <span id="clickButton" onClick="ButtonClick()" style="color:blue;cursor:pointer;">click here</span></p>');
                });
                $('#deleteNowRes').click(function(e) {
                    $("#fileChange").attr('value', '1');
                    $("#uploadResource").css('display','block');
                    $("#keepIt").css('display','none');
                    $("#deleteNowRes").css('display','none');
                    $("#keepItButtons").append('<p id="getMyResBack">Change of mind, Want resource back? <span id="getMyResBackBut" onClick="getMyResBack()" style="color:blue;cursor:pointer;">click here</span></p>');

                });
            });
            function ButtonClick(){
                $("#fileChange").attr('value', '1');
                $('#keepItText').remove();
                $("#getMyResBack").remove();
                $("#uploadResource").css('display','block');
                $("#keepItButtons").append('<p id="getMyResBack">Change of mind, Want resource back? <span id="getMyResBackBut" onClick="getMyResBack()" style="color:blue;cursor:pointer;">click here</span></p>');
            }
            function getMyResBack(){
                $("#fileChange").attr('value', '0');
                $('#getMyResBack').css('display','none');
                $("#uploadResource").css('display','none');
                $("keepItText").remove();
                $("#keepItButtons").append('<p id="keepItText">Your current file will be kept. If you want to change then <span id="clickButton" onClick="ButtonClick()" style="color:blue;cursor:pointer;">click here</span></p>');
            }
        </script>
        <h1 class="h3 mb-4 text-gray-800" style="text-align: center; padding-top: 30px;">Edit Resource</h1>
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <div class="jumbotron bg-gray-200 border-bottom-success">
                    <h3 class="display-6" style="font-size:18px !important;"><span style="color:red;">Caution:</span> <p style="font-size:16px !important;">Any change made on file will result in permanent deletion.Also, System will keep current resoruce if you will not choose any of the file options.</p></h3>
                    <form id="addFile" action="lib.php" method="post" enctype="multipart/form-data">
                        <input type="text" class="form-control" name="page" value="updateResource" style="display:none;">
                        <input type="text" class="form-control" name="id" value="<?php echo $row["rid"];?>" style="display:none;">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Resource Title</label>
                            <input type="text" class="form-control" id="fileTitle" placeholder="Enter title" name="resTitle" value="<?php echo $row["title"];?>">
                            <small id="emailHelp" class="form-text text-muted">This title will be shown to all of the users</small>
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">Version</label>
                            <input type="text" class="form-control" id="fileTitle" placeholder="Enter Version e.g. v1.0, v2.1" name="version" value="<?php echo $row["version"];?>">
                        </div>

                        <div class="form-group">

                            <select name="category" class="dropdown mb-4 btn btn-primary dropdown-toggle">

                                <option>Select the Category</option>
                                <option value="FDC Compliance">FDC Compliance</option>
                                <option value="Frameworks">Frameworks</option>
                                <option value="Educational Resources">Educational Resources</option>
                                <option value="Safety Data">Safety Data</option>
                                <option value="Newsletters">Newsletters</option>
                                <option value="Fact Sheets">Fact Sheets</option>
                                <option value="FDC Insurance">FDC Insurance</option>
                                <option value="Child Safe Standards">Child Safe Standards</option>
                                <option value="Reportable Conduct Scheme">Reportable Conduct Scheme</option>
                                <option value="Online Safety">Online Safety</option>
                                <option value="General and Legal Forms">General and Legal Forms</option>
                                <option value="COVID-19">COVID-19</option>
                                <option value="Resources in other languages">Resources in other languages</option>
                            </select>
                            <div>
                                <?php echo "<span><b>Currently Selected Category:</b> </span> ".$row["category"];?>
                            </div>
                        </div>

                        <div class="form-group">

                            <select name="addResourceRole" class="dropdown mb-4 btn btn-primary dropdown-toggle">

                                <option>Who can view the resource?</option>
                                <option value="2">Educator only</option>
                                <option value="3">Parents only</option>
                                <option value="23">Both</option>
                            </select>
                            <div>
                            <?php echo "<span><b>Currently Selected Role:</b> </span> ";
                            if($row["role"]==2){
                                echo 'Educator';
                            }elseif ($row["role"]==3){
                                echo 'Parent';
                            }elseif ($row["role"]==23){
                                echo 'Educators and parents';
                            }else{
                                echo 'role not assigned yet';
                            }?>
                            </div>
                        </div>

                        <div class="form-group" id="keepItButtons">
                            <div class="btn btn-success btn-icon-split" id="keepIt">
                                            <span class="icon text-white-50">
                                                <i class="fas fa-check"></i>
                                            </span>
                                <span class="text">Keep Current Resource</span>
                            </div>
                            <div class="btn btn-danger btn-icon-split" id="deleteNowRes">
                                            <span class="icon text-white-50">
                                                <i class="fas fa-trash"></i>
                                            </span>
                                <span class="text">Add New Resource Instead</span>
                            </div>
                        </div>

                        <input type="text" class="form-control-file" id="fileChange" name="fileChanged" value="0" style="display:none;">
                        <div class="form-group" id="keepResource" style=" display:none;">
                            <input type="text" class="form-control-file" id="exampleFormControlFile1" name="keepResource" value="<?php echo $row["source"];?>">
                        </div>
                        <div class="form-group" id="uploadResource" style="display:none;">
                            <label for="exampleFormControlFile1">Upload Resource</label>
                            <input type="file" class="form-control-file" id="exampleFormControlFile1" name="resource">
                            <small id="emailHelp" class="form-text text-muted">Make sure to upload only PDF file. System will not accpet any file other than .pdf</small>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-success mb-2 btn-lg" class=".bg-gradient-success">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php
    }

    ///////////////////////////////////
    /// delete resouce///////////
    /// ///////////////////////////
    public function deleteAResource($resId){
        global $pdo;
        try {
            $query = "SELECT * FROM `Resources` where `rid`=:rId";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam('rId', $resId, PDO::PARAM_STR);
            $stmt->execute();
            $row   = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error : ".$e->getMessage();
        }
        if($row["type"]=="file"){
            $file=$row["source"];
            unlink("books/".$file);
        }

        try {
            $sql = "Delete from `Resources` WHERE rid=?";
            $stmt= $pdo->prepare($sql);
            $stmt->execute([$resId]);

        } catch (PDOException $e) {
            echo "Error : ".$e->getMessage();
        }
        $URL="?page=deleteResources&status=deleted";
        echo "<script type='text/javascript'>document.location.href='{$URL}';</script>";
        echo '<META HTTP-EQUIV="refresh" content="0;URL=' . $URL . '">';
    }

}
/*****************************************
* Meeting Class
 * ****************************************
 */
class Meeting{
    ///////////////////////////////////
    /// View all meetings///////////
    /// ///////////////////////////
    public function viewAllMeetings(){
        global $pdo;
        try {
            $query = "select * from `meetings` where `type`='meeting'";
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $row   = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error : ".$e->getMessage();
        }
        $lastIndex= count($row);
        $currentDate= date("Y-m-d");
        date_default_timezone_set('Australia/Melbourne');
        $melbourne=date_default_timezone_get();
        $time = date('h:i a', time());
        if(isset($_GET['message'])){?>
            <div class="card mb-4 py-3 border-left-success" style="padding-top:0px !important;padding-bottom:0px !important; ">
                <div class="card-body" style="color:#1cc88a" id="msg">
                    New meeting is added successfully!
                </div>
            </div>
        <?php }
        ?>
        <h1 class="h3 mb-4 text-gray-800">Coming Meetings</h1>
        <div class="row">
            <?php
            $countCommingMeetings=0;
            for ($i=0; $i <$lastIndex ; $i++) {
                $time12format=date('h:i a', strtotime($row[$i]["time"]));
                if($row[$i]["date"]>=$currentDate){
                    $convertToDate=strtotime($row[$i]["date"]);
                    $currentRecordDateFormation=date("d-m-Y", $convertToDate);
                    $countCommingMeetings++;
                    $origin = new DateTime($currentDate);
                    $target = new DateTime($currentRecordDateFormation);

                    if($row[$i]["date"]==$currentDate && $time12format>$time){
                        ?>
                        <div class="col-xl-3 col-md-6 mb-4 commingMeetingCard" id="commingMeetingCard<?php echo $row[$i]["id"];?>">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" style="color:red !important;">Today</div>
                                            <div style="display: none;"><?php echo $row[$i]["id"];?></div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo date("D,d M Y",$convertToDate);?></div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo  $time12format;?></div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" style="padding-top: 20px;">
                                                <a href="viewMeetingDetails.php?id=<?php echo $row[$i]["id"];?>" class="btn btn-primary">
                                                    <span class="text">View Details</span>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                    }else if($row[$i]["date"]>$currentDate){
                        ?>
                        <div class="col-xl-3 col-md-6 mb-4 commingMeetingCard" id="commingMeetingCard<?php echo $row[$i]["id"];?>">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" style="color:red !important;"><?php
                                                $interval = $origin->diff($target);
                                                echo $interval->format("%a days left"); ?></div>
                                            <div style="display: none;"><?php echo $row[$i]["id"];?></div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo date("D,d M Y",$convertToDate);?></div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo  $time12format;?></div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" style="padding-top: 20px;">
                                                <a href="?page=viewMeetingDetails&id=<?php echo $row[$i]["id"];?>" class="btn btn-primary">
                                                    <span class="text">View Details</span>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                }
            }
            if($countCommingMeetings==0){
                ?>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card mb-4 py-3 border-left-danger">
                        <div class="card-body">
                            No Upcoming Meetings Found!
                        </div>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
        <h1 class="h3 mb-4 text-gray-800">Past Meetings</h1>
        <div class="row">
            <?php
            $countPastMeetings=0;
            for ($i=0; $i <$lastIndex ; $i++) {
                $time12format=date('h:i a', strtotime($row[$i]["time"]));
                if($row[$i]["date"]<=$currentDate){
                    $convertToDate=strtotime($row[$i]["date"]);
                    $currentRecordDateFormation=date("d-m-Y", $convertToDate);
                    $countPastMeetings++;
                    $origin = new DateTime($currentDate);
                    $target = new DateTime($currentRecordDateFormation);
                    if($row[$i]["date"]==$currentDate && $time12format<$time){
                        ?>
                        <div class="col-xl-3 col-md-6 mb-4 pastMeetingCard" id="pastMeetingCard<?php echo $row[$i]["id"];?>">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div style="display: none;"><?php echo $row[$i]["id"];?></div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" style="color:green !important;"><?php   $now = time(); // or your date as well
                                                $your_date = $convertToDate;
                                                $datediff = $now - $your_date; echo round($datediff / (60 * 60 * 24))." days ago"; ?></div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo date("D,d M Y",$convertToDate);?></div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo  $time12format;?></div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" style="padding-top: 20px;">
                                                <a href="viewMeetingDetails.php?id=<?php echo $row[$i]["id"];?>" class="btn btn-primary">
                                                    <span class="text">View Details</span>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                    }else if($row[$i]["date"]<$currentDate){
                        ?>
                        <div class="col-xl-3 col-md-6 mb-4 pastMeetingCard" id="pastMeetingCard<?php echo $row[$i]["id"];?>">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div style="display: none;"><?php echo $row[$i]["id"];?></div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" style="color:green !important;"><?php   $now = time(); // or your date as well
                                                $your_date = $convertToDate;
                                                $datediff = $now - $your_date; echo round($datediff / (60 * 60 * 24))." days ago"; ?></div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo date("D,d M Y",$convertToDate);?></div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo  $time12format;?></div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" style="padding-top: 20px;">
                                                <a href="?page=viewMeetingDetails&id=<?php echo $row[$i]["id"];?>" class="btn btn-primary">
                                                    <span class="text">View Details</span>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                }
            }
            if($countPastMeetings==0){
                ?>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card mb-4 py-3 border-left-danger">
                        <div class="card-body">
                            No Past Meetings Found!
                        </div>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
        <?php
    }
    ///////////////////////////////////
    /// View meeting Details///////////
    /// ///////////////////////////
    public function viewMeetingDetails($mtid){
        global $pdo;
        $meetingId=$mtid;
        try {
            $query = "SELECT * FROM `meetings` where `id`=:meetingId";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam('meetingId', $meetingId, PDO::PARAM_STR);
            $stmt->execute();
            $row   = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error : ".$e->getMessage();
        }
        if (!empty($row['files']) && !empty($row['serializedLinks'])){
            $files=unserialize($row["files"]);
            $titles=unserialize($row["titles"]);
            $links=unserialize($row["serializedLinks"]);
            $linksTitle=unserialize($row["linksTitle"]);
    
            $countLinks=count((is_countable($links)?$links:[]));
            $countFiles=count($files);
        }elseif (!empty($row['files'])){
            $files=unserialize($row["files"]);
            $titles=unserialize($row["titles"]);
            $countFiles=count($files);
        }elseif(!empty($row['serializedLinks'])){
            $links=unserialize($row["serializedLinks"]);
            $linksTitle=unserialize($row["linksTitle"]);
            $countLinks=count((is_countable($links)?$links:[]));
        }
        
        

        $time12format=date('h:i a', strtotime($row["time"]));
        $convertToDate=strtotime($row["date"]);
        $currentRecordDateFormation=date("d-m-Y", $convertToDate);
        ?>
        <!-- Page Heading -->
        <h1 class="h3 mb-4 text-gray-800">Meeting Details</h1>
        <div class="row">

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <a href="#" class="btn btn-primary btn-circle btn-lg" style="padding:40px;font-size: 30px;">
                                    <i class="fas fa-calendar"></i>
                                </a>
                            </div>
                            <div class="col-auto">
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo date("l,d M Y",$convertToDate);?></div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo  strtoupper($time12format);?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-12 col-md-12 mb-12">
                <div class="card shadow h-100 py-2">
                    <div class="card-body">
                        <h3>Agenda:</h3>
                        <p>
                            <?php echo $row["agenda"];?>
                        </p>

                        <h3>Resources:</h3>
                        <?php
                       // var_dump($files);
                        $lastIndex = array_key_last($files);
                        //echo" This is:".$lastIndex;
                        //var_dump($last);
                        //echo"<br/>";
                        
                        if(isset($links)&&isset($links)){
                           
                            $linkTitleCount=1;
                            $countFiles=0;
                            for ($j=1; $j <=$lastIndex+1 ; $j++) {
                                if(isset($files[$j])){
                                    // echo $files[$j];
                                    // echo'<br>';
                                ?>
                                <a href="?page=viewResource&res=<?php echo $files[$j];?>&mtid=<?php echo $meetingId;?>" target="_blank"><i class="fa fa-file"></i><span style="color: black"> &nbsp;<?php echo $titles[$j]; ?></span></a><br>
                                <?php
                                }
                                
                            }
                            foreach($links as $key=>$value ) {
                                if($value!=NULL){
                                ?>
                                    <a href="<?php echo $value?>" target="_blank"><i class="fa fa-link"></i><span style="color: black"> &nbsp; <?php echo $linksTitle[$linkTitleCount]; ?></span></a>
                                    <br>
                                <?php
                                }
                                $linkTitleCount++;
                            }
                            
                        }
                        elseif(isset($links)){
                            $linkTitleCount=1;
                            foreach($links as $key=>$value ) {
                                ?>
                                <a href="<?php echo $value?>" target="_blank"><i class="fa fa-link"></i><span style="color: black"> &nbsp; <?php echo $linksTitle[$linkTitleCount]; ?></span></a>
                                <br>
                                <?php
                                $linkTitleCount++;
                            }
                        }
                        
                        elseif(isset($files)){
                            for ($j=1; $j <$countFiles+1 ; $j++) {
                                ?>
                                <a href="?page=viewResource&res=<?php echo $files[$j];?>&mtid=<?php echo $meetingId;?>" target="_blank"><i class="fa fa-file"></i><span style="color: black"> &nbsp;<?php echo $titles[$j]; ?></span></a><br>
                                <?php
                            }
                        }
                        ?>
                    </div>
                </div>

            </div>
        </div>
        <script>
            // Set the date we're counting down to
            var countDownDate = new Date("March 29, 2021 09:37:25").getTime();

            // Update the count down every 1 second
            var x = setInterval(function() {

                // Get today's date and time
                var now = new Date().getTime();

                // Find the distance between now and the count down date
                var distance = countDownDate - now;

                // Time calculations for days, hours, minutes and seconds
                var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                var seconds = Math.floor((distance % (1000 * 60)) / 1000);

                // Display the result in the element with id="demo"
                document.getElementById("demo").innerHTML = days + "d " + hours + "h "
                    + minutes + "m " + seconds + "s ";

                // If the count down is finished, write some text
                if (distance < 0) {
                    clearInterval(x);
                    document.getElementById("demo").innerHTML = "EXPIRED";
                }
            }, 1000);
        </script>
        <?php
    }

    ///////////////////////////////////
    /// Schedule meeting///////////
    /// ///////////////////////////

    public function scheduleMeeting(){?>
        <script>
            $(document).ready(function(){
                // Submit form data via Ajax
                $("#ANR").click(function() {
                    var counter=$('#counter').val();
                    var deleteResource= '<br><br/><div class="deleteResource btn btn-danger btn-circle btn-sm" id="deleteid'+counter+'" style="cursor:pointer;"><i class="fas fa-trash"></i></div>';
                    var label = '<div class="card-header py-3"><label class="m-0 font-weight-bold text-primary" for="exampleTextarea">File '+counter+' <span style="color:red;">* only pdf should be uploaded</span></label><br/></div>';
                    var file = '<br/><input type="file"  id="file'+counter+'" name="file'+counter+'"  accept="application/pdf" required style="display:block;">'+deleteResource+'</div>';
                    var inputFileTitle='<div class="card-body"><input type="text" class="form-control form-control-user" id="resource'+counter+'name" placeholder="Enter title of the resource" name="resname'+counter+'" required>'+file+'</div>';

                    var section = '<div class="form-group card-header py-3" id="sectionid'+counter+'" style="margin-top:10px !important;margin-bottom:10px !important;">'+label+ inputFileTitle+'</div>';
                    $(".uploadResource").append(section);
                    counter++;
                    $('#counter').val(counter);
                    $(".deleteResource").click(function() {
                        var currentid=$(this).attr("id");
                        var suffix = currentid.replace('deleteid',''); // 123456
                        //alert("#"+currentid);
                        $("#sectionid"+suffix).remove();
                        $("#"+currentid).remove();
                    });
                });

                $("#ANL").click(function() {
                    var linkCounter=$('#linkCounter').val();
                    var label = '<div class="card-header py-3"><label class="m-0 font-weight-bold text-primary" for="exampleTextarea">Link'+linkCounter+'</label></div>';
                    var deleteLink= ' <br><div class="deleteLink btn btn-danger btn-circle btn-sm" id="deletelinkid'+linkCounter+'" style="cursor:pointer;"><i class="fas fa-trash"></i></div>';
                    var link='<br><input type="text" class="form-control form-control-user" id="link'+linkCounter+'" placeholder="https://www.google.com/" name="link'+linkCounter+'" required> <label style="color:red !important;">Please make sure to add complete URL with http/https. Otherwise it will not work properly</label>';
                    var linkTitle='<div class="card-body"><input type="text" class="form-control form-control-user" id="linkTitle'+linkCounter+'name" placeholder="Enter title of the link" name="linkTitle'+linkCounter+'" required>'+link+deleteLink+'</div>';
                    var linksection = '<div class="form-group card-header py-3" id="linksectionid'+linkCounter+'" style="margin-top:10px !important;margin-bottom:10px !important;">'+label+ linkTitle+'</div>';
                    $(".addLinks").append(linksection);
                    linkCounter++;
                    $('#linkCounter').val(linkCounter);
                    $(".deleteLink").click(function() {
                        var currentlinkid=$(this).attr("id");
                        var suffix = currentlinkid.replace('deletelinkid',''); // 123456
                        //alert("#"+currentlinkid);
                        $("#linksectionid"+suffix).remove();
                        $("#"+currentlinkid).remove();
                    });
                });


                $("#a").on('submit', function(e){
                    
                    e.preventDefault();
                    //console.log(new FormData(this));
                    $.ajax({
                        type: 'POST',
                        url: 'lib.php',
                        data: new FormData(this),
                        dataType: 'json',
                        contentType: false,
                        cache: false,
                        processData:false,
                        beforeSend: function(){
                            $('.submitBtn').attr("disabled","disabled");
                            $('#scheduleMeeting').css("opacity",".5");
                        },
                        success: function(response){
                            $('.statusMsg').html('');
                            if(response.status == 1){
                                $('#scheduleMeeting')[0].reset();
                                $('.statusMsg').html('<p class="alert alert-success">'+response.message+'</p>');
                            }else{
                                $('.statusMsg').html('<p class="alert alert-danger">'+response.message+'</p>');
                            }
                            $('#scheduleMeeting').css("opacity","");
                            $(".submitBtn").removeAttr("disabled");
                        }
                    });
                });

                // File type validation
                var match = ['application/pdf'];
                $("#file").change(function() {
                    for(i=0;i<this.files.length;i++){
                        var file = this.files[i];
                        var fileType = file.type;

                        if(!(fileType == match[0])){
                            alert('Sorry, only PDF are allowed to upload.');
                            $("#file").val('');
                            return false;
                        }
                    }
                });
            });
        </script>
        <!-- Page Heading -->
        <h1 class="h3 mb-4 text-gray-800" style="text-align: center; padding-top: 30px;">Add New Meeting</h1>
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <div class="jumbotron bg-gray-200 border-bottom-success">
                    <form id="scheduleMeeting" action="lib.php" method="post" enctype="multipart/form-data">

                        <div class="form-group">
                            <label for="exampleInputEmail1">Name</label>
                            <input type="text" class="form-control" id="name" placeholder="Enter Name" name="name">
                        </div>
                        <div class="form-group row">
                            <label for="example-date-input" class="col-2 col-form-label">Date</label>
                            <div class="col-10">
                                <input class="form-control" type="date" value="" id="example-date-input" name="date" required>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="example-time-input" class="col-2 col-form-label">Time</label>
                            <div class="col-10">
                                <input class="form-control" type="time" value="" id="example-time-input" name="time" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="exampleTextarea">Agenda</label>
                            <textarea class="form-control" id="exampleTextarea" rows="3" name="agenda" required></textarea>
                            <label> Use these tips to add your text to make it look better on other users end.<br>
                                <b>Tips:</b> (use black code as example.You can copy paste into above box)
                                <ul>
                                    <li>To end the line use <span style="background:black;color:white;">&lt;br&gt;</span> at the end of end line/para. </li>
                                    <li>To make a clickable link use <span style="background:black;color:white;"> &lt;a href="yourlinkhere.com"&gt;You link title&lt;/a&gt;</span>. You have to add this to end line/para. </li>
                                    <li>Headings: you can use <a href="https://www.w3schools.com/html/tryit.asp?filename=tryhtml_headings" target="_blank">headings</a> 1-6 depending on size. Sample code:<span style="background:black;color:white;"> &lt;h1&gt;You Heading&lt;/h1&gt;</span>. You can replace h1-h6 depending upon size of your heading.  </li>
                                    <li>To make text bold use <span style="background:black;color:white;"> &lt;b&gt;Text needs to be bold&lt;/b&gt;</span>. </li>
                                    <li>For more information about what else you can do, please visit: <a href="https://www.w3schools.com/html/default.asp" target="_blank">W3School.com</a> </li>
                                </ul>
                            </label
                        </div>

                        <!--tst-->
                        <div class="form-group">

                            <input type="text"  id="counter" value="1" style="display:none;" name="counter">
                            <div class="uploadResource">
                                <!--Put them all here-->
                            </div>
                            <!--////tst-->


                            <h3 class="display-6 btn btn-info btn-icon-split" id="ANR" style="cursor:pointer;">
                                        <span class="icon text-white-50">
                                            <i class="fas fa fa-plus"></i>
                                        </span>
                                <span class="text">Add New Resources</span>
                            </h3>
                        </div>
                        <!--LINKSsssssssssssssssssssssssssssssssssssssssss-->
                        <div class="form-group">

                            <input type="text"  id="linkCounter" value="1" style="display:none;" name="linkCounter">
                            <div class="addLinks">
                                <!--Put them all here-->
                            </div>

                            <h3 class="display-6 btn btn-info btn-icon-split" id="ANL" style="cursor:pointer;">
                                        <span class="icon text-white-50">
                                            <i class="fas fa fa-plus"></i>
                                        </span>
                                <span class="text">Add New Link</span>
                            </h3>
                        </div>
                        <!--LINKssssssssssssssssssssssssssssssssssssssssssss-->

                        <div class="col-auto">
                            <button type="submit" class="btn btn-success mb-2 btn-lg submitBtn" class=".bg-gradient-success">Submit</button>
                        </div>
                        <div class="col-auto">
                            <div class="statusMsg"></div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
        <?php
    }
    ///////////////////////////////////////////
    //////////Edit or delete resources //////////////
    /////////////////////////////////////////
    public function editordeleteMeeting(){
        if(isset($_GET['status']) && $_GET['status']=="deleted"){?>
            <div class="card mb-4 py-3 border-left-danger" style="padding-top:0px !important;padding-bottom:0px !important; ">
                <div class="card-body" id="msg">
                    Meeting is deleted!
                </div>
            </div>
        <?php }elseif(isset($_GET['status'])&& $_GET['status']=="edited"){?>
            <div class="card mb-4 py-3 border-left-success" style="padding-top:0px !important;padding-bottom:0px !important; ">
                <div class="card-body" id="msg">
                    Meeting is edited successfully!
                </div>
            </div>
        <?php }
        global $pdo;
        $mtype="meeting";
        try {
            $query = "SELECT * FROM `meetings` where `type`=:mtype";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam('mtype', $mtype, PDO::PARAM_STR);
            $stmt->execute();
            $row   = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error : ".$e->getMessage();
        }

        ?>
        <h1 class="h3 mb-4 text-gray-800" style="text-align: center; padding-top: 30px;">Change Meetings Details</h1>
        <!-- Page Heading -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">You can edit,update and delete meetings through this panel</h6>
                    </div>
                    <div class="card-body border-bottom-success">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                <tr>
                                    <th style="display:none;">Id</th>
                                    <th>Date</th>
                                    <th>Day</th>
                                    <th>Time</th>
                                    <th>Name</th>
                                    <th>Agenda</th>
                                    <th>Resources</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tfoot>
                                <tr>
                                    <th style="display:none;">Id</th>
                                    <th>Date</th>
                                    <th>Day</th>
                                    <th>Time</th>
                                    <th>Name</th>
                                    <th>Agenda</th>
                                    <th>Resources</th>
                                    <th>Action</th>
                                </tr>
                                </tfoot>
                                <tbody>
                                <?php
                                foreach ($row as $data)
                                {
                                    ?>
                                    <tr>
                                        <td style="display:none;"><?php echo $data["id"];?></td>
                                        <td><?php $convertToDate=strtotime($data["date"]); echo date("l",$convertToDate);?></td>
                                        <td><?php $convertToDate=strtotime($data["date"]); echo date("d M Y",$convertToDate);?></td>
                                        <td><?php $time12format=date('h:i a', strtotime($data["time"])); echo  strtoupper($time12format);?></td>
                                        <td>
                                            <?php echo $data["name"];?>
                                        </td>
                                        <td>
                                            <?php echo $data["agenda"];?>
                                        </td>
                                        <td>
                                            <?php
                                            $files=unserialize($data["files"]);
                                            $titles=unserialize($data["titles"]);
                                            $links=unserialize($data["serializedLinks"]);
                                            $linksTitle=unserialize($data["linksTitle"]);
                                            $countLinks=count((is_countable($links)?$links:[]));
                                            $countFiles=count((is_countable($files)?$files:[]));
                                            ?>
                                            <?php
                                            $linkTitleCount=1;
                                            if($countLinks!=0){
                                                foreach($links as $key=>$value) {
                                                    ?>
                                                    <a href="<?php echo $value?>" target="_blank"><i class="fa fa-link"></i><span style="color: black"> &nbsp; <?php echo $linksTitle[$linkTitleCount];
                                                            ?></span></a>
                                                    <br>
                                                    <?php
                                                    $linkTitleCount++;
                                                }
                                            }
                                            ?>
                                            <?php

                                            if($countFiles!=0){
                                                for ($j=1; $j <$countFiles+1 ; $j++) {
                                                    ?>
                                                    <a href="?page=viewResource&res=<?php echo $files[$j];?>&eudm=1" target="_blank"><i class="fa fa-file"></i><span style="color: black"> &nbsp;<?php echo $titles[$j]; ?></span></a><br>
                                                    <?php
                                                }
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <a href="?page=viewMeetingDetails&id=<?php echo $data["id"];?>" class="btn btn-primary btn-circle btn-md" id="edit<?php echo $data["id"];?>" target="_blank">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="?page=editMeeting&id=<?php echo $data["id"];?>" class="btn btn-primary btn-circle btn-md" id="edit<?php echo $data["id"];?>">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="javascript:void(0);" onclick="confirmDelete(<?php echo $data['id']; ?>)" class="btn btn-danger btn-circle btn-md">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php }?>
                                </tbody>
                            </table>
                            <script>
                                function confirmDelete(meetingID) {
                                    Swal.fire({
                                        title: 'Are you sure?',
                                        text: "You won't be able to revert this!",
                                        icon: 'warning',
                                        showCancelButton: true,
                                        confirmButtonColor: '#d33',
                                        cancelButtonColor: '#3085d6',
                                        confirmButtonText: 'Yes, delete it!'
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            // Redirect to the deletion URL
                                            window.location.href = `?page=deleteMeetings&id=${meetingID}`;
                                        }
                                    });
                                }
                            </script>                 


                        </div>
                    </div>
                </div>
            </div>

        </div>

        <?php

    }
    ///////////////////////////////////////////
    //////////Edit Meeting //////////////
    /////////////////////////////////////////
    public function editMeeting($meetingId){
        global $pdo;
        try {
            $query = "SELECT * FROM `meetings` where `id`=:id";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam('id', $meetingId, PDO::PARAM_STR);
            $stmt->execute();
            $row   = $stmt->fetch(PDO::FETCH_ASSOC);
            $titles=unserialize($row["titles"]);
            $files=unserialize($row["files"]);
            $links=unserialize($row["serializedLinks"]);
            $linksTitle=unserialize($row["linksTitle"]);
            $filesCount=count((is_countable($files)?$files:[]));
            $linksCount=count((is_countable($links)?$links:[]));

        } catch (PDOException $e) {
            echo "Error : ".$e->getMessage();
        }
        ?>
        <script type="text/javascript" src="js/jquery-3.6.0.min.js"></script>
        <script>
            $(document).ready(function(){
                // Submit form data via Ajax
                $("#ANR").click(function() {
                    var counter=$('#counter').val();
                    var deleteResource= '<input type="text" class="form-control form-control-user" value="1" name="newFile'+counter+'" style="display:none;" required><br><br/><div class="deleteResource btn btn-danger btn-circle btn-sm" id="deleteid'+counter+'" onClick="sectionDeleted('+counter+')" style="cursor:pointer;"><i class="fas fa-trash"></i></div>';
                    var label = '<div class="card-header py-3"><label class="m-0 font-weight-bold text-primary" for="exampleTextarea">File  <span style="color:red;">* only pdf should be uploaded</span></label><br/></div>';
                    var file = '<br/><input type="file"  id="file'+counter+'" name="file'+counter+'"  accept="application/pdf" required>'+deleteResource+'</div>';
                    var inputFileTitle='<div class="card-body"><input type="text" class="form-control form-control-user" id="resource'+counter+'name" placeholder="Enter title of the resource" name="resname'+counter+'" required>'+file+'</div>';
                    var checkForDeletion=' <input type="text" value="0" class="form-control form-control-user" id="isitdeleted'+counter+'" name="isItDeleted'+counter+'" required="" style="display:none;">';
                    var section = checkForDeletion+'<div class="form-group card-header py-3" id="sectionid'+counter+'" style="margin-top:10px !important;margin-bottom:10px !important;">'+label+ inputFileTitle+'</div>';
                    $(".uploadResource").append(section);
                    counter++;
                    $('#counter').val(counter);
                    $(".deleteResource").click(function() {
                        var currentid=$(this).attr("id");
                        var suffix = currentid.replace('deleteid',''); // 123456
                        //alert("#"+currentid);
                        $("#sectionid"+suffix).remove();
                        $("#"+currentid).remove();
                    });
                    function sectionDeleted(id){
                        $("#isitdeleted"+id).val(1);
                    }
                });
                $(".deleteResource").click(function() {
                    var currentid=$(this).attr("id");
                    var suffix = currentid.replace('deleteid','');
                    $("#sectionid"+suffix).remove();
                    $("#"+currentid).remove();
                });

                $("#ANL").click(function() {
                    var linkCounter=$('#linkCounter').val();
                    var label = '<div class="card-header py-3"><label class="m-0 font-weight-bold text-primary" for="exampleTextarea">Link</label></div>';
                    var deleteLink= ' <br><div class="deleteLink btn btn-danger btn-circle btn-sm" id="deletelinkid'+linkCounter+'" style="cursor:pointer;"><i class="fas fa-trash"></i></div>';
                    var link='<br><input type="text" class="form-control form-control-user" id="link'+linkCounter+'" placeholder="https://www.google.com/" name="link'+linkCounter+'" required> <label style="color:red !important;">Please make sure to add complete URL with http/https. Otherwise it will not work properly</label>';
                    var linkTitle='<div class="card-body"><input type="text" class="form-control form-control-user" id="linkTitle'+linkCounter+'name" placeholder="Enter title of the link" name="linkTitle'+linkCounter+'" required>'+link+deleteLink+'</div>';
                    var linksection = '<div class="form-group card-header py-3" id="linksectionid'+linkCounter+'" style="margin-top:10px !important;margin-bottom:10px !important;">'+label+ linkTitle+'</div>';
                    $(".addLinks").append(linksection);
                    linkCounter++;
                    $('#linkCounter').val(linkCounter);
                    $(".deleteLink").click(function() {
                        var currentlinkid=$(this).attr("id");
                        var suffix = currentlinkid.replace('deletelinkid',''); // 123456
                        //alert("#"+currentlinkid);
                        $("#linksectionid"+suffix).remove();
                        $("#"+currentlinkid).remove();
                    });
                });
                $(".deleteLink").click(function() {
                    var currentlinkid=$(this).attr("id");
                    var suffix = currentlinkid.replace('deletelinkid',''); // 123456
                    //alert("#"+currentlinkid);
                    $("#linksectionid"+suffix).remove();
                    $("#"+currentlinkid).remove();
                });


                // File type validation
                var match = ['application/pdf'];
                $("#file").change(function() {
                    for(i=0;i<this.files.length;i++){
                        var file = this.files[i];
                        var fileType = file.type;

                        if(!(fileType == match[0])){
                            alert('Sorry, only PDF are allowed to upload.');
                            $("#file").val('');
                            return false;
                        }
                    }
                });
            });

            function keepItClick(fullId,idcounter){
                var Id=$(fullId).attr("id");
                var idCounter=idcounter;
                $("#keepIt"+idcounter).css('display','none');
                $("#deleteNowRes"+idcounter).css('display','none');
                $("#keepItButtons"+idcounter).append('<p id="keepItText'+idcounter+'">Your current file will be kept. If you want to change then <span id="clickButton'+idcounter+'" onClick="ButtonClick('+Id+','+idCounter+')" style="color:blue;cursor:pointer;">click here</span></p>');
            }
            function ButtonClick(fullId,idcounter){
                var Id=$(fullId).attr("id");
                $("#keepIt"+idcounter).css('display','none');
                $("#deleteNowRes"+idcounter).css('display','none');
                $("#fileChange"+idcounter).attr('value', '1');
                $('#keepItText'+idcounter).remove();
                $("#getMyResBack"+idcounter).remove();
                $("#uploadResource"+idcounter).css('display','block');
                $("#keepItButtons"+idcounter).append('<p id="getMyResBack'+idcounter+'">Change of mind, Want resource back? <span id="getMyResBackBut'+idcounter+'" onClick="getMyResBack('+Id+','+idcounter+')" style="color:blue;cursor:pointer;">click here</span></p>');
            }
            function getMyResBack(fullId,idcounter){
                var Id=$(fullId).attr("id");
                $("#fileChange"+idcounter).attr('value', '0');
                $('#getMyResBack'+idcounter).css('display','none');
                $("#uploadResource"+idcounter).css('display','none');
                $("keepItText"+idcounter).remove();
                $("#keepItButtons"+idcounter).append('<p id="keepItText'+idcounter+'">Your current file will be kept. If you want to change then <span id="clickButton" onClick="ButtonClick('+Id+','+idcounter+')" style="color:blue;cursor:pointer;">click here</span></p>');
            }
            function sectionDeleted(id){
                $("#isitdeleted"+id).val(1);
            }
        </script>
        <!-- Page Heading -->
        <h1 class="h3 mb-4 text-gray-800" style="text-align: center; padding-top: 30px;">Add New Meeting</h1>
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <div class="jumbotron bg-gray-200 border-bottom-success">
                    <form id="scheduleMeeting" action="lib.php" method="post" enctype="multipart/form-data">
                        <input class="form-control" type="text" value="updateMeetingNew" id="example-date-input" name="updateMeetingNew" required style="display:none;">
                        <input class="form-control" type="text" value="<?php echo $row["id"];?>" id="example-date-input" name="meetingId" required style="display:none;">
                        <div class="form-group row">
                            <label for="example-date-input" class="col-2 col-form-label">Date</label>
                            <div class="col-10">
                                <input class="form-control" type="date" value="<?php echo $row["date"];?>" id="example-date-input" name="date" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="example-time-input" class="col-2 col-form-label">Time</label>
                            <div class="col-10">
                                <input class="form-control" type="time" value="<?php echo $row["time"];?>" id="example-time-input" name="time" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="exampleInputEmail1">Name</label>
                            <input type="text" class="form-control" id="name" placeholder="Enter Name" name="name" value="<?php echo $row["name"];?>">
                        </div>

                        <div class="form-group">
                            <label for="exampleTextarea">Agenda</label>
                            <textarea class="form-control" id="exampleTextarea" rows="3" name="agenda" required><?php echo $row["agenda"];?></textarea>
                            <label> Use these tips to add your text to make it look better on other users end.<br>
                                <b>Tips:</b> (use black code as example.You can copy paste into above box)
                                <ul>
                                    <li>To end the line use <span style="background:black;color:white;">&lt;br&gt;</span> at the end of end line/para. </li>
                                    <li>To make a clickable link use <span style="background:black;color:white;"> &lt;a href="yourlinkhere.com"&gt;You link title&lt;/a&gt;</span>. You have to add this to end line/para. </li>
                                    <li>Headings: you can use <a href="https://www.w3schools.com/html/tryit.asp?filename=tryhtml_headings" target="_blank">headings</a> 1-6 depending on size. Sample code:<span style="background:black;color:white;"> &lt;h1&gt;You Heading&lt;/h1&gt;</span>. You can replace h1-h6 depending upon size of your heading.  </li>
                                    <li>To make text bold use <span style="background:black;color:white;"> &lt;b&gt;Text needs to be bold&lt;/b&gt;</span>. </li>
                                    <li>For more information about what else you can do, please visit: <a href="https://www.w3schools.com/html/default.asp" target="_blank">W3School.com</a> </li>
                                </ul>
                            </label
                        </div>

                        <!--tst-->
                        <div class="form-group">
                            <input type="text"  id="counter" value="<?php echo $filesCount+1;?>" style="display:none;" name="counter">
                            <div class="uploadResource">
                                <!--Put them all here-->

                                <?php for ($i=1; $i <=$filesCount ; $i++) {

                                    ?>
                                    <input type="text" value="0" class="form-control form-control-user" id="isitdeleted<?php echo $i;?>" name="isItDeleted<?php echo $i;?>" required="" style="display:none;">
                                    <div class="form-group card-header py-3" id="sectionid<?php echo $i;?>" style="margin-top:10px !important;margin-bottom:10px !important;">
                                        <div class="card-header py-3"><label class="m-0 font-weight-bold text-primary" for="exampleTextarea">File <span style="color:red;">* only pdf should be uploaded</span></label><br></div>
                                        <div class="card-body">
                                            <input type="text" value="<?php echo $titles[$i];?>" class="form-control form-control-user" id="resource<?php echo $i;?>name" placeholder="Enter title of the resource" name="resname<?php echo $i;?>" required="" >
                                            <input type="text" value="1" class="form-control form-control-user" id="isOld<?php echo $i;?>" placeholder="Enter title of the resource" name="isOld<?php echo $i;?>" value="1" required="" style="display:none;">
                                            <input type="text" class="form-control-file" id="fileChange<?php echo $i;?>" name="fileChanged<?php echo $i;?>" value="0" style="display:none;">
                                            <div class="form-group" id="keepResource" style="">
                                                <input type="text" class="form-control-file" id="exampleFormControlFile1<?php echo $i;?>" name="keepResource<?php echo $i;?>" value="<?php echo $files[$i];?>" style="display:none;">
                                            </div>
                                            <input type="file" id="uploadResource<?php echo $i;?>" name="file<?php echo $i;?>" value="<?php echo $files[$i];?>" accept="application/pdf" style="display:none;">
                                            <div class="form-group" id="keepItButtons<?php echo $i;?>">
                                                <div class="btn btn-success btn-icon-split" id="keepIt<?php echo $i;?>" onclick="keepItClick(this,<?php echo $i;?>);">
                                                                <span class="icon text-white-50">
                                                                    <i class="fas fa-check"></i>
                                                                </span>
                                                    <span class="text">Keep Current Resource</span>
                                                </div>
                                                <div class="btn btn-danger btn-icon-split" id="deleteNowRes<?php echo $i;?>" onclick="ButtonClick(this,<?php echo $i;?>)">
                                                                <span class="icon text-white-50">
                                                                    <i class="fas fa-trash"></i>
                                                                </span>
                                                    <span class="text">Add New Resource Instead</span>
                                                </div>
                                            </div>
                                            <div class="deleteResource btn btn-danger btn-circle btn-sm" id="deleteid<?php echo $i;?>" style="cursor:pointer;" onClick="sectionDeleted(<?php echo $i;?>)"><i class="fas fa-trash"></i></div>
                                            <div><span style="color:red;">Note:</span> Becareful about deleting resource above. If you have delete accidently, then reload the page before saving.</div>
                                        </div>
                                    </div>

                                <?php } ?>

                            </div>
                            <!--////tst-->


                            <h3 class="display-6 btn btn-info btn-icon-split" id="ANR" style="cursor:pointer;">
                                        <span class="icon text-white-50">
                                            <i class="fas fa fa-plus"></i>
                                        </span>
                                <span class="text">Add New Resources</span>
                            </h3>
                        </div>


                        <!--LINKSsssssssssssssssssssssssssssssssssssssssss-->
                        <div class="form-group">
                            <div class="addLinks">
                                <?php $linksCounter=0;
                                //var_dump($links);
                                if (isset($links) && $links!=false){
                                    //echo 'value is set';
                                    $linkTitleCount=1;
                                    foreach($links as $key=>$value) {
                                        $linksCounter++;
                                        ?>
                                        <div class="form-group card-header py-3" id="linksectionid<?php echo $linksCounter;?>" style="margin-top:10px !important;margin-bottom:10px !important;">
                                            <div class="card-header py-3"><label class="m-0 font-weight-bold text-primary" for="exampleTextarea">Link</label></div>
                                            <div class="card-body">
                                                <input type="text" class="form-control form-control-user" id="linkTitle<?php echo $linksCounter;?>name" value="<?php echo $linksTitle[$linkTitleCount];$linkTitleCount++;?>" name="linkTitle<?php echo $linksCounter;?>" required="">
                                                <br>
                                                <input type="text" class="form-control form-control-user" value="<?php echo $value;?>" id="link<?php echo $linksCounter;?>" placeholder="https://www.google.com/" name="link<?php echo $linksCounter;?>" required="">
                                                <label style="color:red !important;">Please make sure to add complete URL with http/https. Otherwise it will not work properly</label> <br>
                                                <div class="deleteLink btn btn-danger btn-circle btn-sm" id="deletelinkid<?php echo $linksCounter;?>" style="cursor:pointer;"><i class="fas fa-trash"></i></div>
                                            </div>
                                        </div>
                                    <?php }

                                }?>
                                <input type="text"  id="linkCounter" value="<?php echo $linksCounter+1;?>" style="display:none;" name="linkCounter">
                            </div>

                            <h3 class="display-6 btn btn-info btn-icon-split" id="ANL" style="cursor:pointer;">
                                        <span class="icon text-white-50">
                                            <i class="fas fa fa-plus"></i>
                                        </span>
                                <span class="text">Add New Link</span>
                            </h3>
                        </div>
                        <!--LINKssssssssssssssssssssssssssssssssssssssssssss-->

                        <div class="col-auto">
                            <button type="submit" class="btn btn-success mb-2 btn-lg submitBtn" class=".bg-gradient-success">Submit</button>
                        </div>
                        <div class="col-auto">
                            <div class="statusMsg"></div>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        <?php
    }
    ///////////////////////////////////////////
    //////////delete meeting //////////////
    /////////////////////////////////////////
    public function deleteMeeting($meetingId){
        global $pdo;
        try {
            $query = "SELECT * FROM `meetings` where `id`=:meetingId";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam('meetingId', $meetingId, PDO::PARAM_STR);
            $stmt->execute();
            $row   = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error : ".$e->getMessage();
        }
        $files=unserialize($row["files"]);
        if($row["files"]!="N;"){
            $countFiles=count($files);
        }else{
            $countFiles=0;
        }
        if($countFiles!=0){
            if($countFiles!=0){
                for ($j=1; $j <$countFiles+1 ; $j++) {
                    if (file_exists("books/".$files[$j])){
                        unlink("books/".$files[$j]);
                    }
                }
            }
        }

        try {
            $sql = "Delete from `meetings` WHERE id=?";
            $stmt= $pdo->prepare($sql);
            $stmt->execute([$meetingId]);

        } catch (PDOException $e) {
            echo "Error : ".$e->getMessage();
        }
        $URL="?page=deleteMeeting&status=deleted";
        echo "<script type='text/javascript'>document.location.href='{$URL}';</script>";
        echo '<META HTTP-EQUIV="refresh" content="0;URL=' . $URL . '">';
    }
}
/*-----------------v1.0 changes for calender-------------*/
class Event{
    function viewAllEvent(){
        ?>
        <div class="row">
            <div class="col-lg-2"><h1 style="color: black">Legends:</h1></div>
            <div class="col-lg-2" >
                <div class="event-container" role="button" data-event-index="41" style="background: white">
                    <div class="event-icon">
                        <div class="event-bullet-birthday"></div>
                    </div>
                    <div class="event-info">
                        <p class="event-title">Meeting</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-2" >
                <div class="event-container" role="button" data-event-index="41" style="background: white">
                    <div class="event-icon">
                        <div class="event-bullet-holiday"></div>
                    </div>
                    <div class="event-info">
                        <p class="event-title">Holiday</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-2" >
                <div class="event-container" role="button" data-event-index="41" style="background: white">
                    <div class="event-icon">
                        <div class="event-bullet-event"></div>
                    </div>
                    <div class="event-info">
                        <p class="event-title">Event</p>
                    </div>
                </div>
            </div>
            <?php if($_SESSION['role']==1){?>
                <div class="col-lg-3">
                    <div class="row">
                        <div class="col-lg-6">
                            <a class="btn btn-success btn-medium" id="addBtn" href="?page=addNewEvent">ADD EVENT</a>
                        </div>
                        <div class="col-lg-6">
                            <a class="btn btn-warning btn-medium" id="removeBtn" href="?page=editOrDeleteEvents">REMOVE EVENT</a>
                        </div>
                    </div>

                </div>
            <?php } ?>
        </div>

        <div style="padding: 20px;"></div>

        <div id="demoEvoCalendar" data-set-theme="Orange Coral"></div>
        <?php
    }
    function addNewEvent(){?>

        <script>
            $(document).ready(function(){
                $('#category').click(function(){
                    var category=$("#category option:selected").text();
                    if(category=="Select the Category"){
                        $('#categoryerror').remove();
                        $('#category').after('<div class="error" id="categoryerror" style="padding-top:10px; margin:0px;"><p class="error" style="color:red; font-size:12px;margin:0px;">Please select one of the options</p></div>');
                    }else{
                        $('#categoryerror').remove();
                    }
                });

                $("#addANewEvent").on('submit', function(e) {
                    e.preventDefault();
                    //alert("a");
                    var name = $('#name').val();
                    var desc = $('#desc').val();
                    var date = $('#date').val();
                    var category = $("#category option:selected").text();
                    var errorCount=0;
                    if (name.length < 1) {
                        $('#name').after('<div id="nameError" class="error" style="padding-top:10px; margin:0px;"><p class="error" style="color:red; font-size:12px;margin:0px;">This field is required</p></div>');
                        errorCount++;
                    } else {
                        $('#nameError').remove();
                    }

                    if (desc.length < 1) {
                        $('#desc').after('<div id="descError" class="error" style="padding-top:10px; margin:0px;"><p class="error" style="color:red; font-size:12px;margin:0px;">This field is required</p></div>');
                        errorCount++;
                    }else{
                        $('#descError').remove();
                    }

                    if(category=="Select the Category"){
                        $('#categoryerror').remove();
                        $('#category').after('<div class="error" id="categoryerror" style="padding-top:10px; margin:0px;"><p class="error" style="color:red; font-size:12px;margin:0px;">Please select one of the options</p></div>');
                        errorCount++;
                    }else{
                        $('#categoryerror').remove();
                    }

                    if(errorCount==0){
                        $.ajax({
                            url: "lib.php",
                            type: "POST",
                            data: {
                                name: name,
                                desc: desc,
                                date: date,
                                category: category,
                                page: 'addANewEvent'
                            },
                            success: function (data) {
                                $('.statusMsg').html('<div class="alert alert-success" role="alert">Added Successfully!</div>');
                            }
                        });
                    }

                });
            });
        </script>
        <!-- Page Heading -->
        <h1 class="h3 mb-4 text-gray-800" style="text-align: center; padding-top: 30px;">Add New Event</h1>
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <div class="jumbotron bg-gray-200 border-bottom-success">
                    <form id="addANewEvent" method="post" enctype="multipart/form-data">
                        <input type="text" class="form-control" id="page" placeholder="" name="page" value="AddNewEvent" style="display: none;">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Name</label>
                            <input type="text" class="form-control" id="name" placeholder="Enter Name" name="name">
                        </div>

                        <div class="form-group">
                            <label for="exampleInputEmail1">description</label>
                            <input type="text" class="form-control" id="desc" placeholder="Enter description" name="description">
                        </div>

                        <div class="form-group row">
                            <label for="example-date-input" class="col-2 col-form-label">Date</label>
                            <div class="col-10">
                                <input class="form-control" type="date" value="" id="date" name="date" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <select id="category" name="category" class="dropdown mb-4 btn btn-primary dropdown-toggle">
                                <option>Select the Category</option>
                                <option value="event">Event</option>
                                <option value="holiday">Holiday</option>
                            </select>
                        </div>

                        <div class="col-auto">
                            <button type="submit" id="scheduleMeeting" class="btn btn-success mb-2 btn-lg submitBtn .bg-gradient-success">Submit</button>
                        </div>
                        <div class="col-auto">
                            <div class="statusMsg"></div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
        <?php
    }
    function editOrDeleteEvent(){
        global $pdo;
        try {
            $query = "SELECT * FROM `events`";
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $row   = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error : ".$e->getMessage();
        }

        ?>
        <h1 class="h3 mb-4 text-gray-800" style="text-align: center; padding-top: 30px;">Change Events Details</h1>
        <?php
        if (isset($_GET['status']) && $_GET['status']=="edited"){
            echo '<div class="alert alert-success" role="alert" id="userUpdationStatus" style="display: block;">
                    The event is updated successfully
                </div>';
        }elseif (isset($_GET['status']) && $_GET['status']=="deleted"){
            echo '<div class="alert alert-danger" role="alert" id="userUpdationStatus" style="display: block;">
                    The event is deleted successfully
                </div>';
        }
        ?>
        <!-- Page Heading -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">You can edit,update and delete events through this panel</h6>
                    </div>
                    <div class="card-body border-bottom-success">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                <tr>
                                    <th style="display:none;">Id</th>
                                    <th>Date</th>
                                    <th>Day</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Category</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tfoot>
                                <tr>
                                    <th style="display:none;">Id</th>
                                    <th>Date</th>
                                    <th>Day</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Category</th>
                                    <th>Action</th>
                                </tr>
                                </tfoot>
                                <tbody>
                                <?php
                                foreach ($row as $data)
                                {
                                    ?>
                                    <tr>
                                        <td style="display:none;"><?php echo $data["id"];?></td>
                                        <td><?php $convertToDate=strtotime($data["date"]); echo date("l",$convertToDate);?></td>
                                        <td><?php $convertToDate=strtotime($data["date"]); echo date("d M Y",$convertToDate);?></td>
                                        <td><?php  echo  $data["name"];?></td>
                                        <td>
                                            <?php echo $data["description"];?>
                                        </td>
                                        <td>
                                            <?php
                                            echo $data["type"];
                                            ?>
                                        </td>
                                        <td>
                                            <a href="?page=editEvent&id=<?php echo $data["id"];?>" class="btn btn-primary btn-circle btn-md" id="edit<?php echo $data["id"];?>">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="javascript:void(0);" onclick="confirmDelete(<?php echo $data['id']; ?>)" class="btn btn-danger btn-circle btn-md">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php }?>
                                </tbody>
                            </table>
                            <script>
                                function confirmDelete(eventID) {
                                    Swal.fire({
                                        title: 'Are you sure?',
                                        text: "You won't be able to revert this!",
                                        icon: 'warning',
                                        showCancelButton: true,
                                        confirmButtonColor: '#d33',
                                        cancelButtonColor: '#3085d6',
                                        confirmButtonText: 'Yes, delete it!'
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            // Redirect to the deletion URL
                                            window.location.href = `?page=deleteEvent&id=${eventID}`;
                                        }
                                    });
                                }
                            </script>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <?php
    }
    function editEvent($eventId){
        ?>

        <script>
            $(document).ready(function(){
                $('#category').click(function(){
                    var category=$("#category option:selected").text();
                    if(category=="Select the Category"){
                        $('#categoryerror').remove();
                        $('#category').after('<div class="error" id="categoryerror" style="padding-top:10px; margin:0px;"><p class="error" style="color:red; font-size:12px;margin:0px;">Please select one of the options</p></div>');
                    }else{
                        $('#categoryerror').remove();
                    }
                });

                $("#addANewEvent").on('submit', function(e) {
                    e.preventDefault();
                    var eventId = $('#eventId').val();
                    var name = $('#name').val();
                    var desc = $('#desc').val();
                    var date = $('#date').val();
                    var category = $("#category option:selected").text();
                    var errorCount=0;
                    if (name.length < 1) {
                        $('#name').after('<div id="nameError" class="error" style="padding-top:10px; margin:0px;"><p class="error" style="color:red; font-size:12px;margin:0px;">This field is required</p></div>');
                        errorCount++;
                    } else {
                        $('#nameError').remove();
                    }

                    if (desc.length < 1) {
                        $('#desc').after('<div id="descError" class="error" style="padding-top:10px; margin:0px;"><p class="error" style="color:red; font-size:12px;margin:0px;">This field is required</p></div>');
                        errorCount++;
                    }else{
                        $('#descError').remove();
                    }

                    if(category=="Select the Category"){
                        $('#categoryerror').remove();
                        $('#category').after('<div class="error" id="categoryerror" style="padding-top:10px; margin:0px;"><p class="error" style="color:red; font-size:12px;margin:0px;">Please select one of the options</p></div>');
                        errorCount++;
                    }else{
                        $('#categoryerror').remove();
                    }

                    if(errorCount==0){
                        $.ajax({
                            url: "lib.php",
                            type: "POST",
                            data: {
                                id:eventId,
                                name: name,
                                desc: desc,
                                date: date,
                                category: category,
                                page: 'updateEvent'
                            },

                            success: function (data) {
                                $('.statusMsg').html(data+'<div class="alert alert-success" role="alert">Added Successfully!</div>');
                            }
                        });
                    }

                });
            });
        </script>
        <?php
        global $pdo;

        try {
            $query = "select * from `events` where `id`=:Id";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam('Id', $eventId, PDO::PARAM_STR);
            $stmt->execute();
            $row   = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error : ".$e->getMessage();
        }

        ?>
        <!-- Page Heading -->
        <h1 class="h3 mb-4 text-gray-800" style="text-align: center; padding-top: 30px;">Add New Meeting</h1>
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <div class="jumbotron bg-gray-200 border-bottom-success">
                    <form id="addANewEvent" method="post" enctype="multipart/form-data">
                        <input type="text" class="form-control" id="page" placeholder="" name="page" value="editEvent" style="display: none;">
                        <input type="text" class="form-control" id="eventId" placeholder="" name="eventId" value="<?php echo $row['id'];?>" style="display: none;">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Name</label>
                            <input type="text" class="form-control" id="name" placeholder="Enter Name" name="name" value="<?php echo $row['name'];?>">
                        </div>

                        <div class="form-group">
                            <label for="exampleInputEmail1">description</label>
                            <input type="text" class="form-control" id="desc" placeholder="Enter description" name="description" value="<?php echo $row['description'];?>">
                        </div>

                        <div class="form-group row">
                            <label for="example-date-input" class="col-2 col-form-label">Date</label>
                            <div class="col-10">
                                <input class="form-control" type="date" value="<?php echo $row['date'];?>" id="date" name="date" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <select id="category" name="category" class="dropdown mb-4 btn btn-primary dropdown-toggle">
                                <option>Select the Category</option>
                                <option value="event">Event</option>
                                <option value="holiday">Holiday</option>
                            </select>
                            <br>
                            <span><b>Selected Type: </b><?php echo $row['type'];?></span>
                        </div>

                        <div class="col-auto">
                            <button type="submit" id="scheduleMeeting" class="btn btn-success mb-2 btn-lg submitBtn .bg-gradient-success">Submit</button>
                        </div>
                        <div class="col-auto">
                            <div class="statusMsg"></div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
        <?php
    }
    function deleteEvent(){
        global $pdo;
        $eid=$_GET['id'];
        $sql = "Delete from events WHERE id=?";
        $stmt= $pdo->prepare($sql);
        $stmt->execute([$eid]);
        $URL="?page=editOrDeleteEvents&status=deleted";
        echo "<script type='text/javascript'>document.location.href='{$URL}';</script>";
        echo '<META HTTP-EQUIV="refresh" content="0;URL=' . $URL . '">';
    }
}
/*-/////////////////-----v1.0 changes for calender//////////----*/

/*-----------------v2.0 changes for adding more forms-------------*/
class Educator{
    function formsAssignments(){
        global $pdo;
        try {
            $query = "SELECT * FROM `user` where `role`=2";
            $stmt = $pdo->prepare($query);
            //$stmt->bindParam('Id', $eventId, PDO::PARAM_STR);
            $stmt->execute();
            $row   = $stmt->fetchAll(PDO::FETCH_ASSOC);
            //var_dump($row);
        } catch (PDOException $e) {
            echo "Error : ".$e->getMessage();
        }
        ?>
        <script>
            $(document).ready(function(){
                $('#educatorSelector').change(function(){
                    var educator=$("#educatorSelector option:selected").text();
                    if(educator=="Select the educator"){
                        $('#educatornotselectederror').remove();
                        $('#educatorSelector').after('<div class="error" id="educatornotselectederror" style="padding-top:10px; margin:0px;"><p class="error" style="color:red; font-size:12px;margin:0px;">Please select one of the options</p></div>');
                    }else{
                        var educatorid = $("#educatorSelector option:selected").val();
                        $.ajax({
                            url: "lib.php",
                            type: "POST",
                            data: {
                                educatorid:educatorid,
                                page:'getEducatorRegisteredForms'
                            },
                            success: function (data) {
                                //$('.statusMsg').html(data+'<div class="alert alert-success" role="alert">Added Successfully!</div>');
                                var result = jQuery.parseJSON(data);
                                //console.log(result);
                                //alert(data);
                                if(result!=0){
                                    //alert(result.id);
                                    $('#recordid').val(result.id);
                                    $('#enrolmentformid').val(result.enrolmentformid);
                                    $('#enrolmentformprefill').val(result.enrolmentformprefill);
                                    $('#regularform').val(result.regularform);
                                    $('#regularformprefill').val(result.regularformprefill);
                                    $('#excursionform').val(result.excursionform);
                                    $('#excursionformprefill').val(result.excursionformprefill);
                                    $('#regularForSchoolForm').val(result.regularForSchoolForm);
                                    $('#regularForSchoolFormPrefill').val(result.regularForSchoolFormPrefill);
                                    $('#homeandfdcForm').val(result.homeandfdcForm);
                                    $('#homeandfdcFormPrefill').val(result.homeandfdcFormPrefill);
                                }else{
                                    $('#recordid').val('');
                                    $('#enrolmentformid').val('');
                                    $('#enrolmentformprefill').val('');
                                    $('#regularform').val('');
                                    $('#regularformprefill').val('');
                                    $('#excursionform').val('');
                                    $('#excursionformprefill').val('');
                                    $('#regularForSchoolForm').val('');
                                    $('#regularForSchoolFormPrefill').val('');
                                    $('#homeandfdcForm').val('');
                                    $('#homeandfdcFormPrefill').val('');
                                }

                                if(!result.formPassword || result.formPassword==' '){
                                    $('#passwordsection').remove();
                                    $('#educatordropdowndiv').before('<div id="passwordsection"><h6 style="color: red;">Password not found</h6><button type="button" class="btn btn-primary" data-toggle="modal" data-target="#generatingPasswordConfirmation"><span class="text"><i class="fa fa-plus"></i> Generate  Password</span></button></div>');
                                    $("#generatenewpasswordlink").attr("href", '?page=generatepassword&eduid='+educatorid+'&gobackto=assignformpage');
                                }else{
                                    $('#passwordsection').remove();
                                    $('#educatordropdowndiv').before('<div id="passwordsection"><h6 style="color: black;">Forms Password: <span style="color: green;">'+result.formPassword+'</span></h6></div>');
                                }

                            }
                        });
                        $('#educatornotselectederror').remove();
                    }
                });

                $("#assignform").on('submit', function(e) {

                    e.preventDefault();
                    var educatorid = $("#educatorSelector option:selected").val();
                    var enrolmentformid = $('#enrolmentformid').val();
                    var enrolmentformprefill = $('#enrolmentformprefill').val();

                    var regularform= $('#regularform').val();
                    var regularformprefill= $('#regularformprefill').val();
                    var excursionform= $('#excursionform').val();
                    var excursionformprefill= $('#excursionformprefill').val();
                    var regularForSchoolForm= $('#regularForSchoolForm').val();
                    var regularForSchoolFormPrefill= $('#regularForSchoolFormPrefill').val();
                    var homeandfdcForm=$('#homeandfdcForm').val();
                    var homeandfdcFormPrefill= $('#homeandfdcFormPrefill').val();
                    var errorCount=0;
                    if (enrolmentformid.length < 1) {

                    }
                    if (enrolmentformprefill.length < 1) {
                        enrolmentformprefill=null;
                    }
                    if(regularform.length < 1){
                        regularform =null;
                    }
                    if(regularformprefill.length < 1){
                        regularformprefill=null;
                    }
                    if(excursionform.length < 1){
                        excursionform=null;
                    }
                    if(excursionformprefill.length < 1){
                        excursionformprefill=null;
                    }
                    if(regularForSchoolForm.length < 1){
                        regularForSchoolForm =null;
                    }
                    if(regularForSchoolFormPrefill.length < 1){
                        regularForSchoolFormPrefill =null;
                    }
                    if(homeandfdcForm.length < 1){
                        homeandfdcForm=null;
                    }
                    if(homeandfdcFormPrefill.length < 1){
                        homeandfdcFormPrefill=null;
                    }


                    // if (enrolmentformid.length < 1) {
                    //     $('#enrolmentError').remove();
                    //     $('#enrolmentformid').after('<div id="enrolmentError" class="error" style="padding-top:10px; margin:0px;"><p class="error" style="color:red; font-size:12px;margin:0px;">This field is required</p></div>');
                    //     errorCount++;
                    // } else {
                    //     $('#enrolmentError').remove();
                    // }
                    //
                    // if (enrolmentformprefill.length < 1) {
                    //     $('#enrolmentformprefillError').remove();
                    //     $('#enrolmentformprefill').after('<div id="enrolmentformprefillError" class="error" style="padding-top:10px; margin:0px;"><p class="error" style="color:red; font-size:12px;margin:0px;">This field is required</p></div>');
                    //     errorCount++;
                    // } else {
                    //     $('#enrolmentformprefillError').remove();
                    // }
                    //
                    // if (homeandfdcFormPrefill.length < 1) {
                    //     $('#homeandfdcFormPrefillError').remove();
                    //     $('#homeandfdcFormPrefill').after('<div id="homeandfdcFormPrefillError" class="error" style="padding-top:10px; margin:0px;"><p class="error" style="color:red; font-size:12px;margin:0px;">This field is required</p></div>');
                    //     errorCount++;
                    // } else {
                    //     $('#homeandfdcFormPrefillError').remove();
                    // }
                    //
                    // if (homeandfdcForm.length < 1) {
                    //     $('#homeandfdcFormError').remove();
                    //     $('#homeandfdcForm').after('<div id="homeandfdcFormError" class="error" style="padding-top:10px; margin:0px;"><p class="error" style="color:red; font-size:12px;margin:0px;">This field is required</p></div>');
                    //     errorCount++;
                    // } else {
                    //     $('#homeandfdcFormError').remove();
                    // }
                    //
                    // if (regularForSchoolFormPrefill.length < 1) {
                    //     $('#regularForSchoolFormPrefillError').remove();
                    //     $('#regularForSchoolFormPrefill').after('<div id="regularForSchoolFormPrefillError" class="error" style="padding-top:10px; margin:0px;"><p class="error" style="color:red; font-size:12px;margin:0px;">This field is required</p></div>');
                    //     errorCount++;
                    // } else {
                    //     $('#regularForSchoolFormPrefillError').remove();
                    // }
                    //
                    // if (regularForSchoolForm.length < 1) {
                    //     $('#regularForSchoolFormError').remove();
                    //     $('#regularForSchoolForm').after('<div id="regularForSchoolFormError" class="error" style="padding-top:10px; margin:0px;"><p class="error" style="color:red; font-size:12px;margin:0px;">This field is required</p></div>');
                    //     errorCount++;
                    // } else {
                    //     $('#regularForSchoolFormError').remove();
                    // }
                    //
                    // if (excursionformprefill.length < 1) {
                    //     $('#excursionformprefillError').remove();
                    //     $('#excursionformprefill').after('<div id="excursionformprefillError" class="error" style="padding-top:10px; margin:0px;"><p class="error" style="color:red; font-size:12px;margin:0px;">This field is required</p></div>');
                    //     errorCount++;
                    // } else {
                    //     $('#excursionformprefillError').remove();
                    // }
                    //
                    // if (excursionform.length < 1) {
                    //     $('#excursionformError').remove();
                    //     $('#excursionform').after('<div id="excursionformError" class="error" style="padding-top:10px; margin:0px;"><p class="error" style="color:red; font-size:12px;margin:0px;">This field is required</p></div>');
                    //     errorCount++;
                    // } else {
                    //     $('#excursionformError').remove();
                    // }
                    //
                    // if (regularformprefill.length < 1) {
                    //     $('#regularformprefillError').remove();
                    //     $('#regularformprefill').after('<div id="regularformprefillError" class="error" style="padding-top:10px; margin:0px;"><p class="error" style="color:red; font-size:12px;margin:0px;">This field is required</p></div>');
                    //     errorCount++;
                    // } else {
                    //     $('#regularformprefillError').remove();
                    // }
                    //
                    // if (regularform.length < 1) {
                    //     $('#regularformError').remove();
                    //     $('#regularform').after('<div id="regularformError" class="error" style="padding-top:10px; margin:0px;"><p class="error" style="color:red; font-size:12px;margin:0px;">This field is required</p></div>');
                    //     errorCount++;
                    // } else {
                    //     $('#regularformError').remove();
                    // }
                    //
                    // if(educatorid==0){
                    //     $('#educatornotselectederror').remove();
                    //     $('#educatorSelector').after('<div class="error" id="educatornotselectederror" style="padding-top:10px; margin:0px;"><p class="error" style="color:red; font-size:12px;margin:0px;">Please select one of the options</p></div>');
                    //     errorCount++;
                    // }else{
                    //     $('#educatornotselectederror').remove();
                    // }

                    var recordid = $('#recordid').val();
                    //alert(recordid.length)
                    if(recordid.length == 0){
                        var status="insert";
                    }else {
                        var status="update";
                    }

                    // var regularform= $('#regularform').val();
                    // var regularformprefill= $('#regularformprefill').val();
                    // var excursionform= $('#excursionform').val();
                    // var excursionformprefill= $('#excursionformprefill').val();
                    // var regularForSchoolForm= $('#regularForSchoolForm').val();
                    // var regularForSchoolFormPrefill= $('#regularForSchoolFormPrefill').val();
                    // var homeandfdcForm=$('#homeandfdcForm').val();
                    // var homeandfdcFormPrefill= $('#homeandfdcFormPrefill').val();

                    if(errorCount==0){
                        $.ajax({
                            url: "lib.php",
                            type: "POST",
                            data: {
                                recordid:recordid,
                                educatorid:educatorid,
                                enrolmentformid:enrolmentformid,
                                enrolmentformprefill: enrolmentformprefill,
                                regularform:regularform,
                                regularformprefill:regularformprefill,
                                excursionform:excursionform,
                                excursionformprefill:excursionformprefill,
                                regularForSchoolForm:regularForSchoolForm,
                                regularForSchoolFormPrefill:regularForSchoolFormPrefill,
                                homeandfdcForm:homeandfdcForm,
                                homeandfdcFormPrefill:homeandfdcFormPrefill,
                                status:status,
                                page: 'assignform'
                            },
                            success: function (data) {
                                $('.statusMsg').html(data+'<div class="alert alert-success" role="alert">Added Successfully!</div>');
                            }
                        });
                    }

                });
            });
        </script>
        <?php if(isset($_GET['eduid'])){
            ?>
        <script>
            $(document).ready(function() {
                $("#educatorSelector").click();
                $("#educatorSelector").val(<?php echo $_GET['eduid']; ?>);
                var educatorid=<?php echo $_GET['eduid']; ?>;
                $.ajax({
                    url: "lib.php",
                    type: "POST",
                    data: {
                        educatorid:educatorid,
                        page:'getEducatorRegisteredForms'
                    },
                    success: function (data) {
                        //$('.statusMsg').html(data+'<div class="alert alert-success" role="alert">Added Successfully!</div>');
                        var result = jQuery.parseJSON(data);
                        //console.log(result);
                        //alert(data);
                        if(result!=0){
                            $('#recordid').val(result.id);
                            $('#enrolmentformid').val(result.enrolmentformid);
                            $('#enrolmentformprefill').val(result.enrolmentformprefill);
                            $('#regularform').val(result.regularform);
                            $('#regularformprefill').val(result.regularformprefill);
                            $('#excursionform').val(result.excursionform);
                            $('#excursionformprefill').val(result.excursionformprefill);
                            $('#regularForSchoolForm').val(result.regularForSchoolForm);
                            $('#regularForSchoolFormPrefill').val(result.regularForSchoolFormPrefill);
                            $('#homeandfdcForm').val(result.homeandfdcForm);
                            $('#homeandfdcFormPrefill').val(result.homeandfdcFormPrefill);
                        }else{
                            $('#recordid').val('');
                            $('#enrolmentformid').val('');
                            $('#enrolmentformprefill').val('');
                            $('#regularform').val('');
                            $('#regularformprefill').val('');
                            $('#excursionform').val('');
                            $('#excursionformprefill').val('');
                            $('#regularForSchoolForm').val('');
                            $('#regularForSchoolFormPrefill').val('');
                            $('#homeandfdcForm').val('');
                            $('#homeandfdcFormPrefill').val('');
                        }
                        if(!result.formPassword || result.formPassword==' '){
                            $('#passwordsection').remove();
                            $('#educatordropdowndiv').before('<div id="passwordsection"><h6 style="color: red;">Password not found</h6><button type="button" class="btn btn-primary" data-toggle="modal" data-target="#generatingPasswordConfirmation"><span class="text"><i class="fa fa-plus"></i> Generate  Password</span></button></div>');
                            $("#generatenewpasswordlink").attr("href", '?page=generatepassword&eduid='+educatorid+'&gobackto=assignformpage');
                        }else{
                            $('#passwordsection').remove();
                            $('#educatordropdowndiv').before('<div id="passwordsection"><h6 style="color: black;">Forms Password: <span style="color: green;">'+result.formPassword+'</span></h6></div>');
                        }
                    }
                });
                $('#educatornotselectederror').remove();
            });
        </script>

        <?php
        }?>
        <!-- Modal for adding a new password -->
        <div class="modal fade" id="generatingPasswordConfirmation" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        Are you sure about generating a new password for this user?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
                        <a href="" class="btn btn-danger" title="Generate New Password" id="generatenewpasswordlink">
                            <span class="text">I'm sure about it</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!--/////////////////////// Modal for adding a new password -->

        <h1 class="h3 mb-4 text-gray-800" style="
        text-align: center; padding-top: 30px;">Assign/Edit Form</h1>

        <div class="row">
            <div class="col-md-6 offset-md-3">

                <div class="jumbotron bg-gray-200 border-bottom-success">
                    <form id="assignform" method="post" enctype="multipart/form-data">
                        <div class="form-group" id="educatordropdowndiv">
                            <input type="text" class="form-control" id="recordid" placeholder="" name="recordid" value="" style="display: none;">
                            <label for="exampleInputEmail1">Educator</label><br>
                            <select id="educatorSelector" name="educator" class="dropdown mb-4 btn btn-primary dropdown-toggle">
                                <option value="0">Select the educator</option>
                                <?php foreach($row as $var) {
                                    echo '<option value="'.$var['id'].'">'.$var['firstname'].' '.$var['lastname'].'</option>';
                                }?>
                            </select>

                        </div>


<!--                        <input type="text" class="form-control" id="page" placeholder="" name="page" value="AddNewEvent" style="display: none;">-->

                        <div class="form-group" >
                            <label for="exampleInputEmail1">Child Enrolment Form</label>
                            <input type="text" class="form-control" id="enrolmentformid" placeholder="Enter Form id" name="enrolmentformid">
                            <br>
                            <input type="text" class="form-control" id="enrolmentformprefill" placeholder="Enter Form prefilled Link" name="enrolmentformprefill">
                        </div>

                        <div class="form-group">
                            <label for="exampleInputEmail1">Regular/Rountine Outing/Transport Authorisation Form</label>
                            <input type="text" class="form-control" id="regularform" placeholder="Enter Form id" name="regularform">
                            <br>
                            <input type="text" class="form-control" id="regularformprefill" placeholder="Enter Form prefilled Link" name="regularformprefill">
                        </div>

                        <div class="form-group">
                            <label for="exampleInputEmail1">Excursion/Transport Authorisation Form</label>
                            <input type="text" class="form-control" id="excursionform" placeholder="Enter Form id" name="excursionform">
                            <br>
                            <input type="text" class="form-control" id="excursionformprefill" placeholder="Enter Form prefilled Link" name="excursionformprefill">
                        </div>

                        <div class="form-group">
                            <label for="exampleInputEmail1">Regular/Routine Outing/ Transportation Authorisation Form(School,Kinder, PreSchool)</label>
                            <input type="text" class="form-control" id="regularForSchoolForm" placeholder="Enter Form id" name="regularForSchoolForm">
                            <br>
                            <input type="text" class="form-control" id="regularForSchoolFormPrefill" placeholder="Enter Form prefilled Link" name="regularForSchoolFormPrefill">
                        </div>

                        <div class="form-group">
                            <label for="exampleInputEmail1">Home and FDC Residence Regular/ Routine Outing/ Transportation Authorisation Form</label>
                            <input type="text" class="form-control" id="homeandfdcForm" placeholder="Enter Form id" name="homeandfdcForm">
                            <br>
                            <input type="text" class="form-control" id="homeandfdcFormPrefill" placeholder="Enter Form prefilled Link" name="homeandfdcFormPrefill">
                        </div>

                        <div class="col-auto">
                            <button type="submit" id="scheduleMeeting" class="btn btn-success mb-2 btn-lg submitBtn .bg-gradient-success">Submit</button>
                        </div>
                        <div class="col-auto">
                            <div class="statusMsg"></div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
        <?php
    }
    function allEducatorsAssignments(){
        if(isset($_GET['status']) && $_GET['status']=="deleted"){?>
            <div class="card mb-4 py-3 border-left-danger" style="padding-top:0px !important;padding-bottom:0px !important; ">
                <div class="card-body" id="msg">
                    Forms Assignement is deleted!
                </div>
            </div>
        <?php }
        global $pdo;
        try {
            //formassignments.id as formassignmentsid
            $query = "SELECT * FROM `formassignments` JOIN user where user.id=formassignments.eduid";
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $row   = $stmt->fetchAll(PDO::FETCH_ASSOC);
            //var_dump($row);
        } catch (PDOException $e) {
            echo "Error : ".$e->getMessage();
        }

        ?>


        <h1 class="h3 mb-4 text-gray-800" style="text-align: center; padding-top: 30px;">All Educators Assignments</h1>
        <!-- Page Heading -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">You can edit,update and delete assigned forms through this panel</h6>
                    </div>
                    <div class="card-body border-bottom-success">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                <tr>
                                    <th style="display:none;">Id</th>
                                    <th>Educator Name</th>
                                    <th>Enrolment Form Id</th>
                                    <th>Enrolment Form Prefill</th>
                                    <th>Regular/Rountine Outing/Transport Authorisation Form</th>
                                    <th>prefill</th>
                                    <th>Excursion/Transport Authorisation Form</th>
                                    <th>prefill</th>
                                    <th>Regular/Routine Outing/ Transportation Authorisation Form(School,Kinder, PreSchool)</th>
                                    <th>prefill</th>
                                    <th>Home and FDC Residence Regular/ Routine Outing/ Transportation Authorisation Form</th>
                                    <th>prefill</th>
                                    <th>Forms Password</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tfoot>
                                <tr>
                                    <th style="display:none;">Id</th>
                                    <th>Educator Name</th>
                                    <th>Enrolment Form Id</th>
                                    <th>Enrolment Form Prefill</th>
                                    <th>Regular/Rountine Outing/Transport Authorisation Form</th>
                                    <th>prefill</th>
                                    <th>Excursion/Transport Authorisation Form</th>
                                    <th>prefill</th>
                                    <th>Regular/Routine Outing/ Transportation Authorisation Form(School,Kinder, PreSchool)</th>
                                    <th>prefill</th>
                                    <th>Home and FDC Residence Regular/ Routine Outing/ Transportation Authorisation Form</th>
                                    <th>prefill</th>
                                    <th>Forms Password</th>
                                    <th>Action</th>
                                </tr>
                                </tfoot>
                                <tbody>
                                <?php
                                foreach ($row as $data)
                                {
                                    ?>
                                    <!-- Modal for deleting password -->
                                    <div class="modal fade" id="deletingPassword<?php echo $data["eduid"]?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    Are you sure about deleting forms record for this educator?
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
                                                    <a href="?page=deleteassignedforms&id=<?php echo $data["eduid"];?>" class="btn btn-danger" title="Delete Password">
                                                        <span class="text">I'm sure about it</span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--/////////////////////// Modal for deleting password -->
                                    <tr>
                                        <td style="display:none;"><?php echo $data["id"];?></td>
                                        <td><?php echo $data["firstname"].' '.$data["lastname"];?></td>
                                        <td><?php echo $data["enrolmentformid"];?></td>
                                        <td><?php echo $data["enrolmentformprefill"]; ?></td>

                                        <td><?php echo $data["regularform"];?></td>
                                        <td><?php echo $data["regularformprefill"];?></td>
                                        <td><?php echo $data["excursionform"];?></td>
                                        <td><?php echo $data["excursionformprefill"];?></td>
                                        <td><?php echo $data["regularForSchoolForm"];?></td>
                                        <td><?php echo $data["regularForSchoolFormPrefill"];?></td>
                                        <td><?php echo $data["homeandfdcForm"];?></td>
                                        <td><?php echo $data["homeandfdcFormPrefill"];?></td>

                                        <td><?php echo $data["formPassword"]; ?></td>
                                        <td>
                                            <a href="?page=viewEducatorSubmissions&eduid=<?php echo $data["eduid"];?>" class="btn btn-success btn-circle btn-md" id="edit<?php echo $data["eduid"];?>">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="?page=assignformstoaneducator&eduid=<?php echo $data["eduid"];?>" class="btn btn-primary btn-circle btn-md" id="edit<?php echo $data["eduid"];?>">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-danger btn-circle btn-md" data-toggle="modal" data-target="#deletingPassword<?php echo $data["eduid"]?>">
                                                <span class="text"><i class="fas fa-trash"></i></span>
                                            </button>
                                        </td>
                                    </tr>
                                <?php }?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <?php
    }
    function getAllSubmissions(){
        global $api_key;
        if(isset($_GET['status']) && $_GET['status']=="deleted"){?>
            <div class="card mb-4 py-3 border-left-danger" style="padding-top:0px !important;padding-bottom:0px !important; ">
                <div class="card-body" id="msg">
                    Form Submission is deleted!
                </div>
            </div>
        <?php }
        global $pdo;
        try {
            $role=$_SESSION['role'];
            if($role==1){
                $query = "SELECT * FROM `submissions` JOIN user where user.id=submissions.eduid";
                $stmt = $pdo->prepare($query);
                $stmt->execute();
                $row   = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }elseif ($role==2){
                //formassignments.id as formassignmentsid
                $eduid=$_SESSION['userid'];
                $query = "SELECT * FROM `submissions` JOIN user where user.id=:userid1 and submissions.eduid=:userid2";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam('userid1', $eduid, PDO::PARAM_STR);
                $stmt->bindParam('userid2', $eduid, PDO::PARAM_STR);
                $stmt->execute();
                $row   = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            //var_dump($row);
        } catch (PDOException $e) {
            echo "Error : ".$e->getMessage();
        }
        function getFormName($formname){
            if($formname=="excursion"){
                return "Excursion Form";
            }else{
                return "Other";
            }
        }
        function getAttachments($formid,$submissionid){
            global $api_key;
            $jotformAPI = new JotForm($api_key);
            $submissions = $jotformAPI->getFormFiles($formid);
            $counter=0;

            foreach ($submissions as $data)
            {
                if($data["form_id"]==$formid && $data["form_id"]==$formid){
                    $submissionsArray[$counter]['id']=$counter;
                    $submissionsArray[$counter]['type']=$data["type"];
                    $submissionsArray[$counter]['url']=$data["url"];
                    //$submissionsArray[$counter]=$data["url"];
                    $counter++;
                }
            }
            return $submissionsArray;
//                                    echo "<pre>";
//                                    print_r($submissions);
//                                    echo"</pre>";
        }
        ?>
        <h1 style="text-align: center;">List of Submissions</h1>
<!--        <a href='?page=assignSubmissions'>-->
<!--        <a href="?page=addmissingsubmission&amp;form=1" class="btn btn-primary "> -->
<!--            <span class="icon text-white-50"> -->
<!--                <i class="fas fa-plus"></i> -->
<!--            </span> -->
<!--            <span class="text">Add missing submission</span> -->
<!--        </a> -->

        <!-- Page Heading -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">You can edit,update and delete assigned forms through this panel</h6>
                    </div>
                    <div class="card-body border-bottom-success">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                <tr>
                                    <th style="display:none;">Id</th>
                                    <th>Educator Name</th>
                                    <th>Form name(Form ID)</th>
                                    <th>Submission Id</th>
                                    <th>Date and time</th>
                                    <th>Last Update</th>
                                    <th>Unqiue Id/Refrence</th>
                                    <th>Parent 1 id-Name-email</th>
                                    <th>Parents Sign Status</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tfoot>
                                <tr>
                                    <th style="display:none;">Id</th>
                                    <th>Educator Name</th>
                                    <th>Form name(Form ID)</th>
                                    <th>Submission Id</th>
                                    <th>Date and time</th>
                                    <th>Last Update</th>
                                    <th>Unqiue Id/Refrence</th>
                                    <th>Parent 1 id-Name-email</th>
                                    <th>Parents Sign Status</th>
                                    <th>Action</th>
                                </tr>
                                </tfoot>
                                <tbody>
                                <?php
                                global $api_key;
                                $jotformAPI = new JotForm($api_key);

                               // $submissions = $jotformAPI->getSubmission($row[0]["submissionid"]);
                                //                                    $i=0;
                                //                                    foreach ($submissions as $getfiles){
                                //                                        if($getfiles ["answers"][$i]["type"]=="control_fileupload"){
                                //                                            echo $getfiles["answers"][$i]["prettyFormat"];
                                //                                        $i++;
                                //                                        }
                                //                                    }
//                                $i=0;
//                                foreach ($submissions["answers"] as $getfiles){
////                                    echo "<pre>";
////                                   // print_r($getfiles);
////                                    echo "</pre>";
////                                        if($getfiles [$i]["type"]=="control_fileupload"){
////                                            echo $getfiles[$i]["prettyFormat"];
////                                        }
////                                    $i++;
//                                }

//                                echo "<pre>";
//                                //print_r( $submissions["answers"]);
//                                echo "</pre>";
//                                echo "<pre>";
//                                print_r($submissions);
//                                echo "</pre>";


                                foreach ($row as $data)
                                {
                                    ?>
                                    <!-- Modal for deleting password -->
                                    <div class="modal fade" id="deletingPassword<?php echo $data["id"];?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    Are you sure about deleting forms record for this educator?
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
                                                    <a href="?page=deleteSubmission&id=<?php echo $data["submissionid"];?>" class="btn btn-danger" title="Delete Password">
                                                        <span class="text">I'm sure about it</span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--/////////////////////// Modal for deleting password -->
                                    <tr>
                                        <td style="display:none;"><?php echo $data["id"];?></td>
                                         <td><?php echo $data["firstname"].' '.$data["lastname"];?></td>
                                        <td><?php echo $data["formtitle"]; echo "(".$data["formid"].")";?></td>
                                        <td><?php echo $data["submissionid"];?></td>
                                        <td><?php echo $data["datetime"]; ?></td>
                                        <td><?php if($data["updateddaytime"]==NULL){echo "-";}else{echo $data["updateddaytime"];} ?></td>
                                        <td>
                                            <?php echo $data["uniqueid"];?>
                                        </td>
                                        <td><?php if($data["parent1id"]==NULL){
                                            echo ' <a href="#" class="btn btn-warning btn-circle btn-md" id="">
                                                <i class="fas fa-question"></i>
                                            </a>';
                                            }else{
                                                echo getNameById($data["parent1id"]);
                                            }?></td>
                                        <td>
                                            <?php if($data["completionstatus"]==0 && ($data["parent1id"]!=NULL)){?>
                                            <a href="#" class="btn btn-danger btn-circle btn-md" id="">
                                                <i class="fas fa-times"></i>
                                            </a>
                                            <?php }else if($data["completionstatus"]==1){?>
                                            <a href="#" class="btn btn-success btn-circle btn-md" id="">
                                                <i class="fas fa-check"></i>
                                            </a>
                                            <?php }else{
                                                echo "-";

                                            }?>
                                        </td>
                                        <td>
                                                <a href="https://api.jotform.com/pdf-converter/<?php echo $data["formid"]; ?>/fill-pdf?download=1&submissionID=<?php echo $data["submissionid"];?>&apikey=<?php echo $api_key;?>" class="text-reset"  download>
                                                    <i class="fas fa-file-pdf" style="font-size:40px;color:red"></i>
                                                </a>
                                            <?php

                                            $links= getAttachments($data["formid"],$data["submissionid"]);
                                            //var_dump($links);
                                            foreach ($links as $link) {

                                                if(strpos($link["type"], "image/") !== false){
                                                    echo "<a href='".$link['url']."' target='_blank' class='btn btn-warning btn-circle btn-md'><i class='fas fa-image'></i></a>";
//
                                                }else{
                                                    echo "<a href='".$link['url']."' target='_blank' class='btn btn-warning btn-circle btn-md'><i class='fas fa-file-pdf'></i></a>";
                                                }

                                            }

                                            ?>
                                            <?php if($role==1){?>
                                            <a href="?page=sign&formid=<?php echo $data['formid'];?>&submissionid=<?php echo $data['submissionid'];?>" class="btn btn-primary btn-circle btn-md" id="edit<?php echo $data["eduid"];?>">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php }?>
                                            <button type="button" class="btn btn-danger btn-circle btn-md" data-toggle="modal" data-target="#deletingPassword<?php echo $data["id"];?>">
                                                <span class="text"><i class="fas fa-trash"></i></span>
                                            </button>
                                        </td>
                                    </tr>
                                <?php }?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <?php

    }
    function getAllSubmissionsAdmin(){
        global $api_key;
        global $pdo;
        if (isset($_GET['status']) && $_GET['status']=="deleted"){
            echo '<div class="alert alert-danger" role="alert" id="userUpdationStatus" style="display: block;">
                    Form Submission is deleted!
                </div>';
        }

        global $pdo;
        try {
            $role=$_SESSION['role'];
            if($role==1){
                $query = "SELECT * FROM `submissions` JOIN user where user.id=submissions.eduid";
                $stmt = $pdo->prepare($query);
                $stmt->execute();
                $row   = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }elseif ($role==2){
                //formassignments.id as formassignmentsid
                $eduid=$_SESSION['userid'];
                $query = "SELECT * FROM `submissions` JOIN user where user.id=:userid1 and submissions.eduid=:userid2";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam('userid1', $eduid, PDO::PARAM_STR);
                $stmt->bindParam('userid2', $eduid, PDO::PARAM_STR);
                $stmt->execute();
                $row   = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            //var_dump($row);
        } catch (PDOException $e) {
            echo "Error : ".$e->getMessage();
        }
        function getFormName($formname){
            if($formname=="excr"){
                return "Excursion Form";
            }else{
                return "Other";
            }
        }

        ?>


        <script>
            $(document).ready(function() {

                $.ajax({
                    url: "lib.php",
                    type: "POST",
                    data: {
                        page:'getAllEducators'
                    },
                    success: function (data) {
                        var result = jQuery.parseJSON(data);
                        $('#educatorsDropdown').append(`<option value="all" selected="">All Submissions</option>`);
                        $.each(result, function() {
                            var optionText= this.firstname+ ' '+this.lastname+' '+ this.email;
                            //alert(optionText);
                            $('#educatorsDropdown').append(`<option value="${this.id}">
                                           ${optionText}
                                      </option>`);
                        });
                    }
                });
                $("#educatorsDropdown").on('change', function() {
                    if ($(this).val() == 'all'){
                        location.reload();
                    }else if ($(this).val() != 'all'){
                        $('#dataTable').dataTable().fnClearTable();
                        var educatorid=$(this).val();
                        $.ajax({
                            url: "lib.php",
                            type: "POST",
                            data: {
                                page:'getEducatorSubmission',
                                educatorid:educatorid
                            },
                            success: function (data) {
                                var result = jQuery.parseJSON(data);
                                console.log(result);

                                oTable = $('#dataTable').dataTable();



                                function Add(id,name,formname,submissionid,datetime,update,uniqueid,parent,status,action){
                                    // var result=null;
                                    // if(parent1id!=null){
                                    //     $.ajax({
                                    //         url: "lib.php",
                                    //         type: "POST",
                                    //         data: {
                                    //             page:'getNameById',
                                    //             id: parent1id
                                    //         },
                                    //         success: function (data) {
                                    //             result = jQuery.parseJSON(data);
                                    //             console.log(result);
                                    //         }
                                    //     });
                                    // }

                                    //alert(result);
                                    var data = [
                                        id,name,formname,submissionid,datetime,update,uniqueid,parent,status,action
                                    ];

                                    oTable.fnAddData(data);
                                };
                                $( result ).each(function() {
                                    //alert(this.id);
                                    var name=this.firstname +' '+this.lastname;
                                    var formtitle=this.formtitle;
                                    var formname=formtitle+ '(' +this.formid+")";
                                    var updatetemp=this.updateddaytime;
                                    if(updatetemp!= 0 && updatetemp!=null && updatetemp!=''){
                                        var update=this.updateddaytime;
                                    }else{
                                        var update='-';
                                    }

                                    if(this.completionstatus==1){
                                        var status='<a href="#" class="btn btn-success btn-circle btn-md" id=""><i class="fas fa-check"></i></a>';
                                    }else if(this.completionstatus==0 && $(this.parent1id)!= null) {
                                        var status='<a href="#" class="btn btn-danger btn-circle btn-md" id=""><i class="fas fa-times"></i></a>';
                                    }else {
                                        var status="-";
                                    }
                                     if(this.completionstatus==1){
                                        var withCond='<a href="https://api.jotform.com/pdf-converter/'+this.formid+'/fill-pdf?download=1&submissionID='+this.submissionid+'&apikey=<?php global $api_key; echo $api_key;?>" class="text-reset"  download><i class="fas fa-file-pdf" style="font-size:40px;color:red"></i></a>';
                                     }else{
                                         var withCond='';
                                     }

                                    var parenttemp=this.parentname;
                                    if(parenttemp!= 0 && parenttemp!=null && parenttemp!=''){
                                        var parent=parenttemp;
                                    }else{
                                        var parent='-';
                                    }


                                    var withoutConditionAction='<a href="?page=viewEducatorSubmissions&eduid='+this.eduid+'" class="btn btn-success btn-circle btn-md" id="edit'+this.eduid+'"><i class="fas fa-eye"></i> </a>';
                                    var withoutConditionAction2 ='<a href="?page=assignformstoaneducator&eduid='+this.eduid+'" class="btn btn-primary btn-circle btn-md" id="edit'+this.eduid+'"><i class="fas fa-edit"></i></a>';
                                    var withoutConditionAction3=' <button type="button" class="btn btn-danger btn-circle btn-md" data-toggle="modal" data-target="#deletingPassword'+this.eduid+'">' +'<span class="text"><i class="fas fa-trash"></i></span></button>';
                                    var action=withCond+withoutConditionAction+withoutConditionAction2+withoutConditionAction3;

                                    Add(this.id,name,formname,this.submissionid,this.datetime,update,this.uniqueid,parent,status,action);
                                });
                                // if(result==false){
                                //     $('#educatorFormNotFoundError').remove();
                                //     $('#educatorDropdownSection').after('<div id="educatorFormNotFoundError" class="alert alert-danger" role="alert">No Forms have been allocated to this educator yet!</div>');
                                //     $("#forms").css('display','none');
                                // }else{
                                //     $('#educatorFormNotFoundError').remove();
                                //     $("#forms").css('display','block');
                                //     //
                                //     $("#excursionform").attr("href", "?page=createform&form=1&eduid="+educatorid);
                                // }
                            }
                        });
                    }
                });

            });
        </script>
        <h1 style="text-align: center;">List of Submissions</h1>

        <div class="row" id="educatorDropdownSection">
            <div class="offset-1 col-xs-4">
                <div class="form-group row" >
                    <select class="browser-default custom-select form-select" id="educatorsDropdown">
                    </select>
                </div>
            </div>
        </div>

        <!-- Page Heading -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">You can edit,update and delete assigned forms through this panel</h6>
                    </div>
                    <div class="card-body border-bottom-success">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                <tr>
                                    <th style="">Id</th>
                                    <th>Educator Name</th>
                                    <th>Form name(Form ID)</th>
                                    <th>Submission Id</th>
                                    <th>Date and time</th>
                                    <th>Last Update</th>
                                    <th>Unqiue Id/Refrence</th>
                                    <th>Parent 1 id-Name-email</th>
                                    <th>Parents Sign Status</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tfoot>
                                <tr>
                                    <th style="">Id</th>
                                    <th>Educator Name</th>
                                    <th>Form name(Form ID)</th>
                                    <th>Submission Id</th>
                                    <th>Date and time</th>
                                    <th>Last Update</th>
                                    <th>Unqiue Id/Refrence</th>
                                    <th>Parent 1 id-Name-email</th>
                                    <th>Parents Sign Status</th>
                                    <th>Action</th>
                                </tr>
                                </tfoot>
                                <tbody>
                                <?php
                                function getAttachments($formid,$submissionid){
                                    global $api_key;
                                    $jotformAPI = new JotForm($api_key);
                                    $submissions = $jotformAPI->getFormFiles($formid);
                                    $counter=0;

                                foreach ($submissions as $data)
                                {
                                    if($data["form_id"]==$formid && $data["form_id"]==$formid){
                                        $submissionsArray[$counter]['id']=$counter;
                                        $submissionsArray[$counter]['type']=$data["type"];
                                        $submissionsArray[$counter]['url']=$data["url"];
                                        //$submissionsArray[$counter]=$data["url"];
                                        $counter++;
                                    }
                                }
                                return $submissionsArray;
//                                    echo "<pre>";
//                                    print_r($submissions);
//                                    echo"</pre>";
                                }
                                foreach ($row as $data)
                                {
                                    ?>
                                    <!-- Modal for deleting password -->
                                    <div class="modal fade" id="deletingPassword<?php echo $data["id"];?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    Are you sure about deleting forms record for this educator?
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
                                                    <a href="?page=deleteSubmission&id=<?php echo $data["submissionid"];?>" class="btn btn-danger" title="Delete Password">
                                                        <span class="text">I'm sure about it</span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--/////////////////////// Modal for deleting password -->
                                    <tr>
                                        <td style=""><?php echo $data["id"];?></td>
                                        <td><?php echo $data["firstname"].' '.$data["lastname"];?></td>
                                        <td><?php echo $data["formtitle"]; echo "(".$data["formid"].")";?></td>
                                        <td><?php echo $data["submissionid"];?></td>
                                        <td><?php echo $data["datetime"]; ?></td>
                                        <td><?php if($data["updateddaytime"]==NULL){echo "-";}else{echo $data["updateddaytime"];} ?></td>
                                        <td>
                                            <?php echo $data["uniqueid"];?>
                                        </td>
                                        <td><?php if($data["parent1id"]==NULL){
                                                echo ' <a href="#" class="btn btn-warning btn-circle btn-md" id="">
                                                <i class="fas fa-question"></i>
                                            </a>';
                                            }else{
                                                echo getNameById($data["parent1id"]);
                                            }?></td>
                                        <td>
                                            <?php if($data["completionstatus"]==0 && ($data["parent1id"]!=NULL)){?>
                                                <a href="#" class="btn btn-danger btn-circle btn-md" id="">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                            <?php }else if($data["completionstatus"]==1){?>
                                                <a href="#" class="btn btn-success btn-circle btn-md" id="">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                            <?php }else{
                                                echo "-";

                                            }?>
                                        </td>
                                        <td>


                                            <a href="https://api.jotform.com/pdf-converter/<?php echo $data["formid"]; ?>/fill-pdf?download=1&submissionID=<?php echo $data["submissionid"];?>&apikey=<?php echo $api_key;?>" class="text-reset"  download>
                                                <i class="fas fa-file-pdf" style="font-size:40px;color:red"></i>
                                            </a>

                                            <a href="?page=sign&formid=<?php echo $data["formid"];?>&submissionid=<?php echo $data["submissionid"];?>" class="btn btn-primary btn-circle btn-md" id="edit<?php echo $data["eduid"];?>">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php

                                            $links= getAttachments($data["formid"],$data["submissionid"]);
                                            //var_dump($links);
                                            foreach ($links as $link) {

                                                if(strpos($link["type"], "image/") !== false){
                                                    echo "<a href='".$link['url']."' target='_blank' class='btn btn-warning btn-circle btn-md'><i class='fas fa-image'></i></a>";
//
                                                }else{
                                                    echo "<a href='".$link['url']."' target='_blank' class='btn btn-warning btn-circle btn-md'><i class='fas fa-file-pdf'></i></a>";
                                                }

                                            }

                                            ?>


                                            <button type="button" class="btn btn-danger btn-circle btn-md" data-toggle="modal" data-target="#deletingPassword<?php echo $data["id"];?>">
                                                <span class="text"><i class="fas fa-trash"></i></span>
                                            </button>
                                        </td>
                                    </tr>
                                <?php }?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <?php

    }
    function assignSubmission(){
        ?>
        <script>
            $(document).ready(function() {
                $.ajax({
                    url: "lib.php",
                    type: "POST",
                    data: {
                        page:'getAllParents'
                    },
                    success: function (data) {
                        var result = jQuery.parseJSON(data);
                        $('#parent1dropdown').append(`<option class="dropdown-item" >Select one of the parent</option>`);
                        $.each(result, function() {
                            var optionText= this.firstname+ ' '+this.lastname+' '+ this.email;
                            //alert(optionText);
                            $('#parent1dropdown').append(`<option value="${this.id}">
                                           ${optionText}
                                      </option>`);
                        });
                    }
                });
                //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                /////////////////////////////////////////////////////Get submission on Form Submission///////////////////////////////
                //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                $('#formTypes').change(function (){
                    $('#formAlreadyAssignError').remove();
                    $('#seeAllSubmissions').html('');
                    $('#submissions')
                        .find('option')
                        .remove()
                        .end();
                    //alert('abc');
                    var value=$( "#formTypes option:selected").val();
                    var educator=$("#educatorsDropdown").val();
                    if (educator == 'all'){
                        $('#educatorNotSelectedError').remove();
                        $('#createSelectOptionsInside').before('<div id="educatorNotSelectedError" class="alert alert-danger" role="alert">Select An Educator! </div>');
                    }else if (educator != 'all'){
                        $('#educatorNotSelectedError').remove();
                        //alert("EDUCATOR: "+educator);

                        $.ajax({
                            url: "lib.php",
                            type: "POST",
                            data: {
                                page:'getAllCompletedSubmissions',
                                form:value,
                                educatorid:educator
                            },
                            success: function (data) {
                                var result = jQuery.parseJSON(data);
                                 //console.log(result);
                                $("#completionTable").html('');
                                $("#completionTable").css("display","block");
                                $("#completionTable").append('<br>');
                                $("#completionTable").append('<a href="" class="btn btn-google btn-block"><i class=""></i>Completed Submissions</a>');
                                $( result ).each(function( result ) {
                                    //console.log( this.formtitle + ': <a href="#">View Submission</a>');
                                    $("#completionTable").append('<h3 style="padding: 0px;">'+this.formtitle+' <a href="?page=educatorSubmissions" target="_blank" style="font-size: 14px;">View Submission</a></h3>');
                                });
                            }
                        });



                        $.ajax({
                            url: "lib.php",
                            type: "POST",
                            data: {
                                page:'getAllSubmissionOfTheForm',
                                form:value,
                                educatorid:educator
                            },
                            success: function (data) {
                                var result = jQuery.parseJSON(data);
                               // console.log(result);

                                $('#submissions').append(`<option class="dropdown-item" >Select submission</option>`);
                                $.each(result, function() {
                                    var optionText= this.uniqueid+ ' '+this.datetime;
                                    //alert(optionText);
                                    $('#submissions').append(`<option value="${this.submissionid}">${optionText}</option>`);
                                    $('#seeAllSubmissions').append(`<input type="text" id="${this.submissionid}" value="${this.parent1id}" style="display:none;"/>`);
                                });
                                //Submission changed
                                $("#submissions").on('change', function() {
                                    $('#formAlreadyAssignError').remove();
                                    //get submission value
                                    var value=$( "#submissions option:selected").val();
                                    //get value of submissionid
                                    var assignedParentid=$( "#"+value).val();
                                    //copy from the same id of parent
                                    var parentDetails=$("#parent1dropdown option[value='"+assignedParentid+"']").text();
                                    // alert('this is assigned'+ parentDetails);
                                    if (parentDetails!="" && parentDetails!=null){
                                        //alert under submission
                                        $('#submissions').after('<div id="formAlreadyAssignError" class="alert alert-danger" role="alert">This submission is already assign to <b>'+parentDetails+'</b></div>');

                                    }

                                });


                            }
                        });
                    }
                    //alert(value);

                });

                $('#assignSubmission').submit(function(e) {
                    var errorCount=0;
                    e.preventDefault();

                        var parent1=$("#parent1dropdown option:selected").val();
                        var parent1dropdowntext=$("#parent1dropdown option:selected").text();
                        if(parent1dropdowntext=="Select one of the user"){
                            $('#parent1error').remove();
                            $('#parent1dropdown').after('<div class="error" id="parent1error" style="padding-top:10px; margin:0px;"><p class="error" style="color:red; font-size:12px;margin:0px;">Please select one of the parents</p></div>');
                            errorCount++;
                        }else{
                            $('#parent1error').remove();
                        }

                    var formType=$("#formTypes option:selected").text();
                    if(formType=="Select one of the form"){
                        $('#formSelectionerror').remove();
                        $('#formTypes').after('<div class="error" id="formSelectionerror" style="padding-top:10px; margin:0px;"><p class="error" style="color:red; font-size:12px;margin:0px;">Please select one of the options</p></div>');
                        errorcount++;
                    }else{
                        $('#formSelectionerror').remove();
                    }

                    var submissions=$("#submissions option:selected").text();
                    if(submissions=="Select submission"){
                        $('#formSubmissionsrror').remove();
                        $('#submissions').after('<div class="error" id="formSubmissionsrror" style="padding-top:10px; margin:0px;"><p class="error" style="color:red; font-size:12px;margin:0px;">Please select one of the options</p></div>');
                        errorcount++;
                    }else{
                        $('#formSubmissionsrror').remove();
                    }


                    if(errorCount==0){
                        var formType=$("#formTypes option:selected").val();
                        var submissions=$("#submissions option:selected").val();

                        var parent1=$("#parent1dropdown option:selected").val();
                        var info={
                            page:'assignSubmissionsToParent',
                            getNoOfParents:1,
                            parent1:parent1,
                            formType:formType,
                            submissions:submissions
                        };

                        //console.log(info);
                        $.ajax({
                            url: "lib.php",
                            type: "POST",
                            data: info,
                            success: function(data){
                                //console.log(data);
                                //alert('successfully edited!!!!!!!!!!!!!!!');
                                    $('.card').before('<div class="alert alert-success" role="alert">Forms are assigned Successfully and an email is sent to the relevent parents!</div>');
                            }
                        });

                    }
                });


            });


        </script>


        <?php
        echo ' 
        <div class="row">
            <div class="offset-lg-2 col-lg-6" style="padding: 50px;">
                <div class="card" style="padding: 20px;">
                    <form method="post" name="assignSubmission" id="assignSubmission">
                       <div class="text-center">
                          <h1 class="h4 text-gray-900 mb-4">Assign Submission!</h1>
                       </div>
                        
                        <div id="createSelectOptionsInside">
                        
                        </div>
                        
                        
                        
                        
                        <div id="AllSubmissions" style=" padding: 10px;
border: 3px solid;">
                            <div class="form-group" id="" style="">
                           <label for="exampleInputEmail1">Form Selection</label>
                           <br>
                           <div class="dropdown">
                              <select name="formTypes" id="formTypes" class="btn btn-secondary dropdown-toggle">
                              <option class="dropdown-item" >Select one of the form</option>
                              <option class="dropdown-item" value="1">Child Enrolment Form</option>
                              <option class="dropdown-item" value="2">Regular/Rountine Outing/Transport Authorisation Form</option>
                              <option class="dropdown-item" value="3">Excursion/Transport Authorisation Form</option>
                              <option class="dropdown-item" value="4">Regular/Routine Outing/ Transportation Authorisation Form(School,Kinder, PreSchool)</option>
                              <option class="dropdown-item" value="5">Home and FDC Residence Regular/ Routine Outing/ Transportation Authorisation Form</option>
                            </select>
                            </div>
                        </div>
                        
                        <div class="form-group" id="" style="">
                           <label for="exampleInputEmail1">Submission which needed to be signed.</label>
                           <br>
                           <div class="dropdown" >
                              <select name="" id="submissions" class="btn btn-secondary dropdown-toggle">
                              <option class="dropdown-item" >Select submission</option>
                              
                            </select>
                            <div id="seeAllSubmissions"> </div>
                            </div>
                        </div>
                        
                        
                        </div>
                        <div class="form-group" style="" id="parent1">
                           <label for="exampleInputEmail1">Parent</label>
                           <br>
                           <div class="dropdown">
                              <select name="cars" id="parent1dropdown" class="btn btn-secondary dropdown-toggle">
                              </select>
                            </div>
                        </div>
                        
                      
                       <input type="submit" name="submitBtnLogin" id="submitBtnLogin" value="Assign" class="btn btn-success btn-user btn-block">
                       
                    </form>
                    <div id="completionTable" style="display: none;">
                    
                    </div>
                </div>
            </div>
        </div>
';
    }
    function assignSubmissionAdmin(){
        ?>
        <script>
                $(document).ready(function() {

                $.ajax({
                    url: "lib.php",
                    type: "POST",
                    data: {
                        page:'getAllEducators'
                    },
                    success: function (data) {
                        var result = jQuery.parseJSON(data);
                        $('#educatorsDropdown').append(`<option value="all" selected="">Select Educator</option>`);
                        $.each(result, function() {
                            var optionText= this.firstname+ ' '+this.lastname+' '+ this.email;
                            //alert(optionText);
                            $('#educatorsDropdown').append(`<option value="${this.id}">
                                           ${optionText}
                                      </option>`);
                        });
                    }
                });
                $("#educatorsDropdown").on('change', function() {
                    $("#seeAllSubmissions").html('');
                    $('#formAlreadyAssignError').remove();
                    $("#submissions").html('<option class="dropdown-item">Select submission</option>');
                    //$("#formTypes").
                    $('#formTypes option:contains("Select one of the form")').prop('selected', true);
                if ($(this).val() == 'all'){
                    $('#educatorNotSelectedError').remove();
                    $('#createSelectOptionsInside').before('<div id="educatorNotSelectedError" class="alert alert-danger" role="alert">Select An Educator! </div>');
                }else if ($(this).val() != 'all'){
                    $('#educatorNotSelectedError').remove();
                    $('#dataTable').dataTable().fnClearTable();
                        var educatorid=$(this).val();
                        $.ajax({
                        url: "lib.php",
                        type: "POST",
                        data: {
                        page:'getEducatorSubmission',
                        educatorid:educatorid
                    },
                        success: function (data) {
                        var result = jQuery.parseJSON(data);
                        console.log(result);
                     }
                    });
                }
            });


                $.ajax({
                    url: "lib.php",
                    type: "POST",
                    data: {
                        page:'getAllParents'
                    },
                    success: function (data) {
                        var result = jQuery.parseJSON(data);
                        $('#parent1dropdown').append(`<option class="dropdown-item" >Select one of the user</option>`);
                        $.each(result, function() {
                            var optionText= this.firstname+ ' '+this.lastname+' '+ this.email;
                            //alert(optionText);
                            $('#parent1dropdown').append(`<option value="${this.id}">
                                           ${optionText}
                                      </option>`);
                        });
                    }
                });
                //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                /////////////////////////////////////////////////////Get submission on Form Submission///////////////////////////////
                //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                    $('#formTypes').change(function (){

                    });
                $('#formTypes').change(function (){
                    $("#seeAllSubmissions").html('');
                    $('#formAlreadyAssignError').remove();
                    $('#submissions')
                        .find('option')
                        .remove()
                        .end();
                    //alert('abc');
                    var value=$( "#formTypes option:selected").val();
                    var educator=$("#educatorsDropdown").val();
                    if (educator == 'all'){
                        $('#educatorNotSelectedError').remove();
                        $('#createSelectOptionsInside').before('<div id="educatorNotSelectedError" class="alert alert-danger" role="alert">Select An Educator! </div>');
                    }else if (educator != 'all'){
                        $('#educatorNotSelectedError').remove();
                        //alert("EDUCATOR: "+educator);
                        $.ajax({
                            url: "lib.php",
                            type: "POST",
                            data: {
                                page:'getAllCompletedSubmissions',
                                form:value,
                                educatorid:educator
                            },
                            success: function (data) {
                                var result = jQuery.parseJSON(data);
                                //console.log(result);
                                $("#completionTable").html('');
                                $("#completionTable").css("display","block");
                                $("#completionTable").append('<br>');
                                $("#completionTable").append('<a href="" class="btn btn-google btn-block"><i class=""></i>Completed Submissions</a>');
                                $( result ).each(function( result ) {
                                    //console.log( this.formtitle + ': <a href="#">View Submission</a>');
                                    $("#completionTable").append('<h3 style="padding: 0px;">'+this.formtitle+' <a href="?page=educatorSubmissions" target="_blank" style="font-size: 14px;">View Submission</a></h3>');
                                });
                            }
                        });

                        $.ajax({
                            url: "lib.php",
                            type: "POST",
                            data: {
                                page:'getAllSubmissionOfTheForm',
                                form:value,
                                educatorid:educator
                            },
                            success: function (data) {
                                var result = jQuery.parseJSON(data);
                                console.log(result);

                                $('#submissions').append(`<option class="dropdown-item" >Select submission</option>`);
                                $.each(result, function() {
                                    var optionText= this.formtitle+ ' '+this.datetime;
                                    //alert(optionText);
                                    $('#submissions').append(`<option value="${this.submissionid}">${optionText}</option>`);
                                    //$('#submissions').after(`<input type="text" id="${this.submissionid}" value="${this.parent1id}" style="display:none;"/>`);
                                    $('#seeAllSubmissions').append(`<input type="text" id="${this.submissionid}" value="${this.parent1id}" style="display:none;"/>`);
                                });
                                //Submission changed
                                $("#submissions").on('change', function() {
                                    $('#formAlreadyAssignError').remove();
                                //get submission value
                                    var value=$( "#submissions option:selected").val();
                                //get value of submissionid
                                    var assignedParentid=$( "#"+value).val();
                                //copy from the same id of parent
                                    var parentDetails=$("#parent1dropdown option[value='"+assignedParentid+"']").text();
                                   // alert('this is assigned'+ parentDetails);
                                    if (parentDetails!="" && parentDetails!=null){
                                        //alert under submission
                                        $('#submissions').after('<div id="formAlreadyAssignError" class="alert alert-danger" role="alert">This submission is already assign to <b>'+parentDetails+'</b></div>');

                                    }

                                });


                            }
                        });
                    }
                    //alert(value);

                });

                $('#assignSubmission').submit(function(e) {
                    var errorCount=0;
                    e.preventDefault();
                    var educator=$("#educatorsDropdown").val();
                    var parent1=$("#parent1dropdown option:selected").val();
                    var parent1dropdowntext=$("#parent1dropdown option:selected").text();
                    if(parent1dropdowntext=="Select one of the user"){
                        $('#parent1error').remove();
                        $('#parent1dropdown').after('<div class="error" id="parent1error" style="padding-top:10px; margin:0px;"><p class="error" style="color:red; font-size:12px;margin:0px;">Please select one of the parents</p></div>');
                        errorCount++;
                    }else{
                        $('#parent1error').remove();
                    }

                    var formType=$("#formTypes option:selected").text();
                    if(formType=="Select one of the form"){
                        $('#formSelectionerror').remove();
                        $('#formTypes').after('<div class="error" id="formSelectionerror" style="padding-top:10px; margin:0px;"><p class="error" style="color:red; font-size:12px;margin:0px;">Please select one of the options</p></div>');
                        errorcount++;
                    }else{
                        $('#formSelectionerror').remove();
                    }

                    var submissions=$("#submissions option:selected").text();
                    if(submissions=="Select submission"){
                        $('#formSubmissionsrror').remove();
                        $('#submissions').after('<div class="error" id="formSubmissionsrror" style="padding-top:10px; margin:0px;"><p class="error" style="color:red; font-size:12px;margin:0px;">Please select one of the options</p></div>');
                        errorcount++;
                    }else{
                        $('#formSubmissionsrror').remove();
                    }

                    if (educator == 'all'){
                        $('#educatorNotSelectedError').remove();
                        $('#createSelectOptionsInside').before('<div id="educatorNotSelectedError" class="alert alert-danger" role="alert">Select An Educator! </div>');
                        errorcount++;
                    }else if (educator != 'all'){
                        $('#educatorNotSelectedError').remove();
                    }

                    if(errorCount==0){
                        var formType=$("#formTypes option:selected").val();
                        var submissions=$("#submissions option:selected").val();

                        var parent1=$("#parent1dropdown option:selected").val();
                        var info={
                            page:'assignSubmissionsToParent',
                            getNoOfParents:1,
                            parent1:parent1,
                            formType:formType,
                            submissions:submissions,
                            educatorid:educator
                        };

                        //console.log(info);
                        $.ajax({
                            url: "lib.php",
                            type: "POST",
                            data: info,
                            success: function(data){
                                //console.log(data);
                                //alert('successfully edited!!!!!!!!!!!!!!!');
                                if(data.includes('Success')){
                                    $('.card').before('<div class="alert alert-success" role="alert">Forms are assigned Successfully and an email is sent to the relevent parents!</div>');
                                }
                            }
                        });

                    }
                });


            });


        </script>


        <?php
        echo ' 
        <div class="row">
            <div class="offset-lg-3 col-lg-6" style="padding: 50px;">
                <div class="card" style="padding: 20px;">
                    <form method="post" name="assignSubmission" id="assignSubmission">
                       <div class="text-center">
                          <h1 class="h4 text-gray-900 mb-4">Assign Submission!</h1>
                       </div>
                       
                       <div class="row" id="educatorDropdownSection">
                            <div class="offset-1 col-xs-4">
                                <div class="form-group row" >
                                    <select class="browser-default custom-select form-select" id="educatorsDropdown">
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div id="createSelectOptionsInside">
                        
                        </div>
                        
                        <div class="form-group" style="" id="parent1">
                           <label for="exampleInputEmail1">Parent 1</label>
                           <br>
                           <div class="dropdown">
                              <select name="cars" id="parent1dropdown" class="btn btn-secondary dropdown-toggle">
                              </select>
                            </div>
                        </div>
                        
                        
                        <div id="AllSubmissions" style=" padding: 10px;
border: 3px solid;">
                            <div class="form-group" id="" style="">
                           <label for="exampleInputEmail1">Form Selection</label>
                           <br>
                           <div class="dropdown">
                              <select name="formTypes" id="formTypes" class="btn btn-secondary dropdown-toggle">
                              <option class="dropdown-item" >Select one of the form</option>
                              <option class="dropdown-item" value="1">Child Enrolment Form</option>
                              <option class="dropdown-item" value="2">Regular/Rountine Outing/Transport Authorisation Form</option>
                              <option class="dropdown-item" value="3">Excursion/Transport Authorisation Form</option>
                              <option class="dropdown-item" value="4">Regular/Routine Outing/ Transportation Authorisation Form(School,Kinder, PreSchool)</option>
                              <option class="dropdown-item" value="5">Home and FDC Residence Regular/ Routine Outing/ Transportation Authorisation Form</option>
                            </select>
                            </div>
                        </div>
                        
                        <div class="form-group" id="" style="">
                           <label for="exampleInputEmail1">Submission which needed to be signed.</label>
                           <br>
                           <div class="dropdown">
                              <select name="" id="submissions" class="btn btn-secondary dropdown-toggle">
                              <option class="dropdown-item" >Select submission</option>
                              
                            </select>
                            <div id="seeAllSubmissions">
                            
</div>
                            </div>
                        </div>
                        
                        
                        </div>
                        
                        
                      
                       <input type="submit" name="submitBtnLogin" id="submitBtnLogin" value="Assign" class="btn btn-success btn-user btn-block">
                       
                    </form>
                    <div id="completionTable" style="display: none;">
                    
                    </div>
                </div>
            </div>
        </div>
';

    }
    function deleteassignedforms(){
        global $pdo;
        echo $_GET['id'];
        $sql = "Delete from `formassignments` WHERE eduid=?";
        $stmt= $pdo->prepare($sql);
        $stmt->execute([$_GET['id']]);

        $URL="?page=allEducatorsAssignments&status=deleted";
        echo "<script type='text/javascript'>document.location.href='{$URL}';</script>";
        echo '<META HTTP-EQUIV="refresh" content="0;URL=' . $URL . '">';


    }
    function passwordGeneration(){
        global $pdo;
        try {
            $query = "select * from `user` JOIN formassignments where user.`id`=formassignments.eduid";
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $row   = $stmt->fetchAll(PDO::FETCH_ASSOC);
            //var_dump($row);
        } catch (PDOException $e) {
            echo "Error : ".$e->getMessage();
        }
        if (isset($_GET['status']) && $_GET['status']=="generated"){
            echo '<div class="alert alert-success" role="alert" id="userUpdationStatus" style="display: block;">
                    The password is generated successfully
                </div>';
        }elseif (isset($_GET['status']) && $_GET['status']=="deleted"){
            echo '<div class="alert alert-danger" role="alert" id="userUpdationStatus" style="display: block;">
                    The password is deleted successfully
                </div>';
        }
        ?>
        <h1 class="h3 mb-4 text-gray-800" style="text-align: center; padding-top: 30px;">Password Generation/Password(For forms)</h1>
        <!-- Page Heading -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary" style="text-align: center;">You can edit,update and delete passwords through this panel
                        </h6>
                    </div>
                    <div class="card-body border-bottom-success">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                <tr>
                                    <th style="display:none;">Id</th>
                                    <th>First Name</th>
                                    <th>Last Name</th>
                                    <th>Email</th>
                                    <th>Password For Forms</th>
                                    <th>Role</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tfoot>
                                <tr>
                                    <th style="display:none;">Id</th>
                                    <th>First Name</th>
                                    <th>Last Name</th>
                                    <th>Email</th>
                                    <th>Password For Forms</th>
                                    <th>Role</th>
                                    <th>Action</th>
                                </tr>
                                </tfoot>
                                <tbody>
                                <?php
                                foreach ($row as $data)
                                {
                                    ?>
                                    <!-- Modal for adding a new password -->
                                    <div class="modal fade" id="generatingPasswordConfirmation<?php echo $data["eduid"]?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    Are you sure about generating a new password for this user?
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
                                                    <a href="?page=generatepassword&eduid=<?php echo $data["eduid"]?>&gobackto=passwordGeneration" class="btn btn-danger" title="Generate New Password">
                                                        <span class="text">I'm sure about it</span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--/////////////////////// Modal for adding a new password -->
                                    <!-- Modal for deleting password -->
                                    <div class="modal fade" id="deletingPassword<?php echo $data["eduid"]?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    Are you sure about deleting password for this user?
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
                                                    <a href="?page=deletePassword&eduid=<?php echo $data["eduid"]?>" class="btn btn-danger" title="Delete Password">
                                                        <span class="text">I'm sure about it</span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--/////////////////////// Modal for deleting password -->
                                    <tr>
                                        <td style="display:none;"><?php echo $data["id"];?></td>
                                        <td><?php echo $data["firstname"];?></td>
                                        <td><?php echo $data["lastname"];?></td>
                                        <td><?php echo $data["email"];?></td>
                                        <td><?php
                                            if(empty($data["formPassword"])){?>

                                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#generatingPasswordConfirmation<?php echo $data["eduid"]?>">
                                                    <span class="text"><i class="fa fa-plus"></i> Generate  Password</span>
                                                </button>
                                                <!-- Button trigger modal -->




                                            <?php } else{
                                                echo $data["formPassword"];
                                            }
                                            ?></td>
                                        <td><?php
                                            if($data["role"]=='2'){
                                                echo 'Educator';
                                            } ?></td>

                                        <td>
                                            <?php if(!empty($data["formPassword"])){?>
                                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#generatingPasswordConfirmation<?php echo $data["eduid"]?>">
                                                    <span class="text"><i class="fa fa-plus"></i> Generate New Password</span>
                                                </button>
                                                <button type="button" class="btn btn-danger btn-circle btn-md" data-toggle="modal" data-target="#deletingPassword<?php echo $data["eduid"]?>">
                                                    <span class="text"><i class="fas fa-trash"></i></span>
                                                </button>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php }?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <?php
    }
    function generatepassword($gobackto,$eduid){
        global $pdo;
        //echo "na karso g";
        //var_dump($eduid);
        $password=generateRandomString();
        try {
            $query = "SELECT * FROM `formassignments` where `eduid`=:eduid";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam('eduid', $eduid, PDO::PARAM_STR);
            $stmt->execute();
            $row   = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error : ".$e->getMessage();
        }
       // var_dump($row);
        if(!empty($row) && $row['formPassword']==''){
            echo "password exists";
            $sql = "UPDATE `formassignments` SET formPassword=? WHERE eduid=?";
            $stmt= $pdo->prepare($sql);
            $result = $stmt->execute([$password,$eduid]);
        }else{
            $query  = "INSERT INTO `formassignments`( `eduid`, `enrolmentformid`, `enrolmentformprefill`, `regularform`, `regularformprefill`, `excursionform`, `excursionformprefill`, `regularForSchoolForm`, `regularForSchoolFormPrefill`, `homeandfdcForm`, `homeandfdcFormPrefill`, `formPassword`)
            VALUES (:eduid,'','','','','','','','','','',:formPassword)";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam('eduid', $eduid, PDO::PARAM_STR);
            $stmt->bindParam('formPassword', $password, PDO::PARAM_STR);
            $stmt->execute();
            echo "nope";
        }



        if($gobackto=="assignformpage"){
            $URL="?page=assignformstoaneducator&eduid=".$eduid;
        }elseif($gobackto=="passwordGeneration"){
            $URL="?page=passwordGeneration";
        }

        echo "<script type='text/javascript'>document.location.href='{$URL}';</script>";
        echo '<META HTTP-EQUIV="refresh" content="0;URL=' . $URL . '">';
    }
    function deletePassword($eduid){
        global $pdo;
        $password='';
        $sql = "UPDATE `formassignments` SET formPassword=? WHERE eduid=?";
        $stmt= $pdo->prepare($sql);
        $result = $stmt->execute([$password,$eduid]);
        //echo "done";
        $URL="?page=passwordGeneration&status=deleted";
        echo "<script type='text/javascript'>document.location.href='{$URL}';</script>";
        echo '<META HTTP-EQUIV="refresh" content="0;URL=' . $URL . '">';
    }
    function createNewForm(){
        ?>
        <style>
            .card {
                /*position:absolute;*/
                /*top:50%;*/
                /*left:50%;*/
                /*transform:translate(-50%,-50%);*/
                width:200px;
                min-height:200px;
                background:#fff;
                box-shadow:0 20px 50px rgba(0,0,0,.1);
                border-radius:10px;
                transition:0.5s;
            }
            .card:hover {
                box-shadow:0 30px 70px rgba(0,0,0,.2);
            }
            .card .box {
                position:absolute;
                top:50%;
                left:0;
                transform:translateY(-50%);
                text-align:center;
                padding:20px;
                box-sizing:border-box;
                width:100%;
            }
            .card .box .i {
                width:120px;
                height:120px;
                margin:0 auto;
                border-radius:50%;
                overflow:hidden;
            }
            .card .box .img img {
                width:100%;
                height:100%;
            }
            .card .box h2 {
                font-size: 14px;
                color:#262626;
                margin:0px auto;
            }
            .card .box h2 span {
                font-size: 14px;
                background:#e91e63;
                color:#fff;
                display:inline-block;
                padding:4px 10px;
                border-radius:15px;
            }
            .card .box p {
                color:#262626;
            }
            .card .box span {
                display:inline-flex;
            }
            .card .box ul {
                margin:0;
                padding:0;
            }
            .card .box ul li {
                list-style:none;
                float:left;
            }
            .card .box ul li a {
                display:block;
                color:#aaa;
                margin:0 10px;
                font-size:20px;
                transition:0.5s;
                text-align:center;
            }
            .card .box ul li:hover a {
                color:#e91e63;
                transform:rotateY(360deg);
            }
        </style>

        <?php
        global $pdo;
        if (isset($_GET['status']) && $_GET['status']=="created"){
            echo '<div class="alert alert-success" role="alert" id="userUpdationStatus" style="display: block;">
                    The new form entry is created. Visit "<a href="/?page=educatorSubmissions"><b> view all submissions</b></a>" to assign to the parent/guardian.
                </div>';
        }elseif (isset($_GET['status']) && $_GET['status']=="deleted"){
            echo '<div class="alert alert-danger" role="alert" id="userUpdationStatus" style="display: block;">
                    The password is deleted successfully
                </div>';
        }elseif (isset($_GET['message']) && $_GET['message']=="notfound"){
            echo '<div class="alert alert-danger" role="alert" id="userUpdationStatus" style="display: block;">
                    Form not found
                </div>';
        }

        try {
            $query = "SELECT * FROM `formassignments` where `eduid`=:eduid";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam('eduid', $_SESSION['userid'], PDO::PARAM_STR);
            $stmt->execute();
            $row   = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error : ".$e->getMessage();
        }
        // var_dump($row);
        if(empty($row) || $row['formPassword']==''){
            echo 'Please check with your admin to assign you forms and password';
            exit(0);
        }

        ?>
        <div class="row">
            <div class="offset-lg-1 col-lg-8">
                <div class="card-body p-0">
                    <!-- Nested Row within Card Body -->
                    <div class="row">
                        <div class="col-lg-12">
                            <h1 style="text-align: center;">Fill up form and then you can assign to the required parent.</h1>
                        </div>
                    </div>

                    <div class="container-fluid">
                        <br>
                        <div class="row">
                                    <div class="col-xs-6" style="margin-right: 10px;">
                                        <div class="card">
                                            <div class="box">
                                                <div class="img">
                                                    <i class="fa fa-edit" style="font-size: 30px;"></i>
                                                </div><br>
                                                <h2>Child Enrolment Form
                                                    <br>
                                                    <br>
                                                    <a href="?page=createform&form=1"> <span>Fill NOW</span></a>
                                                </h2>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xs-6" style="margin-right: 10px;">
                                        <div class="card">
                                            <div class="box">
                                                <div class="img">
                                                    <i class="fa fa-edit" style="font-size: 30px;"></i>
                                                </div><br>
                                                <h2>Regular/Rountine Outing/Transport Authorisation Form
                                                    <br>
                                                    <br>
                                                    <a href="?page=createform&form=2"> <span>Fill NOW</span></a>
                                                </h2>
                                            </div>
                                        </div>
                                    </div>

                                <div class="col-xs-6" style="margin-right: 10px;">
                                    <div class="card">
                                        <div class="box">
                                            <div class="img">
                                                <i class="fa fa-edit" style="font-size: 30px;"></i>
                                            </div><br>
                                            <h2>Excursion/Transport Authorisation Form
                                                <br>
                                                <br>
                                                <a href="?page=createform&form=3"> <span>Fill NOW</span></a>
                                            </h2>
                                        </div>
                                    </div>
                                </div>

                            <div class="col-xs-6" style="margin-right: 10px;">
                                <div class="card">
                                    <div class="box">
                                        <div class="img">
                                            <i class="fa fa-edit" style="font-size: 30px;"></i>
                                        </div><br>
                                        <h2>Regular/Routine Outing/ Transportation Authorisation Form(School,Kinder, PreSchool)
                                            <br>
                                            <br>
                                            <a href="?page=createform&form=4"> <span>Fill NOW</span></a>
                                        </h2>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xs-3" style="margin-right: 10px;">
                                <div class="card">
                                    <div class="box">
                                        <div class="img">
                                            <i class="fa fa-edit" style="font-size: 30px;"></i>
                                        </div><br>
                                        <h2>Home and FDC Residence Regular/ Routine Outing/ Transportation Authorisation Form
                                            <br>
                                            <br>
                                            <a href="?page=createform&form=5"> <span>Fill NOW</span></a>
                                        </h2>
                                    </div>
                                </div>
                            </div>



                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php
    }
    function fillForm($form){
        global $pdo;
        if (isset($_GET['eduid'])){
            try {
                $query = "SELECT * FROM `formassignments` JOIN user where user.`id`=:id and `formassignments`.`eduid`=:eduid";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam('id', $_GET['eduid'], PDO::PARAM_STR);
                $stmt->bindParam('eduid', $_GET['eduid'], PDO::PARAM_STR);
                $stmt->execute();
                $row   = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                echo "Error : ".$e->getMessage();
            }
        }else{
            try {
                $query = "SELECT * FROM `formassignments` JOIN user where user.`id`=:id and `formassignments`.`eduid`=:eduid";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam('id', $_SESSION['userid'], PDO::PARAM_STR);
                $stmt->bindParam('eduid', $_SESSION['userid'], PDO::PARAM_STR);
                $stmt->execute();
                $row   = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                echo "Error : ".$e->getMessage();
            }
        }
        if($form==1){
            $formid=$row['enrolmentformid'];
            $prefilllink=$row['enrolmentformprefill'];
            if($formid==""){
                $URL = "https://portallocal.brightbeginningsfdcc.com.au/?page=createNewForm&message=notfound";
                echo "<script type='text/javascript'>document.location.href='{$URL}';</script>";
                echo '<META HTTP-EQUIV="refresh" content="0;URL=' . $URL . '">';
            }
        }elseif ($form==2){
            $formid=$row['regularform'];
            $prefilllink=$row['regularformprefill'];
            if($formid==""){
                $URL = "https://portallocal.brightbeginningsfdcc.com.au/?page=createNewForm&message=notfound";
                echo "<script type='text/javascript'>document.location.href='{$URL}';</script>";
                echo '<META HTTP-EQUIV="refresh" content="0;URL=' . $URL . '">';
            }
        }elseif ($form==3){
            $formid=$row['excursionform'];
            $prefilllink=$row['excursionformprefill'];
            if($formid==""){
                $URL = "https://portallocal.brightbeginningsfdcc.com.au/?page=createNewForm&message=notfound";
                echo "<script type='text/javascript'>document.location.href='{$URL}';</script>";
                echo '<META HTTP-EQUIV="refresh" content="0;URL=' . $URL . '">';
            }
        }elseif ($form==4){
            $formid=$row['regularForSchoolForm'];
            $prefilllink=$row['regularForSchoolFormPrefill'];
            if($formid==""){
                $URL = "https://portallocal.brightbeginningsfdcc.com.au/?page=createNewForm&message=notfound";
                echo "<script type='text/javascript'>document.location.href='{$URL}';</script>";
                echo '<META HTTP-EQUIV="refresh" content="0;URL=' . $URL . '">';
            }
        }elseif ($form==5){
            $formid=$row['homeandfdcForm'];
            $prefilllink=$row['homeandfdcFormPrefill'];
            if($formid==""){
                $URL = "https://portallocal.brightbeginningsfdcc.com.au/?page=createNewForm&message=notfound";
                echo "<script type='text/javascript'>document.location.href='{$URL}';</script>";
                echo '<META HTTP-EQUIV="refresh" content="0;URL=' . $URL . '">';
            }
        }else{
            $formid=$form;
        }
        ?>
        <div class="row row-cards">
            <div class="col-md-12 col-xl-12">
                <div class="row row-cards">
                    <div class="col-6 border-0">
                        <div class="card">
                            <div class="card-body" style="border: 2px #01bc70 dashed;">
                                <h4 class="" style="text-align: center; padding-top: 30px;">Email</h4>
                                <p style="text-align: center;font-size: 24px;"><?php echo $row['email'];?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 border-0">
                        <div class="card">
                            <div class="card-body" style="border: 2px #01bc70 dashed;">
                                <h4 class="" style="text-align: center; padding-top: 30px; ">Password</h4>
                                <p style="text-align: center; font-size: 24px;"><?php echo $row['formPassword'];?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php if(isset($prefilllink)){

            ?>
            <script type="text/javascript" src="https://form.jotform.com//jsform/<?php echo $formid;?>/prefill/<?php echo $prefilllink;?>"></script>
            <?php }else{?>
            <script type="text/javascript" src="https://form.jotform.com/jsform/<?php echo $formid;?>"></script>
            <?php }?>
        <?php
    }
    function fillFormAdmin(){
        ?>
        <style>
            .card {
                /*position:absolute;*/
                /*top:50%;*/
                /*left:50%;*/
                /*transform:translate(-50%,-50%);*/
                width:200px;
                min-height:200px;
                background:#fff;
                box-shadow:0 20px 50px rgba(0,0,0,.1);
                border-radius:10px;
                transition:0.5s;
            }
            .card:hover {
                box-shadow:0 30px 70px rgba(0,0,0,.2);
            }
            .card .box {
                position:absolute;
                top:50%;
                left:0;
                transform:translateY(-50%);
                text-align:center;
                padding:20px;
                box-sizing:border-box;
                width:100%;
            }
            .card .box .i {
                width:120px;
                height:120px;
                margin:0 auto;
                border-radius:50%;
                overflow:hidden;
            }
            .card .box .img img {
                width:100%;
                height:100%;
            }
            .card .box h2 {
                font-size:20px;
                color:#262626;
                margin:0px auto;
            }
            .card .box h2 span {
                font-size:14px;
                background:#e91e63;
                color:#fff;
                display:inline-block;
                padding:4px 10px;
                border-radius:15px;
            }
            .card .box p {
                color:#262626;
            }
            .card .box span {
                display:inline-flex;
            }
            .card .box ul {
                margin:0;
                padding:0;
            }
            .card .box ul li {
                list-style:none;
                float:left;
            }
            .card .box ul li a {
                display:block;
                color:#aaa;
                margin:0 10px;
                font-size:20px;
                transition:0.5s;
                text-align:center;
            }
            .card .box ul li:hover a {
                color:#e91e63;
                transform:rotateY(360deg);
            }
        </style>
        <?php
        global $pdo;
        if (isset($_GET['status']) && $_GET['status']=="created"){
            echo '<div class="alert alert-success" role="alert" id="userUpdationStatus" style="display: block;">
                    The new form entry is created. Visit "<b> view all submissions</b>" to assign to the parent/guardian.
                </div>';
        }elseif (isset($_GET['status']) && $_GET['status']=="deleted"){
            echo '<div class="alert alert-danger" role="alert" id="userUpdationStatus" style="display: block;">
                    The password is deleted successfully
                </div>';
        }
            ?>
    <script>
            $(document).ready(function() {
                $.ajax({
                    url: "lib.php",
                    type: "POST",
                    data: {
                        page:'getAllEducators'
                    },
                    success: function (data) {
                        var result = jQuery.parseJSON(data);
                        $('#educatorsDropdown').append(`<option value="" selected="">Select Educator</option>`);
                        $.each(result, function() {
                            var optionText= this.firstname+ ' '+this.lastname+' '+ this.email;
                            //alert(optionText);
                            $('#educatorsDropdown').append(`<option value="${this.id}">
                                           ${optionText}
                                      </option>`);
                        });
                    }
                });
                $("#educatorsDropdown").on('change', function() {
                    if ($(this).val() != 'Select Educator'){
                        var educatorid=$(this).val();

                        $.ajax({
                            url: "lib.php",
                            type: "POST",
                            data: {
                                page:'getEducatorForms',
                                educatorid:educatorid
                            },
                            success: function (data) {
                                var result = jQuery.parseJSON(data);
                                //console.log(result);
                                if(result==false){
                                    $('#educatorFormNotFoundError').remove();
                                    $('#educatorDropdownSection').after('<div id="educatorFormNotFoundError" class="alert alert-danger" role="alert">No Forms have been allocated to this educator yet!</div>');
                                    $("#forms").css('display','none');
                                }else{
                                    $('#educatorFormNotFoundError').remove();
                                    $("#forms").css('display','block');

                                    $("#form1").attr("href", "?page=createform&form=1&eduid="+educatorid);
                                    $("#form2").attr("href", "?page=createform&form=2&eduid="+educatorid);
                                    $("#form3").attr("href", "?page=createform&form=3&eduid="+educatorid);
                                    $("#form4").attr("href", "?page=createform&form=4&eduid="+educatorid);
                                    $("#form5").attr("href", "?page=createform&form=5&eduid="+educatorid);
                                }
                    }
                    });
                }
                });

            });
    </script>
            <div class="row" id="educatorDropdownSection">
                <div class="offset-1 col-xs-4">
                    <div class="form-group row" >
                        <select class="browser-default custom-select form-select" id="educatorsDropdown">

                        </select>
                    </div>
                </div>
            </div>
        <div class="row" id="forms" style="display:none;">
            <div class="offset-lg-1 col-lg-8">
                <div class="card-body p-0">
                    <!-- Nested Row within Card Body -->
                    <div class="row">
                        <div class="col-lg-12">
                            <h1 style="text-align: center;">Fill up form and then you can assign to the required parent.</h1>
                        </div>
                    </div>

                    <div class="container-fluid">
                        <br>
                        <div class="row">
                            <div class="col-xs-6" style="margin-right: 10px;">
                                <div class="card">
                                    <div class="box">
                                        <div class="img">
                                            <i class="fa fa-edit" style="font-size: 30px;"></i>
                                        </div><br>
                                        <h2>Child Enrolment Form
                                            <br>
                                            <br>
                                            <a href="?page=createform&form=1&eduid=" id="form1"> <span>Fill NOW</span></a>
                                        </h2>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xs-6" style="margin-right: 10px;">
                                <div class="card">
                                    <div class="box">
                                        <div class="img">
                                            <i class="fa fa-edit" style="font-size: 30px;"></i>
                                        </div><br>
                                        <h2>Regular/Rountine Outing/Transport Authorisation Form
                                            <br>
                                            <br>
                                            <a href="?page=createform&form=2" id="form2"> <span>Fill NOW</span></a>
                                        </h2>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xs-6" style="margin-right: 10px;">
                                <div class="card">
                                    <div class="box">
                                        <div class="img">
                                            <i class="fa fa-edit" style="font-size: 30px;"></i>
                                        </div><br>
                                        <h2>Excursion/Transport Authorisation Form
                                            <br>
                                            <br>
                                            <a href="?page=createform&form=3" id="form3"> <span>Fill NOW</span></a>
                                        </h2>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xs-6" style="margin-right: 10px;">
                                <div class="card">
                                    <div class="box">
                                        <div class="img">
                                            <i class="fa fa-edit" style="font-size: 30px;"></i>
                                        </div><br>
                                        <h2>Regular/Routine Outing/ Transportation Authorisation Form(School,Kinder, PreSchool)
                                            <br>
                                            <br>
                                            <a href="?page=createform&form=4" id="form4"> <span>Fill NOW</span></a>
                                        </h2>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xs-3" style="margin-right: 10px;">
                                <div class="card">
                                    <div class="box">
                                        <div class="img">
                                            <i class="fa fa-edit" style="font-size: 30px;"></i>
                                        </div><br>
                                        <h2>Home and FDC Residence Regular/ Routine Outing/ Transportation Authorisation Form
                                            <br>
                                            <br>
                                            <a href="?page=createform&form=5" id="form5"> <span>Fill NOW</span></a>
                                        </h2>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php
    }
    function viewEducatorSubmissions(){
        if(isset($_GET['status']) && $_GET['status']=="deleted"){?>
            <div class="card mb-4 py-3 border-left-danger" style="padding-top:0px !important;padding-bottom:0px !important; ">
                <div class="card-body" id="msg">
                    Form Submission is deleted!
                </div>
            </div>
        <?php }
        global $pdo;
        try {
            $eduid=$_GET['eduid'];
            //formassignments.id as formassignmentsid
            $query = "SELECT * FROM `formassignments` JOIN user where user.id=formassignments.eduid";
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $row   = $stmt->fetchAll(PDO::FETCH_ASSOC);
            //var_dump($row);
        } catch (PDOException $e) {
            echo "Error : ".$e->getMessage();
        }

        ?>
        <?php if(isset($_GET['eduid'])){
            ?>
            <script>
                $(document).ready(function() {
                    $("#educatorSelector").click();
                    $("#educatorSelector").val(<?php echo $_GET['eduid']; ?>);
                    var educatorid=<?php echo $_GET['eduid']; ?>;
                    $.ajax({
                        url: "lib.php",
                        type: "POST",
                        data: {
                            educatorid:educatorid,
                            page:'getEducatorRegisteredForms'
                        },
                        success: function (data) {
                            //$('.statusMsg').html(data+'<div class="alert alert-success" role="alert">Added Successfully!</div>');
                            var result = jQuery.parseJSON(data);
                            //console.log(result);
                            //alert(data);
                            if(result!=0){
                                //alert(result.id);
                                $('#recordid').val(result.id);
                                $('#enrolmentformid').val(result.enrolmentformid);
                                $('#enrolmentformprefill').val(result.enrolmentformprefill);
                                $('#regularform').val(result.regularform);
                                $('#regularformprefill').val(result.regularformprefill);
                                $('#excursionform').val(result.excursionform);
                                $('#excursionformprefill').val(result.excursionformprefill);
                                $('#regularForSchoolForm').val(result.regularForSchoolForm);
                                $('#regularForSchoolFormPrefill').val(result.regularForSchoolFormPrefill);
                                $('#homeandfdcForm').val(result.homeandfdcForm);
                                $('#homeandfdcFormPrefill').val(result.homeandfdcFormPrefill);
                            }else{
                                $('#recordid').val('');
                                $('#enrolmentformid').val('');
                                $('#enrolmentformprefill').val('');
                                $('#regularform').val('');
                                $('#regularformprefill').val('');
                                $('#excursionform').val('');
                                $('#excursionformprefill').val('');
                                $('#regularForSchoolForm').val('');
                                $('#regularForSchoolFormPrefill').val('');
                                $('#homeandfdcForm').val('');
                                $('#homeandfdcFormPrefill').val('');
                            }
                        }
                    });
                    $('#educatornotselectederror').remove();
                });
            </script>

            <?php
        }?>

        <h1 class="h3 mb-4 text-gray-800" style="text-align: center; padding-top: 30px;">All Submissions</h1>
        <div class="form-group" id="educatordropdowndiv">
            <input type="text" class="form-control" id="recordid" placeholder="" name="recordid" value="" style="display: none;">
            <label for="exampleInputEmail1">Educator</label><br>
            <select id="educatorSelector" name="educator" class="dropdown mb-4 btn btn-primary dropdown-toggle">
                <option value="0">Select the educator</option>
                <?php foreach($row as $var) {
                    echo '<option value="'.$var['id'].'">'.$var['firstname'].' '.$var['lastname'].'</option>';
                }?>
            </select>

        </div>
        <!-- Page Heading -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">You can edit,update and delete assigned forms through this panel</h6>
                    </div>
                    <div class="card-body border-bottom-success">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                <tr>
                                    <th style="display:none;">Id</th>
                                    <th>Educator Name</th>
                                    <th>Enrolment Form Id</th>
                                    <th>Enrolment Form Prefill</th>
                                    <th>Regular/Rountine Outing/Transport Authorisation Form</th>
                                    <th>prefill</th>
                                    <th>Excursion/Transport Authorisation Form</th>
                                    <th>prefill</th>
                                    <th>Regular/Routine Outing/ Transportation Authorisation Form(School,Kinder, PreSchool)</th>
                                    <th>prefill</th>
                                    <th>Home and FDC Residence Regular/ Routine Outing/ Transportation Authorisation Form</th>
                                    <th>prefill</th>
                                    <th>Forms Password</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tfoot>
                                <tr>
                                    <th style="display:none;">Id</th>
                                    <th>Educator Name</th>
                                    <th>Enrolment Form Id</th>
                                    <th>Enrolment Form Prefill</th>
                                    <th>Regular/Rountine Outing/Transport Authorisation Form</th>
                                    <th>prefill</th>
                                    <th>Excursion/Transport Authorisation Form</th>
                                    <th>prefill</th>
                                    <th>Regular/Routine Outing/ Transportation Authorisation Form(School,Kinder, PreSchool)</th>
                                    <th>prefill</th>
                                    <th>Home and FDC Residence Regular/ Routine Outing/ Transportation Authorisation Form</th>
                                    <th>prefill</th>
                                    <th>Forms Password</th>
                                    <th>Action</th>
                                </tr>
                                </tfoot>
                                <tbody>
                                <?php
                                foreach ($row as $data)
                                {
                                    ?>
                                    <!-- Modal for deleting password -->
                                    <div class="modal fade" id="deletingPassword<?php echo $data["eduid"]?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="exampleModalLabel">Delete submission</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    Are you sure about deleting forms record permanently.?
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
                                                    <a href="?page=deleteassignedforms&id=<?php echo $data["eduid"];?>" class="btn btn-danger" title="Delete Password">
                                                        <span class="text">I'm sure about it</span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--/////////////////////// Modal for deleting password -->
                                    <tr>
                                        <td style="display:none;"><?php echo $data["id"];?></td>
                                        <td><?php echo $data["firstname"].' '.$data["lastname"];?></td>
                                        <td><?php echo $data["enrolmentformid"];?></td>
                                        <td><?php echo $data["enrolmentformprefill"]; ?></td>
                                        <td><?php echo $data["regularform"];?></td>
                                        <td><?php echo $data["regularformprefill"];?></td>
                                        <td><?php echo $data["excursionform"];?></td>
                                        <td><?php echo $data["excursionformprefill"];?></td>
                                        <td><?php echo $data["regularForSchoolForm"];?></td>
                                        <td><?php echo $data["regularForSchoolFormPrefill"];?></td>
                                        <td><?php echo $data["homeandfdcForm"];?></td>
                                        <td><?php echo $data["homeandfdcFormPrefill"];?></td>
                                        <td><?php echo $data["formPassword"]; ?></td>
                                        <td>
                                            <a href="?page=viewEducatorSubmissions&eduid=<?php echo $data["eduid"];?>" class="btn btn-success btn-circle btn-md" id="edit<?php echo $data["eduid"];?>">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="?page=assignformstoaneducator&eduid=<?php echo $data["eduid"];?>" class="btn btn-primary btn-circle btn-md" id="edit<?php echo $data["eduid"];?>">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-danger btn-circle btn-md" data-toggle="modal" data-target="#deletingPassword<?php echo $data["eduid"]?>">
                                                <span class="text"><i class="fas fa-trash"></i></span>
                                            </button>
                                        </td>
                                    </tr>
                                <?php }?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <?php
    }
    function deleteSubmission(){
        $subid=$_GET["id"];
        global $pdo,$api_key;
        $sql = "Delete FROM `submissions` WHERE submissionid=?";
        $stmt= $pdo->prepare($sql);
        $stmt->execute([$subid]);

        $jotformObj=new JotForm($api_key);
        $jotformObj->deleteSubmission($subid);

        $URL="?page=educatorSubmissions&status=deleted";
        echo "<script type='text/javascript'>document.location.href='{$URL}';</script>";
        echo '<META HTTP-EQUIV="refresh" content="0;URL=' . $URL . '">';

    }
    function fillforms(){
        global $pdo;
        $query = "SELECT * FROM `educatordefaultforms`";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $row2   = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (isset($_GET['status']) && $_GET['status']=="created"){
            $eduid=$_SESSION['userid'];
            $query = "UPDATE `educatorformsubmissions` SET eduid=:eduid Where submissionid=:id";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam('eduid', $eduid, PDO::PARAM_STR);
            $stmt->bindParam('id', $_GET['submissionid'], PDO::PARAM_STR);
            $stmt->execute();

            echo '<div class="alert alert-success" role="alert" id="userUpdationStatus" style="display: block;">
                    The form is submitted successfully.
                </div>';
        }

        ?>
        <style>
            .card {
                /*position:absolute;*/
                /*top:50%;*/
                /*left:50%;*/
                /*transform:translate(-50%,-50%);*/
                width:200px;
                min-height:200px;
                background:#fff;
                box-shadow:0 20px 50px rgba(0,0,0,.1);
                border-radius:10px;
                transition:0.5s;
            }
            .card:hover {
                box-shadow:0 30px 70px rgba(0,0,0,.2);
            }
            .card .box {
                position:absolute;
                top:50%;
                left:0;
                transform:translateY(-50%);
                text-align:center;
                padding:20px;
                box-sizing:border-box;
                width:100%;
            }
            .card .box .i {
                width:120px;
                height:120px;
                margin:0 auto;
                border-radius:50%;
                overflow:hidden;
            }
            .card .box .img img {
                width:100%;
                height:100%;
            }
            .card .box h2 {
                font-size:20px;
                color:#262626;
                margin:0px auto;
            }
            .card .box h2 span {
                font-size:14px;
                background:#e91e63;
                color:#fff;
                display:inline-block;
                padding:4px 10px;
                border-radius:15px;
            }
            .card .box p {
                color:#262626;
            }
            .card .box span {
                display:inline-flex;
            }
            .card .box ul {
                margin:0;
                padding:0;
            }
            .card .box ul li {
                list-style:none;
                float:left;
            }
            .card .box ul li a {
                display:block;
                color:#aaa;
                margin:0 10px;
                font-size:20px;
                transition:0.5s;
                text-align:center;
            }
            .card .box ul li:hover a {
                color:#e91e63;
                transform:rotateY(360deg);
            }
        </style>
        <div class="row">
            <div class="offset-lg-1 col-lg-8">
                <div class="card-body p-0">
                    <!-- Nested Row within Card Body -->
                    <div class="row">
                        <div class="col-lg-12">
                            <h1 style="text-align: center;">Fill up form and then you can assign to the required parent.</h1>
                        </div>
                    </div>

                    <div class="container-fluid">
                        <br>
                        <div class="row">
                            <?php
                            foreach ($row2 as $data)
                            {
                            ?>
                            <div class="col-xs-3" style="margin-right: 10px;">
                                <div class="card">
                                    <div class="box">
                                        <div class="img">
                                            <i class="fa fa-edit" style="font-size: 30px;"></i>
                                        </div><br>
                                        <h2><?php echo $data['title'];?>
                                            <br>
                                            <br>
                                            <a href="?page=createform&form=<?php echo $data['formid'];?>"> <span>Fill NOW</span></a>
                                        </h2>
                                    </div>
                                </div>
                            </div>
                            <?php }?>


                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    function signedAgreementsAndForms(){
        global $api_key;
        if(isset($_GET['status']) && $_GET['status']=="deleted"){?>
            <div class="card mb-4 py-3 border-left-danger" style="padding-top:0px !important;padding-bottom:0px !important; ">
                <div class="card-body" id="msg">
                    Form Submission is deleted!
                </div>
            </div>
        <?php }
        global $pdo;
        try {
            $role=$_SESSION['role'];
            if($role==1){
                $query = "SELECT * FROM `educatorformsubmissions` JOIN user where user.id=educatorformsubmissions.eduid";
                $stmt = $pdo->prepare($query);
                $stmt->execute();
                $row   = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }elseif ($role==2){
                //formassignments.id as formassignmentsid
                $eduid=$_SESSION['userid'];
                $query = "SELECT * FROM `educatorformsubmissions` JOIN user where user.id=:userid1 and educatorformsubmissions.eduid=:userid2";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam('userid1', $eduid, PDO::PARAM_STR);
                $stmt->bindParam('userid2', $eduid, PDO::PARAM_STR);
                $stmt->execute();
                $row   = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            //var_dump($row);
        } catch (PDOException $e) {
            echo "Error : ".$e->getMessage();
        }
        function getFormName($formid){
            global $pdo;
            $query = "SELECT * FROM `educatordefaultforms` where formid=:formid";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam('formid', $formid, PDO::PARAM_STR);
            $stmt->execute();
            $row   = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['title'];
        }
        ?>
        <h1 style="text-align: center;">List of Submissions</h1>
        <!--        <a href='?page=assignSubmissions'>-->
        <!--        <a href="?page=addmissingsubmission&amp;form=1" class="btn btn-primary "> -->
        <!--            <span class="icon text-white-50"> -->
        <!--                <i class="fas fa-plus"></i> -->
        <!--            </span> -->
        <!--            <span class="text">Add missing submission</span> -->
        <!--        </a> -->

        <!-- Page Heading -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">You can edit,update and delete assigned forms through this panel</h6>
                    </div>
                    <div class="card-body border-bottom-success">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                <tr>
                                    <th style="display:none;">Id</th>
                                    <th>Form Name</th>
                                    <th>Date and time</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tfoot>
                                <tr>
                                    <th style="display:none;">Id</th>
                                    <th>Form Name</th>
                                    <th>Date and time</th>
                                    <th>Action</th>
                                </tr>
                                </tfoot>
                                <tbody>
                                <?php

                                foreach ($row as $data)
                                {
                                    ?>
                                    <!-- Modal for deleting password -->
                                    <div class="modal fade" id="deletingPassword<?php echo $data["id"];?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    Are you sure about deleting forms record for this educator?
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
                                                    <a href="?page=deleteSubmission&id=<?php echo $data["submissionid"];?>" class="btn btn-danger" title="Delete Password">
                                                        <span class="text">I'm sure about it</span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--/////////////////////// Modal for deleting password -->
                                    <tr>
                                        <td style="display:none;"><?php echo $data["id"];?></td>
                                        <td><?php echo getFormName($data["formid"]);?></td>
                                        <td><?php echo $data["datetime"]; ?></td>

                                        <td>
                                            <?php if($data["completionstatus"]==1){?>
                                                <a href="https://api.jotform.com/pdf-converter/<?php echo $data["formid"]; ?>/fill-pdf?download=1&submissionID=<?php echo $data["submissionid"];?>&apikey=<?php echo $api_key;?>" class="text-reset"  download>
                                                    <i class="fas fa-file-pdf" style="font-size:40px;color:red"></i>
                                                </a>
                                            <?php }else{
                                                echo "-";
                                            } ?>
                                        </td>
                                    </tr>
                                <?php }?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <?php

    }
    public function addNewDefaultForm(){
        global $pdo;
        if (isset($_GET['formid'])) {
            $query = "SELECT * FROM `educatordefaultforms` where formid=:id";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam('id', $_GET['formid'], PDO::PARAM_STR);
            $stmt->execute();
            $row   = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        ?>
        <script type="text/javascript" src="js/jquery-3.6.0.min.js"></script>
        <script type="text/javascript">
            $(document).ready(function() {

                $('#addNewForm').submit(function(e) {

                    e.preventDefault();
                    $("#formtitleerror").remove();
                    $("#formidError").remove();
                    $("#errorblock").css("display","none");
                    $("#messageblock").css("display","none");
                    var formtitle = $('#title').val();
                    var formid = $('#formid').val();
                    $(".error").remove();
                    var errorCount=0;

                    if (formtitle.length < 1) {
                        $('#formtitle').after('<div class="error"  id="formtitleerror" style="padding-top:10px;margin:0px;"><p class="error" style="color:red; font-size:12px;margin:0px;">This field is required</p></div>');
                        errorCount++;
                    }
                    if (formid.length < 1) {
                        $('#formidError').after('<div class="error"  id="formidError" style="padding-top:10px;margin:0px;"><p class="error" style="color:red; font-size:12px;margin:0px;">This field is required</p></div>');
                        errorCount++;
                    }
                var statusOfUpdationOrInsertion=$("#statusOfUpdationOrInsertion").val();

                    if(errorCount==0){
                        var role=$("#role option:selected").val();
                        $.ajax({
                            url: "lib.php",
                            type: "POST",
                            data: {
                                page:'addNewDefaultForm',
                                formtitle:formtitle,
                                formid:formid,
                                statusOfUpdationOrInsertion:statusOfUpdationOrInsertion
                            },
                            success: function(data){
                                    $("#messageblock").css("display","block");
                                    $('#msg').html("New Form is added successfully");
                                    $('#addNewForm').find('input').val('');

                            }
                        });

                    }
                });

            });
        </script>
        <!-- Page Heading -->
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <div class="p-5">
                    <div class="jumbotron bg-gray-200 border-bottom-success">
                        <div class="text-center">
                            <h1 class="h4 text-gray-900 mb-4">Add New Form For All Educators</h1>
                        </div>
                        <form class="user" id="addNewForm" method="post" action="">
                            <input type="text" id="statusOfUpdationOrInsertion" value="<?php if(isset($_GET['formid'])){echo $_GET['formid'];}else{echo 0;}?>" style="display: none;"/>
                            <div class="form-group">
                                <input type="text" class="form-control form-control-user" id="title"
                                       placeholder="Form Title" name="title" value="<?php if(isset($_GET['formid'])){echo $row["title"];}?>">
                            </div>
                            <div class="form-group">
                                <input type="text" class="form-control form-control-user" id="formid"
                                       placeholder="Form id" name="formid" value="<?php if(isset($_GET['formid'])){echo $row["formid"];}?>">
                            </div>

                            <input type="submit" name="submitBtnDefault" id="submitBtnDefault" value="<?php if(isset($_GET['formid'])){echo "Update Now";}else{echo "Add Now";}?>" class="btn btn-primary btn-user btn-block" />

                            <div id="messageblock" style="padding-top:10px; display: none; ">
                                <div class="card mb-4 py-3 border-left-success" style="padding-top:0px !important;padding-bottom:0px !important; ">
                                    <div class="card-body" style="color:#1cc88a" id="msg">
                                    </div>
                                </div>
                            </div>

                            <div id="errorblock" style="padding-top:10px; display: none; ">
                                <div class="card mb-4 py-3 border-left-danger" style="padding-top:0px !important;padding-bottom:0px !important; ">
                                    <div class="card-body" style="color:red" id="errormsg">
                                    </div>
                                </div>
                            </div>

                            <hr>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    function allDefaultFormSubmissions(){
        global $api_key;
        global $pdo;
        if (isset($_GET['status']) && $_GET['status']=="deleted"){
            echo '<div class="alert alert-danger" role="alert" id="userUpdationStatus" style="display: block;">
                    Form Submission is deleted!
                </div>';
        }

        global $pdo;
        try {
            $role=$_SESSION['role'];
            if($role==1){
                $query = "SELECT * FROM `educatorformsubmissions` JOIN user where user.id=educatorformsubmissions.eduid";
                $stmt = $pdo->prepare($query);
                $stmt->execute();
                $row   = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            //var_dump($row);
        } catch (PDOException $e) {
            echo "Error : ".$e->getMessage();
        }
        function getFormName($formid){
            global $pdo;
            $query = "SELECT * FROM `educatordefaultforms` where formid=:formid";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam('formid', $formid, PDO::PARAM_STR);
            $stmt->execute();
            $row   = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['title'];
        }
        ?>


        <script>
            $(document).ready(function() {

                $.ajax({
                    url: "lib.php",
                    type: "POST",
                    data: {
                        page:'getAllEducators'
                    },
                    success: function (data) {
                        var result = jQuery.parseJSON(data);
                        $('#educatorsDropdown').append(`<option value="all" selected="">All Submissions</option>`);
                        $.each(result, function() {
                            var optionText= this.firstname+ ' '+this.lastname+' '+ this.email;
                            //alert(optionText);
                            $('#educatorsDropdown').append(`<option value="${this.id}">
                                           ${optionText}
                                      </option>`);
                        });
                    }
                });
                $("#educatorsDropdown").on('change', function() {
                    if ($(this).val() == 'all'){
                        alert('please select one of the educators');
                    }else if ($(this).val() != 'all'){
                        $('#dataTable').dataTable().fnClearTable();
                        var educatorid=$(this).val();
                        $.ajax({
                            url: "lib.php",
                            type: "POST",
                            data: {
                                page:'getEducatorDefaultSubmissions',
                                educatorid:educatorid
                            },
                            success: function (data) {
                                var result = jQuery.parseJSON(data);
                                //console.log(result);

                                oTable = $('#dataTable').dataTable();

                                function Add(id,name,formname,submissionid,dateandtime,status,action){
                                    //alert(result);
                                    var data = [
                                        id,name,formname,submissionid,dateandtime,status,action
                                    ];

                                    oTable.fnAddData(data);
                                };
                                $( result ).each(function() {
                                    //alert(this.id);
                                    var id=this.id;
                                    var name=this.firstname +' '+this.lastname;
                                    var educator=this.educatorformsubmissionsid;
                                    var formname=this.formtitle;
                                    var formid=this.formid;
                                    var submissionid=this.submissionid;
                                    var dateandtime=this.datetime;
                                    var completionstatus=this.completionstatus;

                                    var formname=formname+ '(' +this.formid+")";

                                    if(this.completionstatus==1){
                                        var status='<a href="#" class="btn btn-success btn-circle btn-md" id=""><i class="fas fa-check"></i></a>';
                                    }else if(this.completionstatus==0) {
                                        var status='<a href="#" class="btn btn-danger btn-circle btn-md" id=""><i class="fas fa-times"></i></a>';
                                    }else {
                                        var status="-";
                                    }
                                    if(this.completionstatus==1){
                                        var withCond='<a href="https://api.jotform.com/pdf-converter/'+this.formid+'/fill-pdf?download=1&submissionID='+this.submissionid+'&apikey=<?php global $api_key; echo $api_key;?>" class="text-reset"  download><i class="fas fa-file-pdf" style="font-size:40px;color:red"></i></a>';
                                    }else{
                                        var withCond='';
                                    }


                                    var withoutConditionAction2 ='<a href="?page=sign&formid='+this.formid+'&submissionid='+this.submissionid+'" class="btn btn-primary btn-circle btn-md" id="edit'+this.eduid+'"><i class="fas fa-edit"></i></a>';
                                    var withoutConditionAction3=' <button type="button" class="btn btn-danger btn-circle btn-md" data-toggle="modal" data-target="#deletingPassword'+this.eduid+'">' +'<span class="text"><i class="fas fa-trash"></i></span></button>';
                                    var action=withCond+withoutConditionAction2+withoutConditionAction3;

                                    Add(this.id,name,formname,submissionid,dateandtime,status,action);
                                });
                            }
                        });
                    }
                });

            });
        </script>
        <h1 style="text-align: center;">List of Submissions</h1>

        <div class="row" id="educatorDropdownSection">
            <div class="offset-1 col-xs-4">
                <div class="form-group row" >
                    <select class="browser-default custom-select form-select" id="educatorsDropdown">
                    </select>
                </div>
            </div>
        </div>

        <!-- Page Heading -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">You can edit,update and delete assigned forms through this panel</h6>
                    </div>
                    <div class="card-body border-bottom-success">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                <tr>
                                    <th style="">Id</th>
                                    <th>Educator Name</th>
                                    <th>Form name(Form ID)</th>
                                    <th>Submission Id</th>
                                    <th>Date and time</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tfoot>
                                <tr>
                                    <th style="">Id</th>
                                    <th>Educator Name</th>
                                    <th>Form name(Form ID)</th>
                                    <th>Submission Id</th>
                                    <th>Date and time</th>
                                    <th>Action</th>
                                </tr>
                                </tfoot>
                                <tbody>
                                <?php
                                foreach ($row as $data)
                                {
                                    ?>
                                    <!-- Modal for deleting password -->
                                    <div class="modal fade" id="deletingPassword<?php echo $data["id"];?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    Are you sure about deleting forms record for this educator?
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
                                                    <a href="?page=deleteSubmission&id=<?php echo $data["submissionid"];?>" class="btn btn-danger" title="Delete Password">
                                                        <span class="text">I'm sure about it</span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--/////////////////////// Modal for deleting password -->
                                    <tr>
                                        <td style=""><?php echo $data["id"];?></td>
                                        <td><?php echo $data["firstname"].' '.$data["lastname"];?></td>
                                        <td><?php echo getFormName($data["formid"]); echo "(".$data["formid"].")";
                                            ?></td>
                                        <td><?php echo $data["submissionid"];?></td>
                                        <td><?php echo $data["datetime"]; ?></td>
                                        <td>

                                            <?php if($data["completionstatus"]==1){?>
                                                <a href="https://api.jotform.com/pdf-converter/<?php echo $data["formid"]; ?>/fill-pdf?download=1&submissionID=<?php echo $data["submissionid"];?>&apikey=<?php echo $api_key;?>" class="text-reset"  download>
                                                    <i class="fas fa-file-pdf" style="font-size:40px;color:red"></i>
                                                </a>
                                            <?php } ?>
                                            <a href="?page=sign&formid=<?php echo $data["formid"];?>&submissionid=<?php echo $data["submissionid"];?>" class="btn btn-primary btn-circle btn-md" id="edit'<?php echo $data["eduid"];?>'">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-danger btn-circle btn-md" data-toggle="modal" data-target="#deletingPassword<?php echo $data["id"];?>">
                                                <span class="text"><i class="fas fa-trash"></i></span>
                                            </button>
                                        </td>
                                    </tr>
                                <?php }?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <?php
    }
    function allDefaultAssignedForm(){
            if(isset($_GET['status']) && $_GET['status']=="deleted"){?>
                <div class="card mb-4 py-3 border-left-danger" style="padding-top:0px !important;padding-bottom:0px !important; ">
                    <div class="card-body" id="msg">
                        Forms Assignment is deleted!
                    </div>
                </div>
            <?php }else if (isset($_GET['status']) && $_GET['status']=="update"){
                echo '<div class="alert alert-success" role="alert" id="userUpdationStatus" style="display: block;">
                    Form is update Successfully!
                </div>';
            }
            global $pdo;
            try {
                //formassignments.id as formassignmentsid
                $query = "SELECT * FROM `educatordefaultforms`";
                $stmt = $pdo->prepare($query);
                $stmt->execute();
                $row   = $stmt->fetchAll(PDO::FETCH_ASSOC);
                //var_dump($row);
            } catch (PDOException $e) {
                echo "Error : ".$e->getMessage();
            }

            ?>


            <h1 class="h3 mb-4 text-gray-800" style="text-align: center; padding-top: 30px;">All Educators Default Form Assignments</h1>
            <!-- Page Heading -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">You can edit,update and delete assigned forms through this panel</h6>
                        </div>
                        <div class="card-body border-bottom-success">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                    <tr>
                                        <th style="">Id</th>
                                        <th>Form title</th>
                                        <th>Form id</th>
                                        <th>Action</th>
                                    </tr>
                                    </thead>
                                    <tfoot>
                                    <tr>
                                        <th style="">Id</th>
                                        <th>Form title</th>
                                        <th>Form id</th>
                                        <th>Action</th>
                                    </tr>
                                    </tfoot>
                                    <tbody>
                                    <?php
                                    foreach ($row as $data)
                                    {
                                        ?>
                                        <!-- Modal for deleting password -->
                                        <div class="modal fade" id="deletingPassword<?php echo $data["defaultformid"];?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        Are you sure about deleting forms record for this educator?
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
                                                        <a href="?page=deleteassigneddefaultforms&id=<?php echo $data["defaultformid"];?>" class="btn btn-danger" title="Delete Password">
                                                            <span class="text">I'm sure about it</span>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!--/////////////////////// Modal for deleting password -->
                                        <tr>
                                            <td style=""><?php echo $data["defaultformid"];?></td>
                                            <td><?php echo $data["title"];?></td>
                                            <td><?php echo $data["formid"]; ?></td>
                                            <td>
                                                <a href="?page=addNewDefaultForm&formid=<?php echo $data["formid"];?>" class="btn btn-primary btn-circle btn-md" id="edit<?php echo $data["formid"];?>">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-danger btn-circle btn-md" data-toggle="modal" data-target="#deletingPassword<?php echo $data["defaultformid"];?>">
                                                    <span class="text"><i class="fas fa-trash"></i></span>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php }?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <?php
    }
    function deleteassigneddefaultforms(){
        global $pdo;
        $formid=$_GET['id'];
        $sql = "Delete from educatordefaultforms WHERE defaultformid=?";
        $stmt= $pdo->prepare($sql);
        $stmt->execute([$formid]);

        $URL="http://portallocal.brightbeginningsfdcc.com.au/portal?page=allDefaultAssignedForm&status=deleted";
        echo "<script type='text/javascript'>document.location.href='{$URL}';</script>";
        echo '<META HTTP-EQUIV="refresh" content="0;URL=' . $URL . '">';
    }
}
class Parents{
     function assignedSubmissions(){
         global $pdo;
         $parentid=$_SESSION['userid'];
         $role=$_SESSION['role'];
         if (isset($_GET["submissionid"])&&isset($_GET["status"]) &&$_GET["status"]=="updated" && $role==3){

             $query  = "UPDATE `submissions` SET `completionstatus`=1 Where submissionid=:submissionid";
             $stmt = $pdo->prepare($query);
             $stmt->bindParam('submissionid', $_GET['submissionid'], PDO::PARAM_STR);
             $stmt->execute();
             echo '<div class="alert alert-success" role="alert">Your entry have been updated successfully!</div>';
             //send email to BBFDCC AND EDUCATOR that form is successfully filled.
//        try {
//            $query = "SELECT * FROM `submissions` where `submissionid`=:subId";
//            $stmt = $pdo->prepare($query);
//            $stmt->bindParam('subId', $_GET["submissionid"], PDO::PARAM_STR);
//            $stmt->execute();
//            $row   = $stmt->fetch(PDO::FETCH_ASSOC);
//
//            $query = "select * from `user` where `id`=:eduId";
//            $stmt = $pdo->prepare($query);
//            $stmt->bindParam('eduId', $row['eduid'], PDO::PARAM_STR);
//            $stmt->execute();
//            $row   = $stmt->fetch(PDO::FETCH_ASSOC);
//            $educatorname=$row['firstname'].' '.$row['lastname'];
//            $email=$row['email'];
//        } catch (PDOException $e) {
//            echo "Error : ".$e->getMessage();
//        }
//             //send_email($educatorname,$Email)
//             //$email="info@bbfdcc.com.au
//             //$name="BBFDCC"
//
//             function send_email($educatorname,$Email){
//                 $to = $Email;
//                 $subject = "Form Signed";
//                 $txt  = '<html><body>';
//                 $txt .= '<h1 style="font-size:18px;">Hi,</h1>';
//                 $txt .= '<p style="font-size:18px;">A form have been filled for'.$educatorname.' </p>';
//                 $txt .= '<a href="https://www.brightbeginningsfdcc.com.au/portal"> <p style="color:#080;font-size:24px;">Click Here to log in to view signed form.</p></a>';
//                 $txt .= '<p style="font-size:18px;">Thanks</p>';
//                 $txt.= '<p style="font-size:18px;">Bright Beginnings Family Day Care Team</p>';
//                 $txt .= '</body></html>';
//                 $headers = "MIME-Version: 1.0" . "\r\n";
//                 $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
//                 $headers .= "From: info@bbfdcc.com.au. \r\n";
//                 mail($to,$subject,$txt,$headers);
//             }
         }
//         else{
//             $URL="http://portallocal.brightbeginningsfdcc.com.au/portal?page=educatorSubmissions";
//             echo "<script type='text/javascript'>document.location.href='{$URL}';</script>";
//             echo '<META HTTP-EQUIV="refresh" content="0;URL=' . $URL . '">';
//         }

//         $query = "SELECT * FROM `submissions` where `parent1id`=:parent1id ";
//         $stmt = $pdo->prepare($query);
//         $stmt->bindParam('parent1id', $parentid, PDO::PARAM_STR);
//         $stmt->execute();
//         $row   = $stmt->fetchAll(PDO::FETCH_ASSOC);
         ?>
         <style>
             .card {
                 /*position:absolute;*/
                 /*top:50%;*/
                 /*left:50%;*/
                 /*transform:translate(-50%,-50%);*/
                 width:200px;
                 min-height:200px;
                 background:#fff;
                 box-shadow:0 20px 50px rgba(0,0,0,.1);
                 border-radius:10px;
                 transition:0.5s;
             }
             .card:hover {
                 box-shadow:0 30px 70px rgba(0,0,0,.2);
             }
             .card .box {
                 position:absolute;
                 top:50%;
                 left:0;
                 transform:translateY(-50%);
                 text-align:center;
                 padding:20px;
                 box-sizing:border-box;
                 width:100%;
             }
             .card .box .i {
                 width:120px;
                 height:120px;
                 margin:0 auto;
                 border-radius:50%;
                 overflow:hidden;
             }
             .card .box .img img {
                 width:100%;
                 height:100%;
             }
             .card .box h2 {
                 font-size:20px;
                 color:#262626;
                 margin:0px auto;
             }
             .card .box h2 span {
                 font-size:14px;
                 background:#e91e63;
                 color:#fff;
                 display:inline-block;
                 padding:4px 10px;
                 border-radius:15px;
             }
             .card .box p {
                 color:#262626;
             }
             .card .box span {
                 display:inline-flex;
             }
             .card .box ul {
                 margin:0;
                 padding:0;
             }
             .card .box ul li {
                 list-style:none;
                 float:left;
             }
             .card .box ul li a {
                 display:block;
                 color:#aaa;
                 margin:0 10px;
                 font-size:20px;
                 transition:0.5s;
                 text-align:center;
             }
             .card .box ul li:hover a {
                 color:#e91e63;
                 transform:rotateY(360deg);
             }
         </style>
         <div class="container-fluid">
             <div style="padding: 20px;"> <h2>Need to be Signed!</h2> </div>
             <br>
             <div class="row">

                 &nbsp;
                 <?php
                 //var_dump($row);
                 $query = "SELECT * FROM `submissions` where `parent1id`=:parent1id ";
                 $stmt = $pdo->prepare($query);
                 $stmt->bindParam('parent1id', $parentid, PDO::PARAM_STR);
                 $stmt->execute();
                 $row   = $stmt->fetchAll(PDO::FETCH_ASSOC);
                 //var_dump($row);
                 foreach ($row as $value) {
                     ?>
                   <?php if(($value["parent1id"]==$parentid && $value["completionstatus"]!=1) ){ ?>
                 <div class="col-xs-3" style="margin-right: 10px;">
                     <div class="card">
                         <div class="box">
                             <div class="img">
                                 <i class="fa fa-edit" style="font-size: 30px;"></i>
                             </div>
                             <h2><?php echo $value['formtitle'];?><br>
                                <a href="?page=sign&formid=<?php echo $value['formid'];?>&submissionid=<?php echo $value['submissionid'];?>"> <span>Sign NOW</span></a>
                             </h2>
                         </div>
                     </div>
                 </div>
                     <?php }?>
                <?php }?>
             </div>

             <div style="padding: 20px;"> <h2>Already Signed Forms⬇</h2> </div>
             <br>
             <div class="row">

                 &nbsp;
                 <?php foreach ($row as $value) {
                     ?>
                     <?php if(($value["parent1id"]==$parentid && $value["completionstatus"]==1) ){ ?>
                         <div class="col-xs-3" style="margin-right: 10px;">
                             <div class="card">
                                 <div class="box">
                                     <div class="img">
                                         <i class="fa fa-check" style="font-size: 30px;color: green;"></i>
                                     </div>
                                     <h2><?php echo $value['formtitle'];?><br>
                                     </h2>
                                 </div>
                             </div>
                         </div>
                     <?php }?>

                 <?php }?>
             </div>
         </div>

         <?php
     }
     function sign($link){
         $role=$_SESSION['role'];
         $userid=$_SESSION['userid'];
         global $pdo;

         if ($role==2){
             try {
                 $query = "SELECT * FROM `formassignments` JOIN user where user.`id`=:id and `formassignments`.`eduid`=:eduid";
                 $stmt = $pdo->prepare($query);
                 $stmt->bindParam('id', $userid, PDO::PARAM_STR);
                 $stmt->bindParam('eduid', $userid, PDO::PARAM_STR);
                 $stmt->execute();
                 $row   = $stmt->fetch(PDO::FETCH_ASSOC);
             } catch (PDOException $e) {
                 echo "Error : ".$e->getMessage();
             }
         ?>
         <div class="row row-cards">
             <div class="col-md-12 col-xl-12">
                 <div class="row row-cards">
                     <div class="col-6 border-0">
                         <div class="card">
                             <div class="card-body" style="border: 2px #01bc70 dashed;">
                                 <h4 class="" style="text-align: center; padding-top: 30px;">Email</h4>
                                 <p style="text-align: center;font-size: 24px;"><?php echo $row['email'];?></p>
                             </div>
                         </div>
                     </div>
                     <div class="col-6 border-0">
                         <div class="card">
                             <div class="card-body" style="border: 2px #01bc70 dashed;">
                                 <h4 class="" style="text-align: center; padding-top: 30px; ">Password</h4>
                                 <p style="text-align: center; font-size: 24px;"><?php echo $row['formPassword'];?></p>
                             </div>
                         </div>
                     </div>

                 </div>
             </div>
         </div>
             <?php }?>
         <div class="row">
             <iframe id="JotFormIFrame"onload="window.parent.scrollTo(0,0)"allowtransparency="true"src="https://www.jotform.com/edit/<?php echo $link; ?>"
                     frameborder="0" style="width:100%;  height: 3000px; border:none;" scrolling="yes"> </iframe>
         </div>
         <?php
     }
}
/*-/////////////////-----v2.0 changes for adding more forms//////////----*/

?>