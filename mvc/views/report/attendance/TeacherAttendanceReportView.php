
<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa icon-attendancereport"></i> <?=$this->lang->line('panel_title')?></h3>
        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
            <li class="active"><?=$this->lang->line('menu_attendancereport')?></li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">

            <div class="col-sm-12">
                <?php CampusReport($this->session->userdata('campus_id'),4); ?>
                <div class="form-group col-sm-4" id="attendancetypeDiv">
                    <label><?=$this->lang->line("attendancereport_attendancetype")?><span class="text-red"> * </span></label>
                    <?php
                         $array = array(
                             "0" => $this->lang->line("attendancereport_please_select"),
                             "P" => $this->lang->line("attendancereport_present"),
                             "LE" => $this->lang->line("attendancereport_late_present_with_excuse"),
                             "L" => $this->lang->line("attendancereport_late_present"),
                             "A" => $this->lang->line("attendancereport_absent"),
                             "LA" => $this->lang->line("attendancereport_leave"),

                         );
                         echo form_dropdown("attendancetype", $array, set_value("attendancetype"), "id='attendancetype' class='form-control select2'");
                     ?>
                </div>

                

                

                <div class="form-group col-sm-4" id="dateDiv">
                    <label><?=$this->lang->line("attendancereport_date")?><span class="text-red"> * </span></label>
                    <input class="form-control" name="date" id="date" value="" type="text">
                </div>

                <div class="col-sm-4">
                    <button id="get_attendancereport" class="btn btn-success" style="margin-top:23px;"> <?=$this->lang->line("attendancereport_submit")?></button>
                </div>

            </div>

        </div><!-- row -->
    </div><!-- Body -->
</div><!-- /.box -->

<div id="load_attendance_report"></div>

<!-- <?php if($siteinfos->attendance == 'subject') { ?>
    <script type="text/javascript">
        $('#subjectDiv').show()
    </script>
<?php } else { ?>
    <script type="text/javascript">
        $('#subjectDiv').hide()
    </script>
<?php } ?> -->

<script type="text/javascript">
    $('.select2').select2();
    function printDiv(divID) {
        var oldPage = document.body.innerHTML;
        $('#headerImage').remove();
        $('.footerAll').remove();
        var divElements = document.getElementById(divID).innerHTML;
        var footer = "<center><img src='<?=base_url('uploads/images/'.$siteinfos->photo)?>' style='width:30px;' /></center>";
        var copyright = "<center><?=$siteinfos->footer?> | <?=$this->lang->line('attendancereport_hotline')?> : <?=$siteinfos->phone?></center>";
        document.body.innerHTML =
          "<html><head><title></title></head><body>" +
          "<center><img src='<?=base_url('uploads/images/'.$siteinfos->photo)?>' style='width:50px;' /></center>"
          + divElements + footer + copyright + "</body>";

        window.print();
        document.body.innerHTML = oldPage;
        window.location.reload();
    }

    
    function divHide() {
       
        $("#dateDiv").hide("slow");
      
    }

    function divShow() {
        
        $("#dateDiv").show("slow");
       
    }

    $(document).ready(function() {
        $("#attendancetype").val('0');
        $("#classesID").val(0);
        $("#sectionID").val('');
        <?php if($siteinfos->attendance == 'subject') { ?>
            $("#subjectID").val('');
        <?php } ?>
        divHide();
    });

    $(document).bind('click', function() {
        $('#date').datepicker({
            autoclose: true,
            format: 'dd-mm-yyyy',
            startDate:'<?=$schoolyearsessionobj->startingdate?>',
            endDate:'<?=$schoolyearsessionobj->endingdate?>',
            daysOfWeekDisabled: "<?=$siteinfos->weekends?>",
            datesDisabled: ["<?=$get_all_holidays;?>"], 
        }); 
    });

    $(document).on('change','#attendancetype', function() {
        $('#load_attendance_report').html('');

        var type = $(this).val();

        if(type != 0) {
            divShow();
        } else {
            $('#attendancetype').val(0);
            divHide();
        }
    });

    // $(document).on('change', '#classesID', function() {
    //     $('#load_attendance_report').html('');
    //     var id = $(this).val();
    //     if(id == 0) {
    //         $('#sectionID').html('<option value="">'+"<?=$this->lang->line("attendancereport_please_select")?>"+'</option>');
    //         $('#sectionID').val('');

    //         $('#subjectID').html('<option value="">'+"<?=$this->lang->line("attendancereport_please_select")?>"+'</option>');
    //         $('#subjectID').val('');
            
    //         $('#date').val('');
    //     } else {
    //         $.ajax({
    //             type: 'POST',
    //             url: "<?=base_url('attendancereport/getSection')?>",
    //             data: {"id" : id},
    //             dataType: "html",
    //             success: function(data) {
    //                $('#sectionID').html(data);
    //             }
    //         });

    //         $.ajax({
    //             type: 'POST',
    //             url: "<?=base_url('attendancereport/getSubject')?>",
    //             data: {"classID" : id},
    //             dataType: "html",
    //             success: function(data) {
    //                $('#subjectID').html(data);
    //             }
    //         });
    //     }
    // });

    // $(document).on('change', '#sectionID', function() {
    //     $('#load_attendance_report').html('');
    // });

    // $(document).on('change', '#subjectID', function() {
    //     $('#load_attendance_report').html('');
    // });
    
    $(document).on('change', '#date', function() {
     var data =  $('#load_attendance_report').html('');
   
    });

    $(document).on('click', '#get_attendancereport', function() {
        $('#load_attendance_report').html('');
        var error = 0;
        var field = {
            'attendancetype' : $('#attendancetype').val(),
            'campusID' : $('#campusID').val(),
            'date' : $('#date').val(),
        };

        error = validation_checker(field, error);

        if(error === 0) {
            makingPostDataPreviousofAjaxCall(field);
        }

    });

    function validation_checker(field, error){
        if (field['attendancetype'] == 0) {
            $('#attendancetypeDiv').addClass('has-error');
            error++;
        } else {
            $('#attendancetypeDiv').removeClass('has-error');
        }

        // if (field['classesID'] == 0) {
        //     $('#classesDiv').addClass('has-error');
        //     error++;
        // } else {
        //     $('#classesDiv').removeClass('has-error');
        // }

        if (field['date'] == '') {
            $('#dateDiv').addClass('has-error');
            error++;
        } else {
            $('#dateDiv').removeClass('has-error');
        }

        // <?php if($siteinfos->attendance == 'subject') { ?>
        //     if (field['subjectID'] == '') {
        //         $('#subjectDiv').addClass('has-error');
        //         error++;
        //     } else {
        //         $('#subjectDiv').removeClass('has-error');
        //     }

        // <?php } ?>

        return error;
    }

    function makingPostDataPreviousofAjaxCall(field) {

        passData = field;
        ajaxCall(passData);
        //console.log(passData);
    }

    function ajaxCall(passData) {
        $.ajax({
            type: 'POST',
            url: "<?=base_url('TeacherAttendancereport/getAttendacneReport')?>",
            data: passData,
            dataType: "html",
            success: function(data) {
                //alert(data);
                var response = JSON.parse(data);
                renderLoder(response, passData);
            }
        });
    }

    function renderLoder(response, passData) {
        if(response.status) {
            $('#load_attendance_report').html(response.render);
            for (var key in passData) {
                if (passData.hasOwnProperty(key)) {
                    $('#'+key).parent().removeClass('has-error');
                }
            }
        } else {
            for (var key in passData) {
                if (passData.hasOwnProperty(key)) {
                    $('#'+key).parent().removeClass('has-error');
                }
            }

            for (var key in response) {
                if (response.hasOwnProperty(key)) {
                    $('#'+key).parent().addClass('has-error');
                }
            }
        }
    }
    
</script>
