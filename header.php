<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
/** Check Session **/
if(isset($_SESSION['role'])){

    $role=$_SESSION['role'];
    $userid =  $_SESSION['userid'];
    if($role==1){
        $roleName="Admin";
    }elseif($role==2){
        $roleName="Normal User";
    }
}else{
    header("/");
    exit();
}?>

<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
<!-- Sidebar - Brand -->
<a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php" style=" background: white; padding-top: 50px !important;">

    <div class="sidebar-brand-text mx-3" ><img src="img/bbfdc logo.png" width="80px;"></div>
</a>
<div style="padding-top: 30px;background: white;"></div>
<!-- Divider -->
<hr class="sidebar-divider my-0">


<li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#resourcesSubMenu"
       aria-expanded="true" aria-controls="collapseUtilities">
        <i class="fas fa-fw fa-folder"></i>
        <span>Resources</span>
    </a>
    <div id="resourcesSubMenu" class="collapse" aria-labelledby="headingUtilities"
         data-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Resources options:</h6>
            <?php if($role==1 || $role==2 || $role==3){ ?>
                <a class="collapse-item" href="?page=viewAllResources">View All Resources</a>
            <?php }
            if($role==1){
                ?>
                <a class="collapse-item" href="?page=addNewResource">Add New Resources</a>
                <a class="collapse-item" href="?page=editResource">Update Resources</a>
                <a class="collapse-item" href="?page=deleteResources">Delete Resources</a>
                <?php
            }
            ?>
        </div>
    </div>
</li>
    <?php if($role==1 || $role==2 ){ ?>
<hr class="sidebar-divider my-0">
<li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#meetings"
       aria-expanded="true" aria-controls="collapseUtilities">
        <i class="fas fa-fw fa-tachometer-alt"></i>
        <span>Meetings</span>
    </a>
    <div id="meetings" class="collapse" aria-labelledby="headingUtilities"
         data-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Meeting options:</h6>
            <a class="collapse-item" href="?page=viewMeetings">View All Meetings</a>
            <?php
            if($role==1){
                ?>
                <a class="collapse-item" href="?page=scheduleMeeting">Schedule Meeting</a>
                <a class="collapse-item" href="?page=updatingMeeting">Update Meetings</a>
                <a class="collapse-item" href="?page=deleteMeeting">Delete Meetings</a>
                <?php
            }
            ?>
        </div>
    </div>
</li>
        <!--------------------------------------------------v1.0 changes for calender-------------------------->
        <?php
        if($role==1){
            ?>
            <hr class="sidebar-divider my-0">

            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseUtilities2"
                   aria-expanded="true" aria-controls="collapseUtilities">
                    <i class="fas fa-fw fa-calendar"></i>
                    <span>Events</span>
                </a>
                <div id="collapseUtilities2" class="collapse" aria-labelledby="headingUtilities"
                     data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header">Calender options:</h6>
                        <a class="collapse-item" href="?page=viewAllEvents">View All Events</a>
                        <a class="collapse-item" href="?page=addNewEvent">Add Event</a>
                        <a class="collapse-item" href="?page=editOrDeleteEvents">Edit or Delete Events</a>
                    </div>
                </div>
            </li>
        <?php }?>

        <!---/////////////////////////////////////-------v1.0 changes for calender------------>
<hr class="sidebar-divider my-0">
<!-- Nav Item - Utilities Collapse Menu -->
<?php
if($role==1){
    ?>
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseUtilities"
           aria-expanded="true" aria-controls="collapseUtilities">
            <i class="fas fa-fw fa-wrench"></i>
            <span>Users</span>
        </a>
        <div id="collapseUtilities" class="collapse" aria-labelledby="headingUtilities"
             data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Users options:</h6>
                <a class="collapse-item" href="?page=currentUsers">Current Users</a>
                <a class="collapse-item" href="?page=pendingUsers">Pending Users</a>
                <a class="collapse-item" href="?page=addNewUser">Add New User</a>
            </div>
        </div>
    </li>
    <hr class="sidebar-divider my-0">
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseForms"
           aria-expanded="true" aria-controls="collapseUtilities">
            <i class="fas fa-fw fa-folder"></i>
            <span>Forms</span>
        </a>
        <div id="collapseForms" class="collapse" aria-labelledby="headingUtilities"
             data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Forms:</h6>
                <a class="collapse-item" href="?page=createNewForm">Create New Form</a>
                <a class="collapse-item" href="?page=educatorSubmissions">View All Submissions</a>
                <a class="collapse-item" href="?page=assignSubmissions">Assign Submission</a>
            </div>
        </div>
    </li>
    <?php
}
?>
    <?php } ?>
    <hr class="sidebar-divider my-0">
    <li class="nav-item">
        <a class="nav-link collapsed" href="https://brightbeginningsfdcc.com.au/"
           aria-expanded="true" aria-controls="collapseUtilities">
            <i class="fas fa-fw fa-globe"></i>
            <span>BBFDCC Website</span>
        </a>
    </li>
    <!-- Divider -->
    <hr class="sidebar-divider">


    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>

