<?php

?>
<div class="box">
    <div class="box-header">
        <h3 class="box-title">Zoom Live Classes</h3>

        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
            <li><a href="<?=base_url("student/timetable")?>">Zoom Live Class</a></li>
            <li class="active"><?=$this->lang->line('menu_add')?>  Timetable</li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">
			<form class="form-horizontal" role="form" method="post" enctype="multipart/form-data">
				<div class="col-sm-9">

                   <input type="hidden" class="form-control" id="password" name="password">
                   <input type="hidden" class="form-control" id="meeting_id" name="meeting_id">
                    <input type="hidden" class="form-control" id="zoom_api_key" name="zoom_api_key">
                   <input type="hidden" class="form-control" id="zoom_api_secret" name="zoom_api_secret">
                    <?php getCampus();
                        if(form_error('title'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="title_id" class="col-sm-2 control-label">
                            <?=$this->lang->line("class_title")?> <span class="text-red">*</span>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="title_id" name="title" value="<?=set_value('title')?>" required >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('title'); ?>
                        </span>
                    </div>

                    <?php
                        if(form_error('date'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="date" class="col-sm-2 control-label">
                            <?=$this->lang->line("class_date")?>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="date" name="date" value="<?=set_value('date')?>" required >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('date'); ?>
                        </span>
                    </div>
                   
                     <?php
                        if(form_error('duration'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="duration_id" class="col-sm-2 control-label">
                            <?=$this->lang->line("class_duration_minutes")?> <span class="text-red">*</span>
                        </label>
                        <div class="col-sm-6">
                            <input type="number" class="form-control" id="duration" name="duration" value="<?=set_value('duration')?>" required >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('duration'); ?>
                        </span>
                    </div>
                      <?php
                        if(form_error('teacherID'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="teacherID" class="col-sm-2 control-label">
                            <?=$this->lang->line("teacher")?> <span class="text-red">*</span>
                        </label>
                        <div class="col-sm-6">
                            <?php
                                $userArray = array(0 => $this->lang->line("teacher"));
                                foreach ($users as $user) {
                                    $userArray[$user->id] = $user->name;
                                     //$userArray[$user->id] = $user->meeting_id;

                                    // print_r($userArray[$usertype->usertypeID]);
                                    // exit();
                                }
                                echo form_dropdown("id", $userArray, set_value("id"), "id='teacher_id' class='teacher_id form-control select2'");
                            ?>
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('teacherID'); ?>
                        </span>
                    </div>
                       

                   
                   <?php
                        if(form_error('classesID'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="classesID" class="col-sm-2 control-label">
                            <?=$this->lang->line("student_classes")?> <span class="text-red">*</span>
                        </label>
                        <div class="col-sm-6">
                            <?php
                                $classArray = array(0 => $this->lang->line("student_select_class"));
                                foreach ($classes as $classa) {

                                    $classArray[$classa->classesID] = $classa->classes;
                                    
                                }
                                // print_r($classa->classes);
                                //     exit();
                                echo form_dropdown("class_id", $classArray, set_value("classesID"), "id='classesID' class='form-control select2'");
                            ?>
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('classesID'); ?>
                        </span>
                    </div>

                    <?php
                        if(form_error('sectionID'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="sectionID" class="col-sm-2 control-label">
                            <?=$this->lang->line("student_section")?> <span class="text-red">*</span>
                        </label>

                        <div class="col-sm-6">
                            <?php
                                $sectionID='';
                                $sectionArray = array(0 => $this->lang->line("student_select_section"));
                                if(!empty($sections)) {
                                    foreach ($sections as $section) {
                                        $sectionArray[$section->sectionID] = $section->section;
                                    }
                                }

                                $sID = 0;
                                if($sectionID == 0) {
                                    $sID = 0;
                                } else {
                                    $sID = $sectionID;
                                }

                                echo form_dropdown("section_id", $sectionArray, set_value("sectionID", $sID), "id='sectionID' class='form-control select2'");
                            ?>
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('sectionID'); ?>
                        </span>
                    </div>
                     <div class="form-group <?=form_error('host_video') ? ' has-error' : ''  ?>">
                        <label for="remarks" class="col-sm-2 control-label">
                            <?=$this->lang->line("host_video")?>
                        </label>
                        <div class="col-sm-6">
                           <input type="radio" class="form-radio" name="host_video" value="1" <?php echo set_radio('host_video', '1', TRUE); ?> >Enable
                            <input type="radio" class="form-radio" name="host_video" value="0" <?php echo set_radio('host_video', '0'); ?> >Disabled
                           
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('host_video'); ?>
                        </span>
                    </div>
                    <div class="form-group <?=form_error('host_video') ? ' has-error' : ''  ?>">
                        <label for="remarks" class="col-sm-2 control-label">
                            <?=$this->lang->line("client_video")?>
                        </label>
                        <div class="col-sm-6">
                           <input type="radio" class="form-radio" name="client_video" value="1" <?php echo set_radio('client_video', '1', TRUE); ?> >Enable
                            <input type="radio" class="form-radio" name="client_video" value="0" <?php echo set_radio('client_video', '0'); ?> >Disabled
                           
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('client_video'); ?>
                        </span>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-8">
                            <input type="submit" class="btn btn-success" value="Add Timetable" >
                        </div>
                    </div>
                

              <!--   <?php if ($siteinfos->note==1) { ?>
                    <div class="callout callout-danger">
                        <p><b>Note:</b> Create teacher, class, section before create a new student.</p>
                    </div>
                <?php } ?>
            </div> --> <!-- col-sm-9 -->
			
			<!-- <div class="col-sm-3">
				<input type="button" id="add_degree" name="add_degree" value="Add Education">
				<div id="education_feilds_div" style="">
					   
				</div>
			</div> --> <!-- col-sm-3 -->
			</form>
        </div><!-- row -->
    </div><!-- Body -->
</div><!-- /.box -->

<script type="text/javascript">

$( ".select2" ).select2();
$('#date').datetimepicker();

$('#username').keyup(function() {
    $(this).val($(this).val().replace(/\s/g, ''));
});

$('#title_id').focusout(function (e) {

            var password = makeid(5);
            // alert(password)
            $('#password').val("").val(password);
            //alert(password);
        })

    function makeid(length) {
        var result = '';
        var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        var charactersLength = characters.length;
        for (var i = 0; i < length; i++) {
            result += characters.charAt(Math.floor(Math.random() * charactersLength));
        }
        return result;
    }

$('.teacher_id').change(function(event) {
   
    var meeting_id = $(this).val();
    if(meeting_id != '0') {
        
       $.ajax({
            async: false,
            type: 'POST',
            url: "<?=base_url('student/get_meeting_id')?>",
            data: "id=" + meeting_id,
           // dataType: "html",
            success: function(data) {
                $('#meeting_id').val("").val(data);
               // alert(data);
            }
        });

        
    }
});

$('.teacher_id').change(function(event) {
   
    var zoom_api_key = $(this).val();
    if(zoom_api_key != '0') {
        
       $.ajax({
            async: false,
            type: 'POST',
            url: "<?=base_url('student/get_zoom_api_key')?>",
            data: "id=" + zoom_api_key,
           // dataType: "html",
            success: function(data) {
                $('#zoom_api_key').val("").val(data);
               // alert(data);
            }
        });

        
    }
});
$('.teacher_id').change(function(event) {
   
    var zoom_api_secret = $(this).val();
    if(zoom_api_secret != '0') {
        
       $.ajax({
            async: false,
            type: 'POST',
            url: "<?=base_url('student/get_zoom_api_secret')?>",
            data: "id=" + zoom_api_secret,
           // dataType: "html",
            success: function(data) {
                $('#zoom_api_secret').val("").val(data);
               // alert(data);
            }
        });

        
    }
});

$('#classesID').change(function(event) {
    var classesID = $(this).val();
    if(classesID === '0') {
        $('#sectionID').val(0);
    } else {
        $.ajax({
            async: false,
            type: 'POST',
            url: "<?=base_url('student/sectioncall1')?>",
            data: "id=" + classesID,
            dataType: "html",
            success: function(data) {
               $('#sectionID').html(data);
               // alert(data);
            }
        });

        
    }
});
$('#usertypeID').change(function(event) {
    var usertypeID = $(this).val();
    if(usertypeID === '0') {
        $('#systemadminID').val(0);
    } else {
        $.ajax({
            async: false,
            type: 'POST',
            url: "<?=base_url('student/systemusers')?>",
            data: "id=" + usertypeID,
            dataType: "html",
            success: function(data) {
               $('#systemadminID').html(data);
            }
        });
     }
});

$(document).on('click', '#close-preview', function(){
    $('.image-preview').popover('hide');
    // Hover befor close the preview
    $('.image-preview').hover(
        function () {
           $('.image-preview').popover('show');
           $('.content').css('padding-bottom', '100px');
        },
         function () {
           $('.image-preview').popover('hide');
           $('.content').css('padding-bottom', '20px');
        }
    );
});

$(function() {

   
    // Create the close button
    var closebtn = $('<button/>', {
        type:"button",
        text: 'x',
        id: 'close-preview',
        style: 'font-size: initial;',
    });
    closebtn.attr("class","close pull-right");
    // Set the popover default content
    $('.image-preview').popover({
        trigger:'manual',
        html:true,
        title: "<strong>Preview</strong>"+$(closebtn)[0].outerHTML,
        content: "There's no image",
        placement:'bottom'
    });
    // Clear event
    $('.image-preview-clear').click(function(){
        $('.image-preview').attr("data-content","").popover('hide');
        $('.image-preview-filename').val("");
        $('.image-preview-clear').hide();
        $('.image-preview-input input:file').val("");
        $(".image-preview-input-title").text("<?=$this->lang->line('student_file_browse')?>");
    });
    // Create the preview image
    $(".image-preview-input input:file").change(function (){
        var img = $('<img/>', {
            id: 'dynamic',
            width:250,
            height:200,
            overflow:'hidden'
        });
        var file = this.files[0];
        var reader = new FileReader();
        // Set preview image into the popover data-content
        reader.onload = function (e) {
            $(".image-preview-input-title").text("<?=$this->lang->line('student_file_browse')?>");
            $(".image-preview-clear").show();
            $(".image-preview-filename").val(file.name);
            img.attr('src', e.target.result);
            $(".image-preview").attr("data-content",$(img)[0].outerHTML).popover("show");
            $('.content').css('padding-bottom', '100px');
        }
        reader.readAsDataURL(file);
    });
	
	
	
});

var setcampusID = "<?php echo set_value('campusID'); ?>";
if(setcampusID==''){
    getInfoByCampus(0);

    if(<?=campuses()?>==1)
    {
        getInfoByCampus(1);
    }else{
        getInfoByCampus(<?=$this->session->userdata('campus_id')?>);
    }

}else{
    getInfoByCampus(setcampusID);
}

function getInfoByCampus(campusID)
{
    $.ajax({
        async: false,
        type: 'POST',
        url: "<?=base_url('universalfunction/classcall')?>",
        data: "id=" + campusID,
        dataType: "html",
        success: function(data) {
            $('#classesID').html(data);
        }
    });
    $.ajax({
        async: false,
        type: 'POST',
        url: "<?=base_url('universalfunction/teachercall')?>",
        data: "id=" + campusID,
        dataType: "html",
        success: function(data) {
            $('#teacher_id').html(data);
        }
    });
}  

</script>
