<?php
session_start();
//var_dump($_SESSION);
//exit(0);
include 'config.php';
global $url;
if ($_SESSION['currentSession'] != 1 ) {
    header('Location: login.php', true, 301);
    exit();
}
include('lib.php');
include('class.php');
$role=$_SESSION['role'];
if(!isset($_GET['page']) && isset($_SESSION['currentSession'])){
    $URL="?page=home";
    echo "<script type='text/javascript'>document.location.href='{$URL}';</script>";
    echo '<META HTTP-EQUIV="refresh" content="0;URL=' . $URL . '">';
}
//phpinfo();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="cache-control" content="no-cache" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="-1" />
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Bright Beginnings Family Day Care Centre</title>
    <link rel="shortcut icon" href="https://www.brightbeginningsfdcc.com.au/wp-content/uploads/2021/04/BRIGHT-BEGINNINGS-FAMILY-DAY-CARE-Logo.png" type="image/x-icon" />
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
            href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
            rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">
    <!--------------------------------------------------v1.0 changes for calender-------------------------->
    <?php if (isset($_GET['page']) && $_GET['page']=='viewAllEvents' ||($role!=3 && $_GET['page']=='home')){?>
        <!-- CSS -->
        <link rel="stylesheet" type="text/css" href="evo-calendar/css/evo-calendar.min.css">
        <link rel="stylesheet" type="text/css" href="evo-calendar/css/evo-calendar.orange-coral.min.css">
        <link rel="stylesheet" type="text/css" href="evo-calendar/css/evo-calendar.midnight-blue.min.css">
        <link rel="stylesheet" type="text/css" href="evo-calendar/css/evo-calendar.royal-navy.min.css">
        <link rel="stylesheet" type="text/css" href="evo-calendar/demo/demo.css">
        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Fira+Mono&display=swap" rel="stylesheet">
    <?php }?>
    <!--/////////////////////////////////////////////-----v1.0 changes for calender-------------------------->
    <!-- Custom styles for this page -->
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <script type="text/javascript" src="js/jquery-3.6.0.min.js"></script>
    <?php if($role==1){?>
    <!-- SweetAlert2 CSS and JavaScript -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
   <?php }?>
</head>

<body id="page-top">

<!-- Page Wrapper -->
<div id="wrapper">
    <?php
    include('header.php');
    ?>
    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">
        <!-- Main Content -->
        <div id="content">
            <!-- Topbar -->
            <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                <!-- Sidebar Toggle (Topbar) -->
                <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                    <i class="fa fa-bars"></i>
                </button>


                <!-- Topbar Navbar -->
                <ul class="navbar-nav ml-auto">
                    <div class="topbar-divider d-none d-sm-block"></div>
                    
                    <!-- Nav Item - User Information and notification -->
                    <?php 
                    notificationBar();
                    $b=userOption();echo $b;?>

                </ul>

            </nav>
            <!-- End of Topbar -->

            <!-- Begin Page Content -->
            <div class="container-fluid">
                <?php
                $page = $_GET['page'];
                /*-----------------v1.0 changes for calender-------------*/
                if($page == 'home'){
                    if($role==3){
                        $resources=new Resources();
                        $resources->viewAllResources();
                    }elseif($role==1 || $role==2){
                        $eventObj=new Event();
                        $eventObj->viewAllEvent();
                    }else{
                        echo '<h1>Unauthorised access</h1>';
                    }
                }else if($page == 'viewAllResources'){
                    if($role==1 || $role==2 || $role==3){
                        $resources=new Resources();
                        $resources->viewAllResources();
                    }else{
                        echo '<h1>Unauthorised access</h1>';
                    }
                }
                /*-/////////////////-----v1.0 changes for calender//////////----*/
                elseif($page == 'viewResource'){
                    if($role==1 || $role==2 || $role==3){
                        $resources=new Resources();
                        $resources->readResource();
                    }else{
                        echo '<h1>Unauthorised access</h1>';
                    }

                }elseif($page == 'addNewResource'){
                    if($role==1){
                        $resources=new Resources();
                        $resources->addNewResource();
                    }else{
                        echo '<h1>Unauthorised access</h1>';
                    }

                }elseif($page == 'editResource' || $page=='deleteResources'){
                    if($role==1){
                        $resources=new Resources();
                        $resources->editordeleteResource();
                    }else{
                        echo '<h1>Unauthorised access</h1>';
                    }

                }elseif($page== 'editingResource'){
                    if($role==1){
                        $resources=new Resources();
                        $resources->editAResource();
                    }else{
                        echo '<h1>Unauthorised access</h1>';
                    }

                }elseif($page== 'deleteAResource'){
                    if($role==1){
                        if (isset($_GET['id'])){
                            $resId=$_GET['id'];
                        }
                        $resources=new Resources();
                        $resources->deleteAResource($resId);
                    }else{
                        echo '<h1>Unauthorised access</h1>';
                    }

                }elseif($page == 'viewMeetings'){
                    if($role==1  || $role==2){
                        $meeting= new Meeting();
                        $meeting->viewAllMeetings();
                    }else{
                        echo '<h1>Unauthorised access</h1>';
                    }

                }elseif ($page=='viewMeetingDetails'){
                    if($role==1 || $role==2){
                        $mtid=$_GET['id'];
                        $meeting= new Meeting();
                        $meeting->viewMeetingDetails($mtid);
                    }else{
                        echo '<h1>Unauthorised access</h1>';
                    }

                }elseif ($page=='scheduleMeeting'){
                    if($role==1){
                        $meeting= new Meeting();
                        $meeting->scheduleMeeting();
                    }else{
                        echo '<h1>Unauthorised access</h1>';
                    }

                }elseif($page == 'updatingMeeting' || $page=='deleteMeeting'){
                    if($role==1){
                        $meeting= new Meeting();
                        $meeting->editordeleteMeeting();
                    }else{
                        echo '<h1>Unauthorised access</h1>';
                    }

                }elseif($page == 'editMeeting'){
                    if($role==1){
                        $userid=$_GET['id'];
                        $meeting= new Meeting();
                        $meeting->editMeeting($userid);
                    }else{
                        echo '<h1>Unauthorised access</h1>';
                    }

                }elseif($page == 'deleteMeetings'){
                    if($role==1){
                        $meetingId=$_GET['id'];
                        $meeting= new Meeting();
                        $meeting->deleteMeeting($meetingId);
                    }else{
                        echo '<h1>Unauthorised access</h1>';
                    }

                }elseif($page == 'currentUsers'){
                    if($role==1){
                        $userObj=new User();
                        $userObj->viewAllCurrentUsers();
                    }else{
                        echo '<h1>Unauthorised access</h1>';
                    }

                }elseif($page == 'editUser'){
                    if($role==1){
                        $userid=$_GET['user'];
                        $userObj=new User();
                        $userObj->editUser($userid);
                    }else{
                        echo '<h1>Unauthorised access</h1>';
                    }

                }elseif($page == 'deleteUser'){
                    if($role==1){
                        $source=$_GET['source'];
                        $userid=$_GET['user'];
                        $userObj=new User();
                        $userObj->deleteUser($userid,$source);
                    }else{
                        echo '<h1>Unauthorised access</h1>';
                    }

                }elseif($page == 'pendingUsers'){
                    if($role==1){
                        $userObj=new User();
                        $userObj->pendingUsers();
                    }else{
                        echo '<h1>Unauthorised access</h1>';
                    }

                }elseif($page == 'acceptUser'){
                    if($role==1){
                        $userid=$_GET['user'];
                        $userObj=new User();
                        $userObj->acceptUser($userid);
                    }else{
                        echo '<h1>Unauthorised access</h1>';
                    }

                }elseif($page == 'addNewUser'){
                    if($role==1){
                        $userObj=new User();
                        $userObj->addNewUser();
                    }else{
                        echo '<h1>Unauthorised access</h1>';
                    }

                }
                /*-----------------v1.0 changes for calender-------------*/
                elseif($page == 'viewAllEvents'){
                    if($role==1 || $role==2){
                        $eventObj=new Event();
                        $eventObj->viewAllEvent();
                    }else{
                        echo '<h1>Unauthorised access</h1>';
                    }
                }elseif($page == 'addNewEvent'){
                    if($role==1){
                        $eventObj=new Event();
                        $eventObj->addNewEvent();
                    }else{
                        echo '<h1>Unauthorised access</h1>';
                    }
                }elseif($page == 'editOrDeleteEvents'){
                    if($role==1){
                        $eventObj=new Event();
                        $eventObj->editOrDeleteEvent();
                    }else{
                        echo '<h1>Unauthorised access</h1>';
                    }
                }elseif ($page=='editEvent'){
                    if($role==1){
                        $eventId=$_GET['id'];
                        $eventObj=new Event();
                        $eventObj->editEvent($eventId);
                    }else{
                        echo '<h1>Unauthorised access</h1>';
                    }
                }elseif ($page=='deleteEvent'){
                    if($role==1){
                        $eventObj=new Event();
                        $eventObj->deleteEvent();
                    }else{
                        echo '<h1>Unauthorised access</h1>';
                    }
                }
                /*-/////////////////-----v1.0 changes for calender//////////----*/
                elseif ($page=='educatorSubmissions'){
                    if($role==1){
                        $educatorObj=new Educator();
                        $educatorObj->getAllSubmissions();
                    }else{
                        echo '<h1>Unauthorised access</h1>';
                    }
                }elseif ($page=='assignSubmissions'){
                    if($role==1){
                        $educatorObj=new Educator();
                        $educatorObj->assignSubmission();
                    }else{
                        echo '<h1>Unauthorised access</h1>';
                    }
                }elseif ($page=='createNewForm'){
                    if($role==1){
                        $educatorObj=new Educator();
                        $educatorObj->createNewForm();
                    }else{
                        echo '<h1>Unauthorised access</h1>';
                    }
                }elseif ($page=='abcform' || $page=='abcform2' ){
                    if($role==1){
                        if($page=='abcform'){
                            $form=1;
                        }else{
                            $form=2;
                        }
                        $educatorObj=new Educator();
                        $educatorObj->fillForm($form);
                    }else{
                        echo '<h1>Unauthorised access</h1>';
                    }
                }
                /*-----------------v2.0 changes for adding more forms-------------*/

                /*-/////////////////-----v2.0 changes for adding more forms//////////----*/
                elseif($page== 'logout'){
                    $user=new User();
                    $user->logout();
                }else{
                    if($role==1 || $role==2 || $role==3){
                        $resources=new Resources();
                        $resources->viewAllResources();
                    }else{
                        echo '<h1>Unauthorised access</h1>';
                    }
                }
                ?>
            </div>

        </div>
        <!-- End of Main Content -->
        <?php
        include('footer.php');
        ?>

    </div>
    <!-- End of Content Wrapper -->
</div>
<!-- End of Page Wrapper -->

<!-- Scroll to Top Button-->
<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>
<!-- Logout Modal-->
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                <a href="?page=logout" class="btn btn-primary" id="logout">Logout</a>
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
<!-- Page level plugins -->
<script src="vendor/datatables/jquery.dataTables.min.js"></script>
<script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>
<!--------------------------------------------------v1.0 changes for calender-------------------------->
<!-- Page level custom scripts -->
<script src="js/demo/datatables-demo.js"></script>
<?php if (isset($_GET['page']) && $_GET['page']=='viewAllEvents' ||($role!=3 && $_GET['page']=='home')){?>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.4.1/dist/jquery.min.js"></script>
    <script src="evo-calendar/js/evo-calendar.js"></script>
    <script src="evo-calendar/demo/demo.js"></script>
<?php }?>

<!--/////////////////////////////////////////////-----v1.0 changes for calender-------------------------->
</body>
</html>
