<?php if ($siteinfos->note==1) { ?>
    <div class="callout callout-danger">
        <p><b>Note:</b> Select Academic year & class</p>
    </div>
<?php } ?>
<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa icon-promotion"></i> <?=$this->lang->line('panel_title')?></h3>


        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
            <li class="active"><?=$this->lang->line('menu_promotion')?></li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">
            <form role="form" method="post" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="col-sm-12">

                            <div class="col-sm-12 list-group-item list-group-item-warning">
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label for="schoolyear" class="control-label">
                                            <?=$this->lang->line('promotion_school_year')?> <span class="text-red">*</span>
                                        </label>
                                        <?php
                                            $array = array();
                                            foreach ($schoolyears as $schoolyear) {
                                                $array[$schoolyear->schoolyearID] = $schoolyear->schoolyear;
                                            }

                                            $array[$siteinfos->school_year] = $array[$siteinfos->school_year].' (Default)';

                                            echo form_dropdown("schoolyear", $array, set_value("schoolyear", $siteinfos->school_year), "id='schoolyear' class='form-control select2'");
                                        ?>
                                    </div>
                                </div>

                                <?php getCampusView(0,3); ?>

                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label for="classesID" class="control-label">
                                            <?=$this->lang->line("promotion_classes")?> <span class="text-red">*</span>
                                        </label>

                                        <?php
                                            $array = array("0" => $this->lang->line("promotion_select_class"));
                                            foreach ($classes as $classa) {
                                                $array[$classa->classesID] = $classa->classes;
                                            }
                                            echo form_dropdown("classesID", $array, set_value("classesID"), "id='classesID' class='form-control select2'");
                                        ?>
                                    </div>
                                </div>


                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label for="sectionID" class="control-label">
                                            <?=$this->lang->line("mark_section")?> <span class="text-red">*</span>
                                        </label>

                                        <?php
                                            $arraysection = array('0' => $this->lang->line("mark_select_section"));
                                            if($sections != 0) {
                                                foreach ($sections as $section) {
                                                    $arraysection[$section->sectionID] = $section->section;
                                                }
                                            }

                                            echo form_dropdown("sectionID", $arraysection, set_value("sectionID", $set_section), "id='sectionID' class='form-control select2'");
                                        ?>
                                    </div>
                                </div>

                            </div>
                        </div>
                        
                    </div>
                </div>
            </form>
        </div><!-- row -->
    </div><!-- Body -->
</div><!-- /.box -->

<script type="text/javascript">
    $('.select2').select2();
    $('#sectionID').change(function() {
        //var classesID = $(this).val();
        var sectionID = $(this).val();
        var classesID = $('#classesID').val();
        var schoolyearID = $('#schoolyear').val();
        var campusID = $('#campusID').val();
        if(classesID == 0) {
            $('#hide-table').hide();
        } else {
            $.ajax({
                type: 'POST',
                url: "<?=base_url('promotion/promotion_list')?>",
                data: {"id" : classesID, "year" : schoolyearID, "campusID" : campusID, "sectionID" : sectionID},
                dataType: "html",
                success: function(data) {
                    window.location.href = data;
                }
            });
        }
    });

    $("#classesID").change(function() {
    var id = $(this).val();
    if(parseInt(id)) {
            $.ajax({
                type: 'POST',
                url: "<?=base_url('mark/sectioncall')?>",
                data: {"id" : id},
                dataType: "html",
                success: function(data) {
                   $('#sectionID').html(data);
                }
            });
    }
});

var setcampusID = "<?php echo set_value('campusID'); ?>";
if(setcampusID==''){
    if(<?=campuses()?>==1 && <?=$this->session->userdata('adminLogin')?>==1)
    {
        getInfoByCampus(<?=$this->session->userdata('accountCampusID')?>);
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
}      
</script>
