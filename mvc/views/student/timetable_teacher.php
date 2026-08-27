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
              
                   
                        <h5 class="page-header">
                            <a href="<?php echo base_url('student/add_classbyteacher') ?>">
                                <i class="fa fa-plus"></i>
                                <?=$this->lang->line('add_timetable')?>
                            </a>
                        </h5>
                    
               
                 <!--  -->

                <div id="hide-table">
                    <table id="example1" class="table table-striped table-bordered table-hover dataTable no-footer">
                        <thead>
                            <tr>
<th><?=$this->lang->line('slno')?></th>               
 <th><?php echo $this->lang->line('class') . " " . $this->lang->line('title'); ?>
                                        </th>
                                        <th><?php echo $this->lang->line('date'); ?>
                                        </th>
                                        <th><?php echo $this->lang->line('api_used_add'); ?>
                                        </th>
                                        <th><?php echo $this->lang->line('created_by') ?>
                                        </th>
                                        <th><?php echo $this->lang->line('class'); ?>
                                        </th>
                                        <th><?php echo $this->lang->line('status'); ?>
                                        </th>
                                        <th><?php echo $this->lang->line('action'); ?></th>               
                            </tr>
                        </thead>
                        <tbody>
                           
     <?php if(!empty($conferences)) {$i = 1; foreach($conferences as $conference_key => $conference_value) { 
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
                                        <?php echo $conference_value->api_type; ?>
                                    </td>
                                    <td data-title="<?=$this->lang->line('exam_name')?>">
                                         <?php
                                                    if ($conference_value->created_id == $logged_staff_id) {
                                                        echo "Self";
                                                    } else {
                                                        echo $conference_value->create_by_name . " " . $conference_value->create_by_surname;
                                                    }
                                                    ?>
                                    </td>
                                     
                                     <td data-title="<?=$this->lang->line('exam_name')?>">
                                         <?php echo $conference_value->classes . " (" . $conference_value->section . ")";
                                                    ?>
                                    </td>
                                     <td data-title="<?=$this->lang->line('event_details')?>">
                                        <form class="chgstatus_form" method="POST" action="<?php echo site_url('student/chgstatus') ?>">
                                                        <input type="hidden" name="conference_id" value="<?php echo $conference_value->id; ?>">
                                                        <select class="form-control chgstatus_dropdown" name="chg_status">
                                                            <option value="0" <?php if ($conference_value->status == 0) echo "selected='selected'" ?>><?php echo $this->lang->line('awaited'); ?></option>
                                                            <option value="1" <?php if ($conference_value->status == 1) echo "selected='selected'" ?>> <?php echo $this->lang->line('cancelled') ?></option>
                                                            <option value="2" <?php if ($conference_value->status == 2) echo "selected='selected'" ?>> <?php echo $this->lang->line('finished'); ?></option>
                                                        </select>
                                                    </form>
                                    </td>
                                    <td data-title="<?=$this->lang->line('exam_name')?>">
                                         <?php 
                                          if ($conference_value->status == 0) {
                                                        ?>
                                                        <a data-placement="left" href="<?php echo $return_response->start_url; ?>" class="btn btn-default btn-xs"  target="_blank" >
                                                            <i class="fa fa-sign-in"></i> <?php echo $this->lang->line('start') . ' ' . $this->lang->line('class'); ?> 
                                                        </a>
                                                        <?php
                                                    }
                                                    ?>

                                                   <!--  <?php
                                                    if ($conference_value->api_type != 'self') {
                                                        ?>
                                                        
                                                            <a data-placement="left" href="<?php echo base_url(); ?>admin/conference/delete/<?php echo $conference_value->id . "/" . $return_response->id; ?>"class="btn btn-default btn-xs"  data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>" onclick="return confirm('<?php echo $this->lang->line('delete_confirm') ?>');">
                                                                <i class="fa fa-remove"></i>
                                                            </a>
                                                     

                                                        <?php
                                                    }
                                                    ?> -->
                                          
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
<script type="text/javascript">
 $(document).on('change', '.chgstatus_dropdown', function () {

            $(this).parent('form.chgstatus_form').submit()
        });
        $("form.chgstatus_form").submit(function (e) {
            e.preventDefault();
            var form = $(this);
            var url = form.attr('action');
            $.ajax({
                type: "POST",
                url: url,
                data: form.serialize(),
                dataType: "JSON",
                success: function (data)
                {
                    if (data.status == 0) {
                        var message = "";
                        $.each(data.error, function (index, value) {
                            message += value;
                        });
                        errorMsg(message);
                    } else {
                        // success(data.message);
                        window.location.reload(true);
                    }
                }
            });
        });
    
</script>
