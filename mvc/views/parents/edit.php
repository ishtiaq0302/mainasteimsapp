
<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa fa-user"></i> <?=$this->lang->line('panel_title')?></h3>

       
        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
            <li><a href="<?=base_url("parents/index/$campusID")?>"><?=$this->lang->line('menu_parents')?></a></li>
            <li class="active"><?=$this->lang->line('menu_edit')?> <?=$this->lang->line('menu_parents')?></li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">
            <div class="col-sm-10">

                <form class="form-horizontal" role="form" method="post" enctype="multipart/form-data">

					<h3>Father Information</h3>
                    
                    <?php getCampus($campusID); ?>

                    <?php 
                        if(form_error('father_name')) 
                            echo "<div class='form-group has-error' >";
                        else     
                            echo "<div class='form-group' >";
                    ?>
                        <label for="father_name" class="col-sm-2 control-label">
                            <?=$this->lang->line("parents_father_name")?>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="father_name" name="father_name" value="<?=set_value('father_name', $parents->father_name)?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('father_name'); ?>
                        </span>
                    </div>

                    
                    <?php 
                        if(form_error('father_profession')) 
                            echo "<div class='form-group has-error' >";
                        else     
                            echo "<div class='form-group' >";
                    ?>
                        <label for="father_profession" class="col-sm-2 control-label">
                            <?=$this->lang->line("parents_father_profession")?>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="father_profession" name="father_profession" value="<?=set_value('father_profession', $parents->father_profession)?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('father_profession'); ?>
                        </span>
                    </div>

                    <?php 
                        if(form_error('email')) 
                            echo "<div class='form-group has-error' >";
                        else     
                            echo "<div class='form-group' >";
                    ?>
                        <label for="email" class="col-sm-2 control-label">
                            <?=$this->lang->line("parents_email")?>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="email" name="email" value="<?=set_value('email', $parents->email)?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('email'); ?>
                        </span>
                    </div>

                    <?php 
                        if(form_error('phone')) 
                            echo "<div class='form-group has-error' >";
                        else     
                            echo "<div class='form-group' >";
                    ?>
                        <label for="phone" class="col-sm-2 control-label">
                            <?=$this->lang->line("parents_phone")?>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="phone" name="phone" value="<?=set_value('phone', $parents->phone)?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('phone'); ?>
                        </span>
                    </div>

                    <?php 
                        if(form_error('address')) 
                            echo "<div class='form-group has-error' >";
                        else     
                            echo "<div class='form-group' >";
                    ?>
                        <label for="address" class="col-sm-2 control-label">
                            <?=$this->lang->line("parents_address")?>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="address" name="address" value="<?=set_value('address', $parents->address)?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('address'); ?>
                        </span>
                    </div>
					
					<?php
                        if(form_error('father_cnic'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="father_cnic" class="col-sm-2 control-label">
                            <?=$this->lang->line("father_cnic")?>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="father_cnic" name="father_cnic" value="<?=set_value('father_cnic', $parents->father_cnic)?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('father_cnic'); ?>
                        </span>
                    </div>
					 <?php
                        if(form_error('father_qualification'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="father_qualification" class="col-sm-2 control-label">
                            <?=$this->lang->line("father_qualification")?>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="father_qualification" name="father_qualification" value="<?=set_value('father_qualification', $parents->father_qualification)?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('father_qualification'); ?>
                        </span>
                    </div>
					<?php
                        if(form_error('father_office_addresss'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="father_office_addresss" class="col-sm-2 control-label">
                            <?=$this->lang->line("father_office_addresss")?>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="father_office_addresss" name="father_office_addresss" value="<?=set_value('father_office_addresss', $parents->father_office_addresss)?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('father_office_addresss'); ?>
                        </span>
                    </div>
					<?php
                        if(form_error('father_nationality'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="father_nationality" class="col-sm-2 control-label">
                            <?=$this->lang->line('father_nationality')?>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="father_nationality" name="father_nationality" value="<?=set_value('father_nationality', $parents->father_nationality)?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('father_nationality'); ?>
                        </span>
                    </div>
					
					<h3>Mother Information</h3>
					<?php 
                        if(form_error('mother_name')) 
                            echo "<div class='form-group has-error' >";
                        else     
                            echo "<div class='form-group' >";
                    ?>
                        <label for="mother_name" class="col-sm-2 control-label">
                            <?=$this->lang->line("parents_mother_name")?>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="mother_name" name="mother_name" value="<?=set_value('mother_name', $parents->mother_name)?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('mother_name'); ?>
                        </span>
                    </div>
					<?php 
                        if(form_error('mother_profession')) 
                            echo "<div class='form-group has-error' >";
                        else     
                            echo "<div class='form-group' >";
                    ?>
                        <label for="mother_profession" class="col-sm-2 control-label">
                            <?=$this->lang->line("parents_mother_profession")?>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="mother_profession" name="mother_profession" value="<?=set_value('mother_profession', $parents->mother_profession)?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('mother_profession'); ?>
                        </span>
                    </div>
					<?php
                        if(form_error('mother_phone'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="mother_phone" class="col-sm-2 control-label">
                            <?=$this->lang->line("mother_phone")?>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="mother_phone" name="mother_phone" value="<?=set_value('mother_phone', $parents->mother_phone)?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('mother_phone'); ?>
                        </span>
                    </div>

                    <?php
                        if(form_error('mother_address'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="mother_address" class="col-sm-2 control-label">
                            <?=$this->lang->line("mother_address")?>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="mother_address" name="mother_address" value="<?=set_value('mother_address', $parents->mother_address)?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('mother_address'); ?>
                        </span>
                    </div>
					
					 <?php
                        if(form_error('mother_cnic'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="mother_cnic" class="col-sm-2 control-label">
                            <?=$this->lang->line("mother_cnic")?>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="mother_cnic" name="mother_cnic" value="<?=set_value('mother_cnic', $parents->mother_cnic)?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('mother_cnic'); ?>
                        </span>
                    </div>
					 <?php
                        if(form_error('mother_qualification'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="mother_qualification" class="col-sm-2 control-label">
                            <?=$this->lang->line("mother_qualification")?>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="mother_qualification" name="mother_qualification" value="<?=set_value('mother_qualification', $parents->mother_qualification)?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('mother_qualification'); ?>
                        </span>
                    </div>
					<?php
                        if(form_error('mother_office_addresss'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="mother_office_addresss" class="col-sm-2 control-label">
                            <?=$this->lang->line("mother_office_addresss")?>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="mother_office_addresss" name="mother_office_addresss" value="<?=set_value('mother_office_addresss', $parents->mother_office_addresss)?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('mother_office_addresss'); ?>
                        </span>
                    </div>
					<?php
                        if(form_error('mother_nationality'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="mother_nationality" class="col-sm-2 control-label">
                            <?=$this->lang->line('mother_nationality')?>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="mother_nationality" name="mother_nationality" value="<?=set_value('mother_nationality', $parents->mother_nationality)?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('mother_nationality'); ?>
                        </span>
                    </div>
					
					<h3>Guardian Information (If appropriate)</h3>
					<?php 
                        if(form_error('name')) 
                            echo "<div class='form-group has-error' >";
                        else     
                            echo "<div class='form-group' >";
                    ?>
                        <label for="name_id" class="col-sm-2 control-label">
                            <?=$this->lang->line("parents_guargian_name")?> <span class="text-red">*</span>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="name_id" name="name" value="<?=set_value('name', $parents->name)?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('name'); ?>
                        </span>
                    </div>
					
					<?php
                        if(form_error('guardian_profession'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="guardian_profession" class="col-sm-2 control-label">
                            <?=$this->lang->line("guardian_profession")?>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="guardian_profession" name="guardian_profession" value="<?=set_value('guardian_profession', $parents->guardian_profession)?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('guardian_profession'); ?>
                        </span>
                    </div>
					<?php
                        if(form_error('guardian_phone'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="guardian_phone" class="col-sm-2 control-label">
                            <?=$this->lang->line("guardian_phone")?>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="guardian_phone" name="guardian_phone" value="<?=set_value('guardian_phone', $parents->guardian_phone)?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('guardian_phone'); ?>
                        </span>
                    </div>

                    <?php
                        if(form_error('guardian_address'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="guardian_address" class="col-sm-2 control-label">
                            <?=$this->lang->line("guardian_address")?>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="guardian_address" name="guardian_address" value="<?=set_value('guardian_address', $parents->guardian_address)?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('guardian_address'); ?>
                        </span>
                    </div>
					
					 <?php
                        if(form_error('guardian_cnic'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="guardian_cnic" class="col-sm-2 control-label">
                            <?=$this->lang->line("guardian_cnic")?>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="guardian_cnic" name="guardian_cnic" value="<?=set_value('guardian_cnic', $parents->guardian_cnic)?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('guardian_cnic'); ?>
                        </span>
                    </div>
					 <?php
                        if(form_error('guardian_qualification'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="guardian_qualification" class="col-sm-2 control-label">
                            <?=$this->lang->line("guardian_qualification")?>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="guardian_qualification" name="guardian_qualification" value="<?=set_value('guardian_qualification', $parents->guardian_qualification)?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('guardian_qualification'); ?>
                        </span>
                    </div>
					<?php
                        if(form_error('guardian_office_addresss'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="guardian_office_addresss" class="col-sm-2 control-label">
                            <?=$this->lang->line("guardian_office_addresss")?>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="guardian_office_addresss" name="guardian_office_addresss" value="<?=set_value('guardian_office_addresss', $parents->guardian_office_addresss)?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('guardian_office_addresss'); ?>
                        </span>
                    </div>
					<?php
                        if(form_error('guardian_nationality'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="guardian_nationality" class="col-sm-2 control-label">
                            <?=$this->lang->line('guardian_nationality')?>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="guardian_nationality" name="guardian_nationality" value="<?=set_value('guardian_nationality', $parents->guardian_nationality)?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('guardian_nationality'); ?>
                        </span>
                    </div>
					<?php
                        if(form_error('guardian_realation_with_child'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="guardian_realation_with_child" class="col-sm-2 control-label">
                            <?=$this->lang->line('guardian_realation_with_child')?>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="guardian_realation_with_child" name="guardian_realation_with_child" value="<?=set_value('guardian_realation_with_child', $parents->guardian_realation_with_child)?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('guardian_realation_with_child'); ?>
                        </span>
                    </div>
					
					
					<h3>User Detail</h3>
                    <?php
                        if(form_error('photo'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="photo" class="col-sm-2 control-label">
                            <?=$this->lang->line("parents_photo")?>
                        </label>
                        <div class="col-sm-6">
                            <div class="input-group image-preview">
                                <input type="text" class="form-control image-preview-filename" disabled="disabled">
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default image-preview-clear" style="display:none;">
                                        <span class="fa fa-remove"></span>
                                        <?=$this->lang->line('parents_clear')?>
                                    </button>
                                    <div class="btn btn-success image-preview-input">
                                        <span class="fa fa-repeat"></span>
                                        <span class="image-preview-input-title">
                                        <?=$this->lang->line('parents_file_browse')?></span>
                                        <input type="file" accept="image/png, image/jpeg, image/gif" name="photo"/>
                                    </div>
                                </span>
                            </div>
                        </div>

                        <span class="col-sm-4">
                            <?php echo form_error('photo'); ?>
                        </span>
                    </div>

                    <?php 
                        if(form_error('username')) 
                            echo "<div class='form-group has-error' >";
                        else     
                            echo "<div class='form-group' >";
                    ?>
                        <label for="username" class="col-sm-2 control-label">
                            <?=$this->lang->line("parents_username")?> <span class="text-red">*</span>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="username" name="username" value="<?=set_value('username', $parents->username)?>" >
                        </div>
                         <span class="col-sm-4 control-label">
                            <?php echo form_error('username'); ?>
                        </span>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-8">
                            <input type="submit" class="btn btn-success" value="<?=$this->lang->line("update_parents")?>" >
                        </div>
                    </div>

                </form>
            </div> <!-- col-sm-8 -->
        </div><!-- row -->
    </div><!-- Body -->
</div><!-- /.box -->
<script type="text/javascript">
$('#username').keyup(function() {
    $(this).val($(this).val().replace(/\s/g, ''));
});
$(document).on('click', '#close-preview', function(){ 
    $('.image-preview').popover('hide');
    // Hover befor close the preview
    $('.image-preview').hover(
        function () {
           $('.image-preview').popover('show');
           $('.content').css('padding-bottom', '130px');
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
        $(".image-preview-input-title").text("<?=$this->lang->line('parents_file_browse')?>"); 
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
            $(".image-preview-input-title").text("<?=$this->lang->line('parents_file_browse')?>");
            $(".image-preview-clear").show();
            $(".image-preview-filename").val(file.name);            
            img.attr('src', e.target.result);
            $(".image-preview").attr("data-content",$(img)[0].outerHTML).popover("show");
            $('.content').css('padding-bottom', '130px');
        }        
        reader.readAsDataURL(file);
    });  
});

$('#campusID').change(function(event) {
    var campusID = $(this).val();
    window.location.href = "<?=base_url('parents/edit/'.$mainID.'/')?>"+campusID;
});

</script>
