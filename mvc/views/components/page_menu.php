 <div class="wrapper row-offcanvas row-offcanvas-left">
        <!-- Left side column. contains the logo and sidebar -->
        <aside class="left-side sidebar-offcanvas">
            <!-- sidebar: style can be found in sidebar.less -->
            <section class="sidebar">
                <!-- Sidebar user panel -->
                <div class="user-panel">
                    <div class="pull-left image">
                        <img style="display:block" src="<?=imagelink($this->session->userdata('photo'))?>" class="img-rounded" alt="" />
                    </div>

                    <div class="pull-left info">
                        <?php
                            $name = $this->session->userdata("name");
                            if(strlen($name) > 18) {
                               $name = substr($name, 0,18);
                            }
                            echo "<p>".$name."</p>";
                        ?>
                        <a href="<?=base_url("profile/index")?>">
                            <i class="fa fa-hand-o-right color-green"></i>
                            <?=$this->session->userdata("usertype")?>
                           
                        </a>
                    </div>
                </div>
                
                <ul class="sidebar-menu">
<li class="treeview "><a href="<?php echo base_url('dashboard'); ?>"><i class="fa fa-laptop"></i><span>Dashboard</span> <i class="fa fa-angle-left pull-right"></i></a>
    <ul class="treeview-menu">
        <li class=""><a href="<?php echo base_url('dashboard/index/'.$this->session->userdata('campus_id')); ?>"><i class="fa fa-laptop"></i><span>Executive Dashboard</span> </a></li>
        <?php $allcampuses=allcampuses();
                    if($this->session->userdata('campus_id')==0){ 
                        foreach($allcampuses as $row)
                        { ?>
                            <li class=""><a href="<?php echo base_url('dashboard/index/'.$row['campusID'])?>"><i class="fa fa-laptop"></i><span><?php echo $row['name']; ?></span> </a></li>
                       <?php }
                    }
                ?>
    </ul>
</li>


                 <?php
 if($this->session->userdata('usertypeID') == 3)
 {
 ?>
 <li class=""><a href="<?php echo base_url('profile/index'); ?>"><i class="fa ast-graduatesilhouettesbw_p
"></i><span>Student</span> </a></li>
<li class=""><a href="<?php echo base_url('teacher'); ?>"><i class="fa ast-t3"></i><span>Teachers</span> </a></li>
<?php
 }
 ?>
                  <?php
 if($this->session->userdata('usertypeID') == 2)
 {
 ?>
      <li class=""><a href="<?php echo base_url('student'); ?>"><i class="fa ast-graduatesilhouettesbw_p
"></i><span>Student</span> </a></li>
 <li class=""><a href="<?php echo base_url('parents'); ?>"><i class="fa ast-p2"></i><span>Parents</span> </a></li>
 <li class=""><a href="<?php echo base_url('profile/index'); ?>"><i class="fa ast-t3"></i><span>Teacher</span> </a></li>
<?php
 }
 ?>
    <?php
 if($this->session->userdata('usertypeID') == 1 || $this->session->userdata('usertypeID') == 9)
 {
 ?>            
<li class="treeview "><a href="<?php echo base_url('dashboard'); ?>"><i class="fa fa-users"></i><span>Manage Stakeholders</span> <i class="fa fa-angle-left pull-right"></i></a>
    <ul class="treeview-menu">
        <li class=""><a href="<?php echo base_url('student'); ?>"><i class="fa ast-graduatesilhouettesbw_p
"></i><span>Students</span> </a></li>
        <li class=""><a href="<?php echo base_url('parents'); ?>"><i class="fa ast-p2"></i><span>Parents</span> </a></li>
        <li class=""><a href="<?php echo base_url('teacher'); ?>"><i class="fa ast-t3"></i><span>Teachers</span> </a></li>
        <li class=""><a href="<?php echo base_url('user'); ?>"><i class="fa fa-user"></i><span>Mgmt. & Support Staff 
</span> </a></li>
    </ul>
</li>

<li class="treeview "><a href="<?php echo base_url('#'); ?>"><i class="fa fa-building-o"></i><span>Departments/Functions</span> <!-- <i class="fa fa-angle-left pull-right"></i> --></a>
    <ul class="treeview-menu">
        <li class="treeview "><a href="<?php echo base_url('#'); ?>"><i class="fa fa-usd"></i><span>Payroll</span> <i class="fa fa-angle-left pull-right"></i></a>
            <ul class="treeview-menu">
                <li class=""style="background-color: #16425B;"><a href="<?php echo base_url('salary_template'); ?>"><i class="fa fa-calculator"></i><span>Salary Template</span> </a></li>
                <li class=""style="background-color: #16425B;"><a href="<?php echo base_url('hourly_template'); ?>"><i class="fa fa fa-clock-o"></i><span>Hourly Template</span> </a></li>
                <li class=""style="background-color: #16425B;"><a href="<?php echo base_url('manage_salary'); ?>"><i class="fa fa-beer"></i><span>Manage Salary</span> </a></li>
                <li class=""style="background-color: #16425B;"><a href="<?php echo base_url('make_payment'); ?>"><i class="fa fa-money"></i><span>Make Payment</span> </a></li>
            </ul>
        </li>
        <li class="treeview "><a href="<?php echo base_url('#'); ?>"><i class="fa fa-archive"></i><span>Fixed Assets</span> <i class="fa fa-angle-left pull-right"></i></a>
            <ul class="treeview-menu">
                <li class="" style="background-color: #16425B;"><a href="<?php echo base_url('vendor'); ?>"><i class="fa fa-rss"></i><span>Vendor</span> </a></li>
                <li class=""style="background-color: #16425B;"><a href="<?php echo base_url('location'); ?>"><i class="fa fa-newspaper-o"></i><span>Location</span> </a></li>
                <li class=""style="background-color: #16425B;"><a href="<?php echo base_url('asset_category'); ?>"><i class="fa fa-life-ring"></i><span>Asset Category</span> </a></li>
                <li class=""style="background-color: #16425B;"><a href="<?php echo base_url('asset'); ?>"><i class="fa fa-fax"></i><span>Asset</span> </a></li>
                <li class=""style="background-color: #16425B;"><a href="<?php echo base_url('asset_assignment'); ?>"><i class="fa fa-plug"></i><span>Asset Assignment</span> </a></li>
                <li class=""style="background-color: #16425B;"><a href="<?php echo base_url('purchase'); ?>"><i class="fa fa-cart-plus"></i><span>Purchase</span> </a></li>
            </ul>
        </li>
        <li style="background-color: 16425B;" class="treeview "><a href="<?php echo base_url('#'); ?>"><i class="fa iniicon-maininventory"></i><span>Inventory</span> <i class="fa fa-angle-left pull-right"></i></a>
            <ul class="treeview-menu">
                <li class="" style="background-color: #16425B;"><a href="<?php echo base_url('productcategory'); ?>"><i class="fa iniicon-productcategory"></i><span>Category</span> </a></li>
                <li class="" style="background-color: #16425B;"><a href="<?php echo base_url('product'); ?>"><i class="fa iniicon-product"></i><span>Product</span> </a></li>
                <li class="" style="background-color: #16425B;"><a href="<?php echo base_url('productwarehouse'); ?>"><i class="fa iniicon-productwarehouse"></i><span>Warehouse</span> </a></li>
                <li class="" style="background-color: #16425B;"><a href="<?php echo base_url('productsupplier'); ?>"><i class="fa iniicon-productsupplier"></i><span>Supplier</span> </a></li>
                <li class="" style="background-color: #16425B;"><a href="<?php echo base_url('productpurchase'); ?>"><i class="fa iniicon-productpurchase"></i><span>Purchase</span> </a></li>
                <li class="" style="background-color: #16425B;"><a href="<?php echo base_url('productsale'); ?>"><i class="fa iniicon-productsale"></i><span>Sale</span> </a></li>
            </ul>
        </li>
        <li class="treeview "><a href="<?php echo base_url('#'); ?>"><i class="fa icon-library"></i><span>Library</span> <i class="fa fa-angle-left pull-right"></i></a>
            <ul class="treeview-menu">
                <li class="" style="background-color:#16425B;"><a href="<?php echo base_url('lmember'); ?>"><i class="fa icon-member"></i><span>Member</span> </a></li>
                <li class="" style="background-color:#16425B; "><a href="<?php echo base_url('book'); ?>"><i class="fa icon-lbooks"></i><span>Books</span> </a></li>
                <li class="" style="background-color:#16425B; "><a href="<?php echo base_url('issue'); ?>"><i class="fa icon-issue"></i><span>Issue</span> </a></li>
                <li class="" style="background-color:#16425B; "><a href="<?php echo base_url('ebooks'); ?>"><i class="fa iniicon-ebook"></i><span>E-Books</span> </a></li>
            </ul>
        </li>
        <li class="treeview "><a href="<?php echo base_url('#'); ?>"><i class="fa icon-bus"></i><span>Transport</span> <i class="fa fa-angle-left pull-right"></i></a>
            <ul class="treeview-menu">
                <li class="" style="background-color:#16425B; "><a href="<?php echo base_url('transport'); ?>"><i class="fa icon-sbus"></i><span>Transport</span> </a></li>
                <li class="" style="background-color:#16425B; "><a href="<?php echo base_url('tmember'); ?>"><i class="fa icon-member"></i><span>Member</span> </a></li>
            </ul>
        </li>
        <li class="treeview "><a href="<?php echo base_url('#'); ?>"><i class="fa icon-hhostel"></i><span>Hostel</span> <i class="fa fa-angle-left pull-right"></i></a>
            <ul class="treeview-menu">
                <li class="" style="background-color:#16425B;"><a href="<?php echo base_url('hostel'); ?>"><i class="fa icon-hostel"></i><span>Hostel</span> </a></li>
                <li class="" style="background-color:#16425B;"><a href="<?php echo base_url('category'); ?>"><i class="fa fa-leaf"></i><span>Category</span> </a></li>
                <li class="" style="background-color:#16425B;"><a href="<?php echo base_url('hmember'); ?>"><i class="fa icon-member"></i><span>Member</span> </a></li>
            </ul>
        </li>
        <li class="treeview "><a href="<?php echo base_url('#'); ?>"><i class="fa icon-account"></i><span>Account</span> <i class="fa fa-angle-left pull-right"></i></a>
            <ul class="treeview-menu">
                <li class=""style="background-color: #16425B;"><a href="<?php echo base_url('feetypes'); ?>"><i class="fa icon-feetypes"></i><span>Fee Types</span> </a></li>
                <li class=""style="background-color: #16425B;"><a href="<?php echo base_url('invoice'); ?>"><i class="fa icon-invoice"></i><span>Fee Voucher</span> </a></li>
                <li class=""style="background-color: #16425B;"><a href="<?php echo base_url('paymenthistory'); ?>"><i class="fa icon-payment"></i><span>Payment History</span> </a></li>
                <li class=""style="background-color: #16425B;"><a href="<?php echo base_url('expense'); ?>"><i class="fa icon-expense"></i><span>Expense</span> </a></li>
                <li class=""style="background-color: #16425B;"><a href="<?php echo base_url('income'); ?>"><i class="fa iniicon-income"></i><span>Income</span> </a></li>
                <li class=""style="background-color: #16425B;"><a href="<?php echo base_url('global_payment'); ?>"><i class="fa fa-balance-scale"></i><span>Global Payment</span> </a></li>
            </ul>
        </li>
    </ul>
</li>
<?php
}
?>

<li class="treeview "><a href="<?php echo base_url('#'); ?>"><i class="fa icon-academicmain"></i><span>Academic & LMS</span> <i class="fa fa-angle-left pull-right"></i></a>
    <ul class="treeview-menu">
 <?php
 if($this->session->userdata('usertypeID') == 1 || $this->session->userdata('usertypeID') == 9 ||
   $this->session->userdata('usertypeID') == 4 || $this->session->userdata('usertypeID') == 2 )
    {
    ?>
        <li class=""><a href="<?php echo base_url('classes'); ?>"><i class="fa fa-sitemap"></i><span>Class</span> </a></li>
        <li class=""><a href="<?php echo base_url('section'); ?>"><i class="fa fa-star"></i><span>Section</span> </a></li>
        
        <?php
        }
        ?>
        <li class=""><a href="<?php echo base_url('subject'); ?>"><i class="fa ast-b1"></i><span>Subject</span> </a></li>
    <li class="treeview "><a href="<?php echo base_url('#'); ?>"><i class="fa icon-attendance"></i><span>     Syllabus</span> <i class="fa fa-angle-left pull-right"></i></a>
        <ul class="treeview-menu">
       <?php         
       if($this->session->userdata('usertypeID') == 3) {
            $classID = $this->data['myclass'];
            $class_syllabus = $this->db->where('adminID',$this->session->userdata('adminID'))->where('classesID',$classID)->get('classes')->result_array(); 
        } else {
            $class_syllabus = $this->db->where('adminID',$this->session->userdata('adminID'))->get('classes')->result_array(); 
        }
                                  foreach ($class_syllabus as $row):
                                    ?>
                                  <li class="" style="color: white;">
            <a href="<?php echo base_url('syllabus/index/' . $row['classesID']); ?>" >
                        <span>
                       Class <?php echo $row['classes']; ?>
                           </span>
                           </a> 
                           
        </li>
        <?php endforeach; ?>
       </ul>
    </li>

    <?php if($this->session->userdata('usertypeID') == 1 || $this->session->userdata('usertypeID') == 9 || $this->session->userdata('usertypeID') == 4 || $this->session->userdata('usertypeID') == 2 ){ ?>
        <li class=""><a href="<?php echo base_url('campus'); ?>"><i class="fa icon-syllabus"></i><span>Campus</span> </a></li>
    <?php } ?>
    
        <li class=""><a href="<?php echo base_url('lecture'); ?>"><i class="fa icon-syllabus"></i><span>Lecture</span> </a></li>
        <li class=""><a href="<?php echo base_url('assignment'); ?>"><i class="fa icon-assignment"></i><span>Assignment</span> </a></li>
        <li class=""><a href="<?php echo base_url('routine'); ?>"><i class="fa icon-routine"></i><span>Timetable</span> </a></li>
    </ul>
</li>
            <?php
 if($this->session->userdata('usertypeID') == 3)
 {
 ?>
 <li class=""><a href="<?php echo base_url('student/timetable_student'); ?>"><i class="fa icon-sattendance"></i><span>Live Classes</span> </a></li>
  <li class=""><a href="<?php echo base_url('sattendance'); ?>"><i class="fa icon-sattendance"></i><span>Student Attendance</span> </a></li>
<?php
 }
 ?>
  <?php
 if($this->session->userdata('usertypeID') == 2)
 {
 ?>
 <li class=""><a href="<?php echo base_url('student/timetable'); ?>"><i class="fa icon-sattendance"></i><span>Live Classes</span> </a></li>
   <li class="treeview "><a href="<?php echo base_url('#'); ?>"><i class="fa icon-attendance"></i><span>Mark Attendance</span> <i class="fa fa-angle-left pull-right"></i></a>
    <ul class="treeview-menu">

        <li class=""><a href="<?php echo base_url('sattendance'); ?>"><i class="fa icon-sattendance"></i><span>Student Attendance</span> </a></li>
       
        <li class=""><a href="<?php echo base_url('tattendance'); ?>"><i class="fa icon-tattendance"></i><span>Teacher Attendance</span> </a></li>
       

        
       
    </ul>
</li>
<?php
 }
 ?>
 <?php
 if($this->session->userdata('usertypeID') == 1 || $this->session->userdata('usertypeID') == 9 || 
   $this->session->userdata('usertypeID') == 4)
    {
    ?>
   <!--  <li class=""><a href="<?php echo base_url('student/timetable_student'); ?>"><i class="fa icon-sattendance"></i><span>Live Classes</span> </a></li> -->
    <li class=""><a href="<?php echo base_url('student/timetable'); ?>"><i class="fa fa-flask"></i><span>Live Classes</span> </a></li>
    <!-- <li class="treeview "><a href="<?php echo base_url('#'); ?>/#"><i class="fa icon-markmain"></i><span>Zoom Live Classes</span> <i class="fa fa-angle-left pull-right"></i></a>
    <ul class="treeview-menu">
        <li class=""><a href="<?php echo base_url('student/timetable'); ?>"><i class="fa fa-flask"></i><span>Live Classes</span> </a></li>
        <li class=""><a href="<?php echo base_url('markpercentage'); ?>"><i class="fa icon-markpercentage"></i><span>Mark Distribution</span> </a></li>
        <li class=""><a href="<?php echo base_url('promotion'); ?>"><i class="fa icon-promotion"></i><span>Promotion</span> </a></li>
    </ul>
</li> -->
<li class="treeview "><a href="<?php echo base_url('#'); ?>"><i class="fa icon-attendance"></i><span>Mark Attendance</span> <i class="fa fa-angle-left pull-right"></i></a>
    <ul class="treeview-menu">

        <li class=""><a href="<?php echo base_url('sattendance'); ?>"><i class="fa icon-sattendance"></i><span>Student Attendance</span> </a></li>
       
        <li class=""><a href="<?php echo base_url('tattendance'); ?>"><i class="fa icon-tattendance"></i><span>Teacher Attendance</span> </a></li>
       

        <li class=""><a href="<?php echo base_url('uattendance'); ?>"><i class="fa fa-user-secret"></i><span>User Attendance</span> </a></li>
       
    </ul>
</li>
 
<li class="treeview "><a href="<?php echo base_url('#'); ?>"><i class="fa icon-exam"></i><span>Examination</span> <i class="fa fa-angle-left pull-right"></i></a>
    <ul class="treeview-menu">
        <li class=""><a href="<?php echo base_url('exam'); ?>"><i class="fa fa-pencil"></i><span>Examination</span> </a></li>
        <li class=""><a href="<?php echo base_url('examschedule'); ?>"><i class="fa fa-puzzle-piece"></i><span>Exam Schedule</span> </a></li>
        <li class=""><a href="<?php echo base_url('grade'); ?>"><i class="fa fa-signal"></i><span>Grading System</span> </a></li>
        <li class=""><a href="<?php echo base_url('eattendance'); ?>"><i class="fa icon-eattendance"></i><span>Exam Attendance</span> </a></li>
    </ul>
</li>
<?php
        }
        ?>
 <?php
 if($this->session->userdata('usertypeID') == 2)
 {
 ?>
  <li class="treeview "><a href="<?php echo base_url('#'); ?>"><i class="fa icon-exam"></i><span>Examination</span> <i class="fa fa-angle-left pull-right"></i></a>
    <ul class="treeview-menu">
        
        <li class=""><a href="<?php echo base_url('examschedule'); ?>"><i class="fa fa-puzzle-piece"></i><span>Exam Schedule</span> </a></li>
       
        <li class=""><a href="<?php echo base_url('eattendance'); ?>"><i class="fa icon-eattendance"></i><span>Exam Attendance</span> </a></li>
    </ul>
</li>
 <li class=""><a href="<?php echo base_url('mark'); ?>"><i class="fa fa-flask"></i><span>Exam Marking</span> </a></li>
   <li class=""><a href="<?php echo base_url('conversation'); ?>"><i class="fa fa-envelope"></i><span>Message</span> </a></li>
   <li class=""><a href="<?php echo base_url('media'); ?>"><i class="fa fa-film"></i><span>Media</span> </a></li>
   <li class="treeview "><a href="<?php echo base_url('#'); ?>/#"><i class="fa fa-graduation-cap"></i><span>Online Examination</span> <i class="fa fa-angle-left pull-right"></i></a>
    <ul class="treeview-menu">
        <li class=""><a href="<?php echo base_url('question_group'); ?>"><i class="fa fa-question-circle"></i><span>Question Group</span> </a></li>
        <li class=""><a href="<?php echo base_url('question_level'); ?>"><i class="fa fa-level-up"></i><span>Question Level</span> </a></li>
        <li class=""><a href="<?php echo base_url('question_bank'); ?>"><i class="fa fa-qrcode"></i><span>Question Bank</span> </a></li>
        <li class=""><a href="<?php echo base_url('online_exam'); ?>"><i class="fa fa-slideshare"></i><span>Online Exam</span> </a></li>
        <li class=""><a href="<?php echo base_url('instruction'); ?>"><i class="fa fa-map-signs"></i><span>Instruction</span> </a></li>
        
    </ul>
</li>
<li class="treeview "><a href="<?php echo base_url('#'); ?>"><i class="fa iniicon-mainleaveapplication"></i><span>Leave Process</span> <i class="fa fa-angle-left pull-right"></i></a>
    <ul class="treeview-menu">
       <li class=""><a href="<?php echo base_url('leaveapply'); ?>"><i class="fa iniicon-leaveapply"></i><span>Apply Leave</span> </a></li>
        <li class=""><a href="<?php echo base_url('leaveapplication'); ?>"><i class="fa iniicon-leaveapplication"></i><span>Approve/Decline Leave</span> </a></li>
    </ul>
</li>
<li class=""><a href="<?php echo base_url('activities'); ?>"><i class="fa fa-fighter-jet"></i><span>Activities</span> </a></li>
 <li class="treeview "><a href="<?php echo base_url('#'); ?>"><i class="fa icon-library"></i><span>Library</span> <i class="fa fa-angle-left pull-right"></i></a>
            <ul class="treeview-menu">
                <!-- <li class="" style="background-color:#16425B;"><a href="<?php echo base_url('lmember'); ?>"><i class="fa icon-member"></i><span>Member</span> </a></li> -->
                <li class="" ><a href="<?php echo base_url('book'); ?>"><i class="fa icon-lbooks"></i><span>Books</span> </a></li>
                <!-- <li class="" style="background-color:#16425B; "><a href="<?php echo base_url('issue'); ?>"><i class="fa icon-issue"></i><span>Issue</span> </a></li> -->
                <li class="" ><a href="<?php echo base_url('ebooks'); ?>"><i class="fa iniicon-ebook"></i><span>E-Books</span> </a></li>
            </ul>
        </li>
         <li class=""><a href="<?php echo base_url('transport'); ?>"><i class="fa icon-sbus"></i><span>Transport</span> </a></li>
         <li class="treeview "><a href="<?php echo base_url('#'); ?>"><i class="fa icon-hhostel"></i><span>Hostel</span> <i class="fa fa-angle-left pull-right"></i></a>
            <ul class="treeview-menu">
                <li class="" ><a href="<?php echo base_url('hostel'); ?>"><i class="fa icon-hostel"></i><span>Hostel</span> </a></li>
                <li class="" ><a href="<?php echo base_url('category'); ?>"><i class="fa fa-leaf"></i><span>Category</span> </a></li>
                
            </ul>
        </li>
        <li class="treeview "><a href="<?php echo base_url('#'); ?>"><i class="fa icon-noticemain"></i><span>Announcement</span> <i class="fa fa-angle-left pull-right"></i></a>
    <ul class="treeview-menu">
        <li class=""><a href="<?php echo base_url('notice'); ?>"><i class="fa fa-calendar"></i><span>Notice</span> </a></li>
        <li class=""><a href="<?php echo base_url('event'); ?>"><i class="fa fa-calendar-check-o"></i><span>Event</span> </a></li>
        <li class=""><a href="<?php echo base_url('holiday'); ?>"><i class="fa icon-holiday"></i><span>Holiday</span> </a></li>
    </ul>
</li>
<li class="treeview "><a href="<?php echo base_url('#'); ?>"><i class="fa fa-clipboard"></i><span>Reports</span> <i class="fa fa-angle-left pull-right"></i></a>
    <ul class="treeview-menu">
       <!--  <li class=""><a href="<?php echo base_url('classesreport'); ?>"><i class="fa icon-classreport"></i><span>Class Report</span> </a></li> -->
        <li class=""><a href="<?php echo base_url('studentreport'); ?>"><i class="fa icon-studentreport"></i><span>Student Report</span> </a></li>
        <!-- <li class=""><a href="<?php echo base_url('idcardreport'); ?>"><i class="fa iniicon-idcardreport"></i><span>ID Card Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('admitcardreport'); ?>"><i class="fa iniicon-admitcardreport"></i><span>Admit Card Report</span> </a></li> -->
        <li class=""><a href="<?php echo base_url('routinereport'); ?>"><i class="fa iniicon-routinereport"></i><span>Routine Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('examschedulereport'); ?>"><i class="fa iniicon-examschedulereport"></i><span>Exam Schedule Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('attendancereport'); ?>"><i class="fa icon-attendancereport"></i><span>Attendance Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('attendanceoverviewreport'); ?>"><i class="fa iniicon-attendanceoverviewreport"></i><span>Attendance Overview Report</span> </a></li>
        <!-- <li class=""><a href="<?php echo base_url('librarybooksreport'); ?>"><i class="fa iniicon-librarybooksreport"></i><span>Library Books Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('librarycardreport'); ?>"><i class="fa iniicon-librarycardreport"></i><span>Library Card Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('librarybookissuereport'); ?>"><i class="fa iniicon-librarybookissuereport"></i><span>Library Book Issue Report</span> </a></li> -->
        <li class=""><a href="<?php echo base_url('terminalreport'); ?>"><i class="fa iniicon-terminalreport"></i><span>Terminal Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('meritstagereport'); ?>"><i class="fa iniicon-meritstagereport"></i><span>Merit Stage Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('tabulationsheetreport'); ?>"><i class="fa iniicon-tabulationsheetreport"></i><span>Tabulation Sheet Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('marksheetreport'); ?>"><i class="fa iniicon-marksheetreport"></i><span>Mark Sheet Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('progresscardreport'); ?>"><i class="fa iniicon-progresscardreport"></i><span>Progress Card Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('onlineexamreport'); ?>"><i class="fa iniicon-onlineexamreport"></i><span>Online Examination Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('onlineexamquestionreport'); ?>"><i class="fa iniicon-onlineexamquestionreport"></i><span>Online Exam Question Report</span> </a></li>
        <!-- <li class=""><a href="<?php echo base_url('onlineadmissionreport'); ?>"><i class="fa iniicon-onlineadmissionreport"></i><span>Online Admission Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('certificatereport'); ?>"><i class="fa fa-diamond"></i><span>Certificate Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('leaveapplicationreport'); ?>"><i class="fa iniicon-leaveapplicationreport"></i><span>Leave Application Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('productpurchasereport'); ?>"><i class="fa iniicon-productpurchasereport"></i><span>Product Purchase Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('productsalereport'); ?>"><i class="fa iniicon-productsalereport"></i><span>Product Sale Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('searchpaymentfeesreport'); ?>"><i class="fa iniicon-searchpaymentfeesreport"></i><span>Search Payment Fees Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('feesreport'); ?>"><i class="fa iniicon-feesreport"></i><span>Fees Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('duefeesreport'); ?>"><i class="fa iniicon-duefeesreport"></i><span>Due Fees Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('balancefeesreport'); ?>"><i class="fa iniicon-balancefeesreport"></i><span>Balance Fees Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('transactionreport'); ?>"><i class="fa iniicon-transactionreport"></i><span>Transaction Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('studentfinereport'); ?>"><i class="fa iniicon-studentfinereport"></i><span>Student Fine Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('salaryreport'); ?>"><i class="fa iniicon-salaryreport"></i><span>Salary Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('accountledgerreport'); ?>"><i class="fa iniicon-accountledgerreport"></i><span>Account Ledger Report</span> </a></li> -->
    </ul>
</li>

<li class=""><a href="<?php echo base_url('complain'); ?>"><i class="fa fa-commenting"></i><span>Complaint</span> </a></li>
<?php
        }
        ?>      
 
            <?php
 if($this->session->userdata('usertypeID') == 3)
 {
 ?>
  <li class=""><a href="<?php echo base_url('examschedule'); ?>"><i class="fa fa-puzzle-piece"></i><span>Exam Schedule</span> </a></li>
  <li class=""><a href="<?php echo base_url('mark'); ?>"><i class="fa fa-flask"></i><span>Exam Marking</span> </a></li>
   <li class=""><a href="<?php echo base_url('conversation'); ?>"><i class="fa fa-envelope"></i><span>Message</span> </a></li>
   <li class=""><a href="<?php echo base_url('media'); ?>"><i class="fa fa-film"></i><span>Media</span> </a></li>
    <li class=""><a href="<?php echo base_url('take_exam'); ?>"><i class="fa fa-user-secret"></i><span>Take Exam</span> </a></li>
    <li class=""><a href="<?php echo base_url('leaveapply'); ?>"><i class="fa iniicon-leaveapply"></i><span>Apply Leave</span> </a></li>
    <li class=""><a href="<?php echo base_url('activities'); ?>"><i class="fa fa-fighter-jet"></i><span>Activities</span> </a></li>
    <li class="treeview "><a href="<?php echo base_url('#'); ?>"><i class="fa icon-library"></i><span>Library</span> <i class="fa fa-angle-left pull-right"></i></a>
            <ul class="treeview-menu">
                <!-- <li class="" style="background-color:#16425B;"><a href="<?php echo base_url('lmember'); ?>"><i class="fa icon-member"></i><span>Member</span> </a></li> -->
                <li class="" ><a href="<?php echo base_url('book'); ?>"><i class="fa icon-lbooks"></i><span>Books</span> </a></li>
                <li class="" ><a href="<?php echo base_url('issue'); ?>"><i class="fa icon-issue"></i><span>Issue</span> </a></li>
                <li class="" ><a href="<?php echo base_url('ebooks'); ?>"><i class="fa iniicon-ebook"></i><span>E-Books</span> </a></li>
            </ul>
        </li>
        <li class="treeview "><a href="<?php echo base_url('#'); ?>"><i class="fa icon-bus"></i><span>Transport</span> <i class="fa fa-angle-left pull-right"></i></a>
            <ul class="treeview-menu">
                <li class="" ><a href="<?php echo base_url('transport'); ?>"><i class="fa icon-sbus"></i><span>Transport</span> </a></li>
                <li class="" ><a href="<?php echo base_url('tmember'); ?>"><i class="fa icon-member"></i><span>Member</span> </a></li>
            </ul>
        </li>
        <li class="treeview "><a href="<?php echo base_url('#'); ?>"><i class="fa icon-hhostel"></i><span>Hostel</span> <i class="fa fa-angle-left pull-right"></i></a>
            <ul class="treeview-menu">
                <li class="" ><a href="<?php echo base_url('hostel'); ?>"><i class="fa icon-hostel"></i><span>Hostel</span> </a></li>
                <li class="" ><a href="<?php echo base_url('category'); ?>"><i class="fa fa-leaf"></i><span>Category</span> </a></li>
                
            </ul>
        </li>
        <li class="treeview "><a href="<?php echo base_url('#'); ?>"><i class="fa icon-account"></i><span>Account</span> <i class="fa fa-angle-left pull-right"></i></a>
            <ul class="treeview-menu">
               <li class=""><a href="<?php echo base_url('invoice'); ?>"><i class="fa icon-invoice"></i><span>Fee Voucher</span> </a></li>
                <li class=""><a href="<?php echo base_url('paymenthistory'); ?>"><i class="fa icon-payment"></i><span>Payment History</span> </a></li>
            </ul>
        </li>
        <li class="treeview "><a href="<?php echo base_url('#'); ?>"><i class="fa icon-noticemain"></i><span>Announcement</span> <i class="fa fa-angle-left pull-right"></i></a>
    <ul class="treeview-menu">
        <li class=""><a href="<?php echo base_url('notice'); ?>"><i class="fa fa-calendar"></i><span>Notice</span> </a></li>
        <li class=""><a href="<?php echo base_url('event'); ?>"><i class="fa fa-calendar-check-o"></i><span>Event</span> </a></li>
        <li class=""><a href="<?php echo base_url('holiday'); ?>"><i class="fa icon-holiday"></i><span>Holiday</span> </a></li>
    </ul>
</li>
<li class=""><a href="<?php echo base_url('examschedulereport'); ?>"><i class="fa iniicon-examschedulereport"></i><span>Exam Schedule Report</span> </a></li>
<li class=""><a href="<?php echo base_url('onlineadmission'); ?>"><i class="fa iniicon-onlineadmission"></i><span>Online Admission</span> </a></li> 
<li class=""><a href="<?php echo base_url('complain'); ?>"><i class="fa fa-commenting"></i><span>Complaint</span> </a></li>
<?php
 }
 ?>
 <?php
 if($this->session->userdata('usertypeID') == 1 || $this->session->userdata('usertypeID') == 9 ||
    $this->session->userdata('usertypeID') == 4)
    {
    ?>       
<li class="treeview "><a href="<?php echo base_url('#'); ?>/#"><i class="fa icon-markmain"></i><span>Exam Marking</span> <i class="fa fa-angle-left pull-right"></i></a>
    <ul class="treeview-menu">
        <li class=""><a href="<?php echo base_url('mark'); ?>"><i class="fa fa-flask"></i><span>Exam Marking</span> </a></li>
        <li class=""><a href="<?php echo base_url('markpercentage'); ?>"><i class="fa icon-markpercentage"></i><span>Mark Distribution</span> </a></li>
        <li class=""><a href="<?php echo base_url('promotion'); ?>"><i class="fa icon-promotion"></i><span>Promotion</span> </a></li>
    </ul>
</li>


<!-- <li class=""><a href="<?php echo base_url('converstion'); ?>"><i class="fa fa-envelope"></i><span>Message</span> </a></li>
<li class=""><a href="<?php echo base_url('media'); ?>"><i class="fa fa-film"></i><span>Media</span> </a></li> -->

<li class=""><a href="<?php echo base_url('mailandsms'); ?>"><i class="fa icon-mailandsms"></i><span>Mail / SMS</span> </a></li>
<li class="treeview "><a href="<?php echo base_url('#'); ?>/#"><i class="fa fa-graduation-cap"></i><span>Online Examination</span> <i class="fa fa-angle-left pull-right"></i></a>
    <ul class="treeview-menu">
        <li class=""><a href="<?php echo base_url('question_group'); ?>"><i class="fa fa-question-circle"></i><span>Question Group</span> </a></li>
        <li class=""><a href="<?php echo base_url('question_level'); ?>"><i class="fa fa-level-up"></i><span>Question Level</span> </a></li>
        <li class=""><a href="<?php echo base_url('question_bank'); ?>"><i class="fa fa-qrcode"></i><span>Question Bank</span> </a></li>
        <li class=""><a href="<?php echo base_url('online_exam'); ?>"><i class="fa fa-slideshare"></i><span>Online Exam</span> </a></li>
        <li class=""><a href="<?php echo base_url('instruction'); ?>"><i class="fa fa-map-signs"></i><span>Instruction</span> </a></li>
        <li class=""><a href="<?php echo base_url('take_exam'); ?>"><i class="fa fa-user-secret"></i><span>Take Exam</span> </a></li>
    </ul>
</li>
<li class="treeview "><a href="<?php echo base_url('#'); ?>"><i class="fa iniicon-mainleaveapplication"></i><span>Leave Process</span> <i class="fa fa-angle-left pull-right"></i></a>
    <ul class="treeview-menu">
        <li class=""><a href="<?php echo base_url('leavecategory'); ?>"><i class="fa iniicon-leavecategory"></i><span>Leave Category</span> </a></li>
        <li class=""><a href="<?php echo base_url('leaveassign'); ?>"><i class="fa iniicon-leaveassign"></i><span>Assign Leave</span> </a></li>
        <li class=""><a href="<?php echo base_url('leaveapply'); ?>"><i class="fa iniicon-leaveapply"></i><span>Apply Leave</span> </a></li>
        <li class=""><a href="<?php echo base_url('leaveapplication'); ?>"><i class="fa iniicon-leaveapplication"></i><span>Approve/Decline Leave</span> </a></li>
    </ul>
</li>


<li class="treeview "><a href="<?php echo base_url('#'); ?>"><i class="fa fa-child"></i><span>Child Care</span> <i class="fa fa-angle-left pull-right"></i></a>
    <ul class="treeview-menu">
        <li class=""><a href="<?php echo base_url('activitiescategory'); ?>"><i class="fa fa-pagelines"></i><span>Activities Category</span> </a></li>
        <li class=""><a href="<?php echo base_url('activities'); ?>"><i class="fa fa-fighter-jet"></i><span>Activities</span> </a></li>
        <li class=""><a href="<?php echo base_url('childcare'); ?>"><i class="fa fa-wheelchair"></i><span>Child Care</span> </a></li>
    </ul>
</li>
<li class="treeview "><a href="<?php echo base_url('#'); ?>"><i class="fa icon-noticemain"></i><span>Announcement</span> <i class="fa fa-angle-left pull-right"></i></a>
    <ul class="treeview-menu">
        <li class=""><a href="<?php echo base_url('notice'); ?>"><i class="fa fa-calendar"></i><span>Notice</span> </a></li>
        <li class=""><a href="<?php echo base_url('event'); ?>"><i class="fa fa-calendar-check-o"></i><span>Event</span> </a></li>
        <li class=""><a href="<?php echo base_url('holiday'); ?>"><i class="fa icon-holiday"></i><span>Holiday</span> </a></li>
    </ul>
</li>
<li class=""><a href="<?php echo base_url('complain'); ?>"><i class="fa fa-commenting"></i><span>Complaint</span> </a></li>
<?php
}
?>
            <?php
 if( $this->session->userdata('usertypeID') == 4 )
    {
    ?>
<li class="treeview "><a href="<?php echo base_url('#'); ?>"><i class="fa fa-clipboard"></i><span>Reports</span> <i class="fa fa-angle-left pull-right"></i></a>
    <ul class="treeview-menu">
       
        <li class=""><a href="<?php echo base_url('routinereport'); ?>"><i class="fa iniicon-routinereport"></i><span>Routine Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('examschedulereport'); ?>"><i class="fa iniicon-examschedulereport"></i><span>Exam Schedule Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('attendancereport'); ?>"><i class="fa icon-attendancereport"></i><span>Attendance Report</span> </a></li>
         <li class=""><a href="<?php echo base_url('marksheetreport'); ?>"><i class="fa iniicon-marksheetreport"></i><span>Mark Sheet Report</span> </a></li>
          <li class=""><a href="<?php echo base_url('feesreport'); ?>"><i class="fa iniicon-feesreport"></i><span>Fees Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('duefeesreport'); ?>"><i class="fa iniicon-duefeesreport"></i><span>Due Fees Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('balancefeesreport'); ?>"><i class="fa iniicon-balancefeesreport"></i><span>Balance Fees Report</span> </a></li>
        
    </ul>
</li>
 <?php
        }
        ?>
             <?php
 if( $this->session->userdata('usertypeID') == 1 || $this->session->userdata('usertypeID') == 9 )
    {
    ?>
<li class="treeview "><a href="<?php echo base_url('#'); ?>"><i class="fa fa-clipboard"></i><span>Reports</span> <i class="fa fa-angle-left pull-right"></i></a>
    <ul class="treeview-menu">
        <li class=""><a href="<?php echo base_url('classesreport'); ?>"><i class="fa icon-classreport"></i><span>Class Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('studentreport'); ?>"><i class="fa icon-studentreport"></i><span>Student Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('idcardreport'); ?>"><i class="fa iniicon-idcardreport"></i><span>ID Card Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('admitcardreport'); ?>"><i class="fa iniicon-admitcardreport"></i><span>Admit Card Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('routinereport'); ?>"><i class="fa iniicon-routinereport"></i><span>Routine Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('examschedulereport'); ?>"><i class="fa iniicon-examschedulereport"></i><span>Exam Schedule Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('attendancereport'); ?>"><i class="fa icon-attendancereport"></i><span>Attendance Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('TeacherAttendancereport'); ?>"><i class="fa icon-attendancereport"></i><span>Teacher Attendance Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('attendanceoverviewreport'); ?>"><i class="fa iniicon-attendanceoverviewreport"></i><span>Attendance Overview Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('librarybooksreport'); ?>"><i class="fa iniicon-librarybooksreport"></i><span>Library Books Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('librarycardreport'); ?>"><i class="fa iniicon-librarycardreport"></i><span>Library Card Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('librarybookissuereport'); ?>"><i class="fa iniicon-librarybookissuereport"></i><span>Library Book Issue Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('terminalreport'); ?>"><i class="fa iniicon-terminalreport"></i><span>Terminal Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('meritstagereport'); ?>"><i class="fa iniicon-meritstagereport"></i><span>Merit Stage Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('tabulationsheetreport'); ?>"><i class="fa iniicon-tabulationsheetreport"></i><span>Tabulation Sheet Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('marksheetreport'); ?>"><i class="fa iniicon-marksheetreport"></i><span>Mark Sheet Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('progresscardreport'); ?>"><i class="fa iniicon-progresscardreport"></i><span>Progress Card Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('onlineexamreport'); ?>"><i class="fa iniicon-onlineexamreport"></i><span>Online Examination Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('onlineexamquestionreport'); ?>"><i class="fa iniicon-onlineexamquestionreport"></i><span>Online Exam Question Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('onlineadmissionreport'); ?>"><i class="fa iniicon-onlineadmissionreport"></i><span>Online Admission Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('certificatereport'); ?>"><i class="fa fa-diamond"></i><span>Certificate Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('leaveapplicationreport'); ?>"><i class="fa iniicon-leaveapplicationreport"></i><span>Leave Application Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('productpurchasereport'); ?>"><i class="fa iniicon-productpurchasereport"></i><span>Product Purchase Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('productsalereport'); ?>"><i class="fa iniicon-productsalereport"></i><span>Product Sale Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('searchpaymentfeesreport'); ?>"><i class="fa iniicon-searchpaymentfeesreport"></i><span>Search Payment Fees Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('feesreport'); ?>"><i class="fa iniicon-feesreport"></i><span>Fees Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('duefeesreport'); ?>"><i class="fa iniicon-duefeesreport"></i><span>Due Fees Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('balancefeesreport'); ?>"><i class="fa iniicon-balancefeesreport"></i><span>Balance Fees Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('transactionreport'); ?>"><i class="fa iniicon-transactionreport"></i><span>Transaction Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('studentfinereport'); ?>"><i class="fa iniicon-studentfinereport"></i><span>Student Fine Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('salaryreport'); ?>"><i class="fa iniicon-salaryreport"></i><span>Salary Report</span> </a></li>
        <li class=""><a href="<?php echo base_url('accountledgerreport'); ?>"><i class="fa iniicon-accountledgerreport"></i><span>Account Ledger Report</span> </a></li>
    </ul>
</li>
 <?php
        }
        ?>
<!-- <li class=""><a href="<?php echo base_url('onlineadmission'); ?>"><i class="fa iniicon-onlineadmission"></i><span>Online Admission</span> </a></li> -->
           <?php
 if( $this->session->userdata('usertypeID') == 1 || $this->session->userdata('usertypeID') == 9 )
    {
    ?>
<!-- <li class=""><a href="<?php echo base_url('visitorinfo'); ?>"><i class="fa icon-visitorinfo"></i><span>Visitor Information</span> </a></li>
 --> 
<li class="treeview "><a href="<?php echo base_url('#'); ?>"><i class="fa icon-administrator"></i><span>Administrator</span> <i class="fa fa-angle-left pull-right"></i></a>
    <ul class="treeview-menu">
        <li class=""><a href="<?php echo base_url('schoolyear'); ?>"><i class="fa fa fa-calendar-plus-o"></i><span>Academic Year</span> </a></li>
        <li class=""><a href="<?php echo base_url('studentgroup'); ?>"><i class="fa fa-object-group"></i><span>Student Group</span> </a></li>
        
        <li class=""><a href="<?php echo base_url('certificate_template'); ?>"><i class="fa fa-certificate"></i><span>Certificate Template</span> </a></li>
        <?php /* <li class=""><a href="<?php echo base_url('systemadmin'); ?>"><i class="fa icon-systemadmin"></i><span>System Admin</span> </a></li> */ ?>
        <li class=""><a href="<?php echo base_url('resetpassword'); ?>"><i class="fa icon-reset_password"></i><span>Reset Password</span> </a></li>
        <li class=""><a href="<?php echo base_url('sociallink'); ?>"><i class="fa iniicon-sociallink"></i><span>Social Link</span> </a></li>
        <li class=""><a href="<?php echo base_url('mailandsmstemplate'); ?>"><i class="fa icon-template"></i><span>Mail / SMS Template</span> </a></li>
        <li class=""><a href="<?php echo base_url('bulkimport'); ?>"><i class="fa fa-upload"></i><span>Import</span> </a></li>
        <li class=""><a href="<?php echo base_url('backup'); ?>"><i class="fa fa-download"></i><span>Backup</span> </a></li>
        <li class=""><a href="<?php echo base_url('usertype'); ?>"><i class="fa icon-role"></i><span>Role</span> </a></li>
        <li class=""><a href="<?php echo base_url('permission'); ?>"><i class="fa icon-permission"></i><span>Permission</span> </a></li>
        <li class=""><a href="<?php echo base_url('update'); ?>"><i class="fa fa-refresh"></i><span>Update</span> </a></li>
    </ul>
</li>

<li class="treeview "><a href="<?php echo base_url('#'); ?>"><i class="fa fa-home"></i><span>Frontend</span> <i class="fa fa-angle-left pull-right"></i></a>
    <ul class="treeview-menu">
        <li class=""><a href="<?php echo base_url('posts_categories'); ?>"><i class="fa fa-anchor"></i><span>Posts Categories</span> </a></li>
        <li class=""><a href="<?php echo base_url('posts'); ?>"><i class="fa fa-thumb-tack"></i><span>Posts</span> </a></li>
        <li class=""><a href="<?php echo base_url('pages'); ?>"><i class="fa fa-connectdevelop"></i><span>Pages</span> </a></li>
        <li class=""><a href="<?php echo base_url('frontendmenu'); ?>"><i class="fa iniicon-fmenu"></i><span>Menu</span> </a></li>
    </ul>
</li>
<li class="treeview "><a href="<?php echo base_url('#'); ?>"><i class="fa fa-gavel"></i><span>Settings</span> <i class="fa fa-angle-left pull-right"></i></a>
    <ul class="treeview-menu">
        <li class=""><a href="<?php echo base_url('setting'); ?>"><i class="fa fa-gears"></i><span>General Setting</span> </a></li>
        <li class=""><a href="<?php echo base_url('frontend_setting'); ?>"><i class="fa fa-asterisk"></i><span>Frontend Settings</span> </a></li>
        <li class=""><a href="<?php echo base_url('paymentsettings'); ?>"><i class="fa icon-paymentsettings"></i><span>Payment Settings</span> </a></li>
        <li class=""><a href="<?php echo base_url('smssettings'); ?>"><i class="fa fa-wrench"></i><span>SMS Settings</span> </a></li>
        <li class=""><a href="<?php echo base_url('emailsetting'); ?>"><i class="fa iniicon-ini-emailsetting"></i><span>Email Setting</span> </a></li>
        <li class=""><a href="<?php echo base_url('bankdetail'); ?>"><i class="fa fa-wrench"></i><span>Download Setting</span> </a></li>
    </ul>
</li>
<?php
        }
        ?>

</ul>
                
            </section>
            <!-- /.sidebar -->
        </aside>




     