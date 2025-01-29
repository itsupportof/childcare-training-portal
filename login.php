<?php

session_start();
// var_dump($_SESSION);

if(isset($_SESSION['currentSession'])){
    header("Location: /");
}
require 'lib.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <noscript><h1 style="text-align:center; font-size:2em; padding-bottom:100000px; color:black; background:white; padding-top:400px;">JavaScript is off. Please enable to view full site.</h1></noscript>
    <title>BBFDC - Login</title>

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <script src="vendor/jquery/jquery.min.js"></script>
    <script type="text/javascript">
        //alert('m here 1');
        $(document).ready(function() {
           // alert('m here 1');
            $('#loginform').submit(function(e) {
                //alert('m hereads');
                e.preventDefault();
                $("#errorblock").css("display","none");
                $("#messageblock").css("display","none");
                var email = $('#email').val();
                var password = $('#password').val();
                var EmailregEx = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                var errorCount=0;
                $(".error").remove();
                if (email.length < 1) {
                    $('#email').after('<div class="error" style="padding-top:10px;margin:0px;"><p style="color:red; font-size:12px;margin:0px;">This field is required</p></div>');
                    errorCount++;
                } else {
                    var validEmail = EmailregEx.test(email);
                    if (!validEmail) {
                        $('#email').after('<div class="error" style="padding-top:10px;"margin:0px;><p class="error" style="color:red; font-size:12px;margin:0px;">Enter a valid email</p></div>');
                        errorCount++;
                    }
                }
                if (password.length < 0 ) {
                    $('#passworderror').append('<div class="error" style="padding-top:10px;margin:0px;margin:0px;"><p class="error" style="color:red; font-size:12px;">Password cannot be empty</p></div>');
                    errorCount++;
                }

                if(errorCount==0){
                    $.ajax({
                        url: "lib.php",
                        type: "POST",
                        data: {
                            Email: email,
                            Password: password,
                            loginProcess:1
                        },
                        success: function(data){
                            //alert(data);
                            if (data.includes("Success")) {
                                $("#messageblock").css("display","block");
                                $('#msg').html(data);
                                $('#registrationform').find('input').val('');
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
                            <div class="col-lg-6 d-none d-lg-block bg-login-image">

                            </div>
                            <div class="col-lg-6">
                                <div class="p-5">
                                <form class="user" id="loginform" method="post" action="">
                                        <div class="text-center">
                                            <div class="sidebar-brand-text mx-3" style="padding: 20px;"><img src="img/bbfdc logo.png" width="150px;"></div>

                                            <h1 class="h4 text-gray-900 mb-4">Welcome Back!</h1>
                                        </div>
                                        
                                            <div class="form-group">
                                                <input type="email" class="form-control form-control-user"
                                                    id="email" aria-describedby="emailHelp"
                                                    placeholder="Enter Email Address..." name="email">
                                            </div>
                                            <div class="form-group">
                                                <input type="password" class="form-control form-control-user"
                                                    id="password" placeholder="Password" name="password">
                                            </div>
                                            <div id="passworderror" style="margin-left: 20px;"></div>

                                        <input type="submit" name="submitBtnLogin" id="submitBtnLogin" value="Login" class="btn btn-primary btn-user btn-block" />
                                            <br>
                                           
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
                                        </form>
                                        <hr>
                                        <div class="text-center">
                                            <a class="small" href="forgot-password.php">Forgot Password?</a>
                                        </div>
                                        <div class="text-center">
                                            <a class="small" href="register.php">Create an Account!</a>
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
    
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>

</body>

</html>
