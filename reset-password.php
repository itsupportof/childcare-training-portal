<?php
session_start();
if (isset($_SESSION['currentSession'])) {
    header("Location: /");
}
/*****************************************
 * 'Reset password'
 * ****************************************
 */
$email = $_GET['email'];
require 'config.php';
global $pdo;
$query3 = "select * from `passreset` where `email`=:username";
$stmt3 = $pdo->prepare($query3);
$stmt3->bindParam('username', $email, PDO::PARAM_STR);
$stmt3->execute();
$row3 = $stmt3->fetch(PDO::FETCH_ASSOC);
$count2 = $stmt3->rowCount();
$currentDate = date("Y-m-d");
if ($count2 == 0 || $row3['token'] != $_GET['key']) {
    echo '<h1>URL is expired please try reseting again!</h1>';
    exit(0);
} else if ($row3['timestamp'] < $currentDate) {
    echo '<h1>This link is expired please try reseting again!</h1>';
    exit(0);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>BBFDCC - Reset Password</title>

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
            href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
            rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        .bg-password-image {
            background: url(https://www.brightbeginningsfdcc.com.au/portal/img/loginpage.png) !important;
            background-position: center;
            background-size: contain !important;
        }
    </style>
    <script src="js/jquery-3.6.0.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {

            $('#updationform').submit(function(e) {
                e.preventDefault();
                $("#errorblock").css("display","none");
                $("#messageblock").css("display","none");

                var password = $('#Password').val();
                var repeatPassword = $('#RepeatPassword').val();
                var email=$('#email').val();
                var errorCount=0;

                $(".error").remove();

                if (password.length < 8 ) {
                    $('#passworderror').append('<div class="error" style="padding-top:10px;margin:0px;margin:0px;"><p class="error" style="color:red; font-size:12px;">Password must be at least 8 characters</p></div>');
                    errorCount++;
                }else if(password!=repeatPassword){
                    $('#passworderror').append('<div class="error" style="padding-top:10px;margin:0px;margin:0px;"><p class="error" style="color:red; font-size:12px;">Try again. Password does not match.</p></div>');
                    errorCount++;
                }


                if(errorCount==0){

                    //alert(email);
                    var info={
                        resetPassEmail:email,
                        Password: password,
                    }
                    //alert(info);
                    $.ajax({
                        url: "lib.php",
                        type: "POST",
                        data: info,
                        success: function(data){
                            alert(data);
                            var a = data.includes("Success");
                            if (a) {
                                $("#messageblock").css("display","block");
                                $('#msg').html(data);
                                $('#updationform').find('input').val('')
                                window.setTimeout(function() {
                                    window.location.href='login.php';
                                }, 5000);
                            } else {
                                $("#errorblock").css("display","block");
                                $('#errormsg').html(data);
                            }

                        },error: function(xhr, status, error) {
                            var err = eval("(" + xhr.responseText + ")");
                            alert(err.Message);
                        }
                    });
                }
            });
        });
    </script>
</head>

<body class="bg-gradient-primary">

<div class="container">

    <!-- Outer Row -->
    <div class="row justify-content-center">

        <div class="col-xl-10 col-lg-12 col-md-9">

            <div class="card o-hidden border-0 shadow-lg my-5">
                <div class="card-body p-0">
                    <!-- Nested Row within Card Body -->
                    <div class="row">
                        <div class="col-lg-6 d-none d-lg-block bg-password-image"></div>
                        <div class="col-lg-6">
                            <div class="p-5">
                                <div class="text-center">
                                    <div class="sidebar-brand-text mx-3" style="padding: 20px;"><img src="img/bbfdc logo.png" width="150px;"></div>
                                </div>
                                <div class="text-center">
                                    <h1 class="h4 text-gray-900 mb-2">Reset password </h1>
                                    <p class="mb-4">Enter your new password.</p>
                                </div>
                                <form id="updationform"  method="post">
                                    <input type="email" class="form-control form-control-user" id="email" placeholder="" name="email" value="<?php echo $_GET['email'];?>" style="display: none;">
                                    <div class="card-body">
                                        <div class="form-group row mb-3">
                                            <div class="mb-3">
                                                <div class="input-group input-group-flat">
                                                    <input type="password" class="form-control form-control-user" id="Password" placeholder="Password" name="password">
                                                    <span class="input-group-text">
                                        <a href="#" class="link-secondary" title="" data-bs-toggle="tooltip" data-bs-original-title="Show password" onclick="pass();"><!-- Download SVG icon from http://tabler-icons.io/i/eye -->
                                          <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><circle cx="12" cy="12" r="2"></circle><path d="M22 12c-2.667 4.667 -6 7 -10 7s-7.333 -2.333 -10 -7c2.667 -4.667 6 -7 10 -7s7.333 2.333 10 7"></path></svg>
                                      </a>
                                    </span>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <div class="input-group input-group-flat">
                                                    <input type="password" class="form-control form-control-user" id="RepeatPassword" placeholder="Repeat Password" name="repeatpassword">
                                                    <span class="input-group-text">
                                    <a href="#" class="link-secondary" title="" data-bs-toggle="tooltip" data-bs-original-title="Show password" onclick="pass2();"><!-- Download SVG icon from http://tabler-icons.io/i/eye -->
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><circle cx="12" cy="12" r="2"></circle><path d="M22 12c-2.667 4.667 -6 7 -10 7s-7.333 -2.333 -10 -7c2.667 -4.667 6 -7 10 -7s7.333 2.333 10 7"></path></svg>
                                    </a>
                                    </span>
                                                </div>
                                            </div>
                                            <div id="passworderror" style="margin-left: 20px;">
                                            </div>
                                        </div>
                                        <div class="form-footer">
                                            <button class="btn btn-primary w-100" type="submit"  name="submitBtnRegister" id="submitBtnRegister">
                                                <!-- Download SVG icon from http://tabler-icons.io/i/mail -->
                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><rect x="3" y="5" width="18" height="14" rx="2"></rect><polyline points="3 7 12 13 21 7"></polyline></svg>
                                                Update Now
                                            </button>
                                        </div>
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
                                    </div>
                                </form>
                                <hr>
                                <div class="text-center">
                                    <a class="small" href="https://www.brightbeginningsfdcc.com.au/portal/register.php">Create an Account!</a>
                                </div>
                                <div class="text-center">
                                    <a class="small" href="https://www.brightbeginningsfdcc.com.au/portal/login.php">Already have an account? Login!</a>
                                </div>
                                <div class="text-center">
                                    <a class="small" href="https://brightbeginningsfdcc.com.au/">Go back to Bright Beginnings Website</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>

<!-- Bootstrap core JavaScript-->
<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

<!-- Core plugin JavaScript-->
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>

<!-- Custom scripts for all pages-->
<script src="js/sb-admin-2.min.js"></script>
<script>
    function pass() {
        var x = document.getElementById("Password");
        if (x.type === "password") {
            x.type = "text";
        } else {
            x.type = "password";
        }
    }
    function pass2() {
        var x = document.getElementById("RepeatPassword");
        if (x.type === "password") {
            x.type = "text";
        } else {
            x.type = "password";
        }
    }
</script>
</body>

</html>
