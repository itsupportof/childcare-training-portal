<?php
session_start();
if (isset($_SESSION['currentSession'])) {
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

    <title>BBFDCC - Forgot Password</title>

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
                                    <h1 class="h4 text-gray-900 mb-2">Forgot Your Password?</h1>
                                    <p class="mb-4">We get it, stuff happens. Just enter your email address below
                                        and we'll send you a link to reset your password!</p>
                                </div>
                                <form  method="post">


                                    <input type="email" class="form-control" placeholder="Enter email"name="email">
                                    <br>
                                    <button class="btn btn-primary w-100" type="submit"  name="submitBtnRegister" id="submitBtnRegister">
                                        <!-- Download SVG icon from http://tabler-icons.io/i/mail -->
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><rect x="3" y="5" width="18" height="14" rx="2"></rect><polyline points="3 7 12 13 21 7"></polyline></svg>
                                        Send a link
                                    </button>
                                    <span class="loginMsg" style="text-align:center;color:red;padding:10px;display: none">
                                                <?php echo @$msg;?></span>
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

</body>

</html>
