<!-- Begin Page Content -->
    <?php
    if(isset($_GET['mtid'])){
        ?>
        <a href="/portal/<?php echo "viewMeetingDetails.php?id=".$_GET['mtid']?>" class="btn btn-secondary btn-icon-split">
                                        <span class="icon text-white-50">
                                            <i class="fas fa-arrow-left"></i>
                                        </span>
            <span class="text">Go back</span>
        </a>
        <?php
    }elseif(isset($_GET['eudm'])){
        ?>
        <a href="/portal/edit-update-deleteMeetings.php" class="btn btn-secondary btn-icon-split">
                                        <span class="icon text-white-50">
                                            <i class="fas fa-arrow-left"></i>
                                        </span>
            <span class="text">Go back</span>
        </a>

        <?php
    }elseif(isset($_GET['vr'])){ ?>
        <a href="/portal/viewAllResource.php" class="btn btn-secondary btn-icon-split">
                                        <span class="icon text-white-50">
                                            <i class="fas fa-arrow-left"></i>
                                        </span>
            <span class="text">Go back</span>
        </a>
        <?php
    }else{
        ?>
        <a href="/portal/" class="btn btn-secondary btn-icon-split">
                                        <span class="icon text-white-50">
                                            <i class="fas fa-arrow-left"></i>
                                        </span>
            <span class="text">Go back</span>
        </a>
    <?php }?>
    <br><br>

    <!-- Page Heading -->
    <p><iframe style="height: 700px; width: 900px;" src="/portal/books/?location=<?php echo $_GET['res']?>&amp;requestfrom=bbfdcc" seamless="seamless" scrolling="no" frameborder="0" allowtransparency="true" allowfullscreen="true"></iframe></p>
    <p></p>





<div class="container">
    <div class="row text-center">

        <!-- Team item -->
        <div class="col-xl-3 col-sm-6 mb-5">
            <div class="bg-white rounded shadow-sm py-5 px-4"><img src="https://lms.of.edu.au/draftfile.php/16/user/draft/690723154/1%20%282%29.png" alt="" width="100" class="img-fluid rounded-circle mb-3 img-thumbnail shadow-sm">
                <h5 class="mb-0">Dashboard</h5>
            </div>
        </div><!-- End -->

        <!-- Team item -->
        <div class="col-xl-3 col-sm-6 mb-5">
            <div class="bg-white rounded shadow-sm py-5 px-4"><img src="https://lms.of.edu.au/draftfile.php/16/user/draft/690723154/6%20%282%29.png" alt="" width="100" class="img-fluid rounded-circle mb-3 img-thumbnail shadow-sm">
                <h5 class="mb-0">Library</h5>

            </div>
        </div><!-- End -->

        <!-- Team item -->
        <div class="col-xl-3 col-sm-6 mb-5">
            <div class="bg-white rounded shadow-sm py-5 px-4"><img src="https://lms.of.edu.au/draftfile.php/16/user/draft/690723154/3%20%283%29.png" alt="" width="100" class="img-fluid rounded-circle mb-3 img-thumbnail shadow-sm">
                <h5 class="mb-0">Policies</h5>
            </div>
        </div><!-- End -->

        <!-- Team item -->
        <div class="col-xl-3 col-sm-6 mb-5">
            <div class="bg-white rounded shadow-sm py-5 px-4"><img src="https://lms.of.edu.au/draftfile.php/16/user/draft/690723154/7%20%284%29.png" alt="" width="100" class="img-fluid rounded-circle mb-3 img-thumbnail shadow-sm">
                <h5 class="mb-0">Learn to use LMS</h5>
            </div>
        </div>
        <!-- End -->

        <!-- Team item -->
        <div class="col-xl-3 col-sm-6 mb-5">
            <div class="bg-white rounded shadow-sm py-5 px-4"><img src="https://lms.of.edu.au/draftfile.php/16/user/draft/690723154/4%20%282%29.png" alt="" width="100" class="img-fluid rounded-circle mb-3 img-thumbnail shadow-sm">
                <h5 class="mb-0">FAQs</h5>
            </div>
        </div>
        <!-- End -->

        <!-- Team item -->
        <div class="col-xl-3 col-sm-6 mb-5">
            <div class="bg-white rounded shadow-sm py-5 px-4"><img src="https://lms.of.edu.au/draftfile.php/16/user/draft/690723154/5%20%281%29.png" alt="" width="100" class="img-fluid rounded-circle mb-3 img-thumbnail shadow-sm">
                <h5 class="mb-0">Contacts</h5>
            </div>
        </div>
        <!-- End -->
        <!-- Team item -->
        <div class="col-xl-3 col-sm-6 mb-5">
            <div class="bg-white rounded shadow-sm py-5 px-4"><img src="https://lms.of.edu.au/draftfile.php/16/user/draft/690723154/2%20%281%29.png" alt="" width="100" class="img-fluid rounded-circle mb-3 img-thumbnail shadow-sm">
                <h5 class="mb-0">IT Help Desk</h5>
            </div>
        </div>
        <!-- End -->

    </div>
</div>









