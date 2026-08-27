 <?php
                            // print_r($data['conferences']);
                            // exit();
                            ?>
<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa fa-calendar-check-o"></i> Zoom Live Class Timetable</h3>


        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
            <li class="active">Zoom Live Class</li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">
            <div class="col-sm-12">
                <?php if((($siteinfos->school_year == $this->session->userdata('defaultschoolyearID')) || ($this->session->userdata('usertypeID') == 1))) { ?>
                    <?php if(permissionChecker('event_add')) { ?>
                        <h5 class="page-header">
                            <a href="<?php echo base_url('student/addonlineclass') ?>">
                                <i class="fa fa-plus"></i>
                                <?=$this->lang->line('add_timetable')?>
                            </a>
                        </h5>
                    <?php } ?>
                <?php } ?>

                 <!--  -->

                <div id="hide-table">
                    <table id="example1" class="table table-striped table-bordered table-hover dataTable no-footer">
                        <thead>
                            <tr>
<th><?=$this->lang->line('slno')?></th>               
 <th><?php echo $this->lang->line('class') . " " . $this->lang->line('title'); ?></th>
<th><?php echo $this->lang->line('date'); ?></th>
<th><?php echo $this->lang->line('class'); ?></th>
<th><?php echo $this->lang->line('class') . ' ' . $this->lang->line('host'); ?></th>
<th><?php echo $this->lang->line('status'); ?></th>
<th class="text-right"><?php echo $this->lang->line('action'); ?></th>                 
                            </tr>
                        </thead>
                        <tbody>
                           
     <?php if(!empty($conferences_student)) {$i = 1; foreach($conferences_student as $conference_key => $conference_value) { 
         $return_response = json_decode($conference_value->return_response);
            ?>
                                <tr>
                                    <td data-title="<?=$this->lang->line('slno')?>">
                                        <?php echo $i; ?>
                                    </td>
                                    <td data-title="<?=$this->lang->line('event_title')?>">
                                        <?php
                                            if(strlen($conference_value->title) > 25)
                                                echo strip_tags(substr($conference_value->title, 0, 25)."...");
                                            else
                                                echo strip_tags(substr($conference_value->title, 0, 25));
                                        ?>
                                    </td>
                                    <td data-title="<?=$this->lang->line('event_fdate')?>">
                                        <?php echo date("d M Y h:i A", strtotime($conference_value->date)); ?>
                                    </td>
                                    <td data-title="<?=$this->lang->line('exam_name')?>">
                                         <?php echo $conference_value->classes . " (" . $conference_value->section . ")";
                                                    ?>
                                    </td>
                                   
                                    <td data-title="<?=$this->lang->line('exam_name')?>">
                                        <?php
                                                    $name = ($conference_value->create_for_surname == "") ? $conference_value->create_for_name : $conference_value->create_for_name . " " . $conference_value->create_for_surname;
                                                    echo $name . " (" . $conference_value->for_create_role_name . " : " . $conference_value->for_create_employee_id . ")";
                                                    ?>
                                    </td>
                                     
                                     
                                     <td data-title="<?=$this->lang->line('event_details')?>">
                                         <?php
                                                    if ($conference_value->status == 0) {
                                                        ?>
                                                        <span class="label label-warning">
                                                            <?php
                                                            echo $this->lang->line('awaited');
                                                            ?>
                                                        </span>
                                                        <?php
                                                    } elseif ($conference_value->status == 1) {
                                                        ?>
                                                        <span class="label label-default">
                                                            <?php
                                                            echo $this->lang->line('cancelled');
                                                            ?>
                                                        </span>
                                                        <?php
                                                    } else {
                                                        ?>
                                                        <span class="label label-success">
                                                            <?php
                                                            echo $this->lang->line('finished');
                                                            ?>
                                                        </span>
                                                        <?php
                                                    }
                                                    ?>
                                    </td>
                                    <td data-title="<?=$this->lang->line('exam_name')?>">
                                        <?php
                                                    if ($conference_value->status == 0) {
                                                        ?>
                                                        <a data-placement="left" href="<?php echo $return_response->join_url; ?>" class="btn btn-default btn-xs join-btn"  target="_blank" data-id="<?php echo $conference_value->id ?>">
                                                            <i class="fa fa-sign-in"></i> <?php echo $this->lang->line('join') . ' ' . $this->lang->line('class'); ?>
                                                        </a>
                                                        <?php
                                                    }
                                                    ?>
                                                </td> 
                                            </tr>
                            <?php $i++; }} ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

