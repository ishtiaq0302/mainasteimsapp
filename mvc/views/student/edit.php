
<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa icon-student"></i> <?=$this->lang->line('panel_title')?></h3>

       
        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
            <li><a href="<?=base_url("student/index/$campusID/$set")?>"><?=$this->lang->line('menu_student')?></a></li>
            <li class="active"><?=$this->lang->line('menu_edit')?> <?=$this->lang->line('menu_student')?></li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">
			<form class="form-horizontal" role="form" method="post" enctype="multipart/form-data">
				<div class="col-sm-9">

                    <?php getCampus($campusID); ?>
                     <?php /*

                        if(form_error('campusID')) 

                            echo "<div class='form-group has-error' >";

                        else     

                            echo "<div class='form-group' >";

                     

                        <label for="campusID" class="col-sm-2 control-label">

                            <?=$this->lang->line("student_campus") <span class="text-red">*</span>

                        </label>

                        <div class="col-sm-6">

                            <?php

                             //   $campusArray = array(0 => $this->lang->line("student_select_campus"));

                                foreach ($campuse as $campus) {

                                    $campusArray[$campus->campusID] = $campus->name;

                                }

                                echo form_dropdown("campusID", $campusArray, set_value("campusID", $campusID), "id='campusID' class='form-control select2' required='required' ");

                           

                        </div>

                        <span class="col-sm-4 control-label">

                            <?php echo form_error('campusID'); 

                        </span>

                    </div> */ ?>


                    <?php 
                        if(form_error('name')) 
                            echo "<div class='form-group has-error' >";
                        else     
                            echo "<div class='form-group' >";
                    ?>
                        <label for="name_id" class="col-sm-2 control-label">
                            <?=$this->lang->line("student_name")?> <span class="text-red">*</span>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="name_id" name="name" value="<?=set_value('name', $student->name)?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('name'); ?>
                        </span>
                    </div>

                    <?php 
                        if(form_error('guargianID')) 
                            echo "<div class='form-group has-error' >";
                        else     
                            echo "<div class='form-group' >";
                    ?>
                        <label for="guargianID" class="col-sm-2 control-label">
                            <?=$this->lang->line("student_guargian")?>
                        </label>
                        <div class="col-sm-6">
                            <?php
                                $array = array('0' => $this->lang->line('student_select_guargian'));
                                foreach ($parents as $parent) {
                                    $parentsemail = '';
                                    if($parent->email) {
                                        $parentsemail = " (" . $parent->email ." )";
                                    }
                                    $array[$parent->parentsID] = $parent->name.$parentsemail;
                                }
                                echo form_dropdown("guargianID", $array, set_value("guargianID", $student->parentID), "id='guargianID' class='form-control guargianID select2'");
                            ?>
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('guargianID'); ?>
                        </span>
                    </div>

                    <?php 
                        if(form_error('dob')) 
                            echo "<div class='form-group has-error' >";
                        else     
                            echo "<div class='form-group' >";
                    ?>
                        <label for="dob" class="col-sm-2 control-label">
                            <?=$this->lang->line("student_dob")?>
                        </label>
                        <div class="col-sm-6">
                            <?php $dob = ''; if($student->dob) { $dob = date("d-m-Y", strtotime($student->dob)); }  ?>
                            <input type="text" class="form-control" id="dob" name="dob" value="<?=set_value('dob', $dob)?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('dob'); ?>
                        </span>
                    </div>

					<?php
                        if(form_error('student_pob'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="student_pob" class="col-sm-2 control-label">
                            <?=$this->lang->line("student_pob")?> 
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="student_pob" name="student_pob" value="<?=set_value('student_pob',$student->student_pob)?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('student_pob'); ?>
                        </span>
                    </div>
					
					<?php
                        if(form_error('emergency_contact_no'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="emergency_contact_no" class="col-sm-2 control-label">
                            <?=$this->lang->line("emergency_contact_no")?> 
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="emergency_contact_no" name="emergency_contact_no" value="<?=set_value('emergency_contact_no',$student->emergency_contact_no)?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('emergency_contact_no'); ?>
                        </span>
                    </div>
					
					<?php
                        if(form_error('emergency_contact_relation'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="emergency_contact_relation" class="col-sm-2 control-label">
                            <?=$this->lang->line("emergency_contact_relation")?> 
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="emergency_contact_relation" name="emergency_contact_relation" value="<?=set_value('emergency_contact_relation',$student->emergency_contact_relation)?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('emergency_contact_relation'); ?>
                        </span>
                    </div>
					
                    <?php 
                        if(form_error('sex')) 
                            echo "<div class='form-group has-error' >";
                        else     
                            echo "<div class='form-group' >";
                    ?>
                        <label for="sex" class="col-sm-2 control-label">
                            <?=$this->lang->line("student_sex")?>
                        </label>
                        <div class="col-sm-6">
                            <?php 
                                echo form_dropdown("sex", array($this->lang->line('student_sex_male') => $this->lang->line('student_sex_male'), $this->lang->line('student_sex_female') => $this->lang->line('student_sex_female')), set_value("sex", $student->sex), "id='sex' class='form-control'");
                            ?>
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('sex'); ?>
                        </span>

                    </div>

                    <?php 
                        if(form_error('bloodgroup')) 
                            echo "<div class='form-group has-error' >";
                        else     
                            echo "<div class='form-group' >";
                    ?>
                        <label for="bloodgroup" class="col-sm-2 control-label">
                            <?=$this->lang->line("student_bloodgroup")?>
                        </label>
                        <div class="col-sm-6">
                            <?php 
                                $bloodArray = array(
                                    '0' => $this->lang->line('student_select_bloodgroup'),
                                    'A+' => 'A+',
                                    'A-' => 'A-',
                                    'B+' => 'B+',
                                    'B-' => 'B-',
                                    'O+' => 'O+',
                                    'O-' => 'O-',
                                    'AB+' => 'AB+',
                                    'AB-' => 'AB-'
                                );
                                echo form_dropdown("bloodgroup", $bloodArray, set_value("bloodgroup", $student->bloodgroup), "id='bloodgroup' class='form-control select2'"); 
                            ?>
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('bloodgroup'); ?>
                        </span>
                    </div>

                   
                    <?php 
                        if(form_error('religion')) 
                            echo "<div class='form-group has-error' >";
                        else     
                            echo "<div class='form-group' >";
                    ?>
                        <label for="religion" class="col-sm-2 control-label">
                            <?=$this->lang->line("student_religion")?>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="religion" name="religion" value="<?=set_value('religion', $student->religion)?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('religion'); ?>
                        </span>
                    </div>

                    <?php 
                        if(form_error('email')) 
                            echo "<div class='form-group has-error' >";
                        else     
                            echo "<div class='form-group' >";
                    ?>
                        <label for="email" class="col-sm-2 control-label">
                            <?=$this->lang->line("student_email")?>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="email" name="email" value="<?=set_value('email', $student->email)?>" >
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
                            <?=$this->lang->line("student_phone")?>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="phone" name="phone" value="<?=set_value('phone', $student->phone)?>" >
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
                            <?=$this->lang->line("student_address")?>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="address" name="address" value="<?=set_value('address', $student->address)?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('address'); ?>
                        </span>
                    </div>

                    <?php 
                        if(form_error('state')) 
                            echo "<div class='form-group has-error' >";
                        else     
                            echo "<div class='form-group' >";
                    ?>
                        <label for="state" class="col-sm-2 control-label">
                            <?=$this->lang->line("student_state")?>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="state" name="state" value="<?=set_value('state', $student->state)?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('state'); ?>
                        </span>
                    </div>

                    <?php 
                        if(form_error('country')) 
                            echo "<div class='form-group has-error' >";
                        else     
                            echo "<div class='form-group' >";
                    ?>
                        <label for="country" class="col-sm-2 control-label">
                            <?=$this->lang->line("student_country")?>
                        </label>
                        <div class="col-sm-6">
                            <?php
                                $country['0'] = $this->lang->line('student_select_country');  
                                foreach ($allcountry as $allcountryKey => $allcountryit) {
                                    $country[$allcountryKey] = $allcountryit;
                                }
                            ?>
                            <?php 
                                echo form_dropdown("country", $country, set_value("country", $student->country), "id='country' class='form-control select2'");
                            ?>
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('country'); ?>
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
                                echo form_dropdown("classesID", $classArray, set_value("classesID", $student->srclassesID), "id='classesID' class='form-control select2'");
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
                                $array = array(0 => $this->lang->line("student_select_section"));
                                foreach ($sections as $section) {
                                    $array[$section->sectionID] = $section->section;
                                }
                                echo form_dropdown("sectionID", $array, set_value("sectionID", $student->srsectionID), "id='sectionID' class='form-control select2'");
                            ?>
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('sectionID'); ?>
                        </span>
                    </div>


                   
                    

                    <div class="form-group <?=form_error('studentGroupID') ? ' has-error' : ''  ?>">
                        <label for="studentGroupID" class="col-sm-2 control-label">
                            <?=$this->lang->line("student_studentgroup")?>
                        </label>
                        <div class="col-sm-6">
                            <?php
                            $groupArray = array(0 => $this->lang->line("student_select_studentgroup"));
                            if(!empty($studentgroups)) {
                                foreach ($studentgroups as $studentgroup) {
                                    $groupArray[$studentgroup->studentgroupID] = $studentgroup->group;
                                }
                            }
                            echo form_dropdown("studentGroupID", $groupArray, set_value("studentGroupID", $student->srstudentgroupID), "id='studentGroupID' class='form-control select2'");
                            ?>
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('studentGroupID'); ?>
                        </span>
                    </div>

                    <div class="form-group <?=form_error('optionalSubjectID') ? ' has-error' : ''  ?>">
                        <label for="optionalSubjectID" class="col-sm-2 control-label">
                            <?=$this->lang->line("student_optionalsubject")?>
                        </label>
                        <div class="col-sm-6">
                            <?php
                            $optionalSubjectArray = array(0 => $this->lang->line("student_select_optionalsubject"));
                            if($optionalSubjects != "empty") {
                                foreach ($optionalSubjects as $optionalSubject) {
                                    $optionalSubjectArray[$optionalSubject->subjectID] = $optionalSubject->subject;
                                }
                            }

                            echo form_dropdown("optionalSubjectID", $optionalSubjectArray, set_value("optionalSubjectID", $student->sroptionalsubjectID), "id='optionalSubjectID' class='form-control select2'");
                            ?>
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('optionalSubjectID'); ?>
                        </span>
                    </div>

                    <?php 
                        if(form_error('registerNO')) 
                            echo "<div class='form-group has-error' >";
                        else     
                            echo "<div class='form-group' >";
                    ?>
                        <label for="registerNO" class="col-sm-2 control-label">
                            <?=$this->lang->line("student_registerNO")?> <span class="text-red">*</span>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="registerNO" name="registerNO" readonly="readonly" value="<?=set_value('registerNO', $student->srregisterNO)?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('registerNO'); ?>
                        </span>
                    </div>

                    <?php 
                        if(form_error('roll')) 
                            echo "<div class='form-group has-error' >";
                        else     
                            echo "<div class='form-group' >";
                    ?>
                        <label for="roll" class="col-sm-2 control-label">
                            <?=$this->lang->line("student_roll")?> 
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="roll" name="roll" value="<?=set_value('roll', $student->srroll)?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('roll'); ?>
                        </span>
                    </div>

                    <?php
                        if(form_error('photo'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="photo" class="col-sm-2 control-label">
                            <?=$this->lang->line("student_photo")?>
                        </label>
                        <div class="col-sm-6">
                            <div class="input-group image-preview">
                                <input type="text" class="form-control image-preview-filename" disabled="disabled">
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default image-preview-clear" style="display:none;">
                                        <span class="fa fa-remove"></span>
                                        <?=$this->lang->line('student_clear')?>
                                    </button>
                                    <div class="btn btn-success image-preview-input">
                                        <span class="fa fa-repeat"></span>
                                        <span class="image-preview-input-title">
                                        <?=$this->lang->line('student_file_browse')?></span>
                                        <input type="file" accept="image/png, image/jpeg, image/gif" name="photo"/>
                                    </div>
                                </span>
                            </div>
                        </div>

                        <span class="col-sm-4">
                            <?php echo form_error('photo'); ?>
                        </span>
                    </div>

                    <div class="form-group <?=form_error('extraCurricularActivities') ? ' has-error' : ''  ?>">
                        <label for="extraCurricularActivities" class="col-sm-2 control-label">
                            <?=$this->lang->line("student_extracurricularactivities")?>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="extraCurricularActivities" name="extraCurricularActivities" value="<?=set_value('extraCurricularActivities', $student->extracurricularactivities)?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('extraCurricularActivities'); ?>
                        </span>
                    </div>

                    <div class="form-group <?=form_error('admission_result') ? ' has-error' : ''  ?>">
                        <label for="remarks" class="col-sm-2 control-label">
                            <?=$this->lang->line("admission_result")?>
                        </label>
                        <div class="col-sm-6">
                            <input type="radio" class="form-radio" name="admission_result" value="pass" <?php if(empty($student->admission_result) || $student->admission_result=='pass'){echo 'checked="checked"'; } ?>>Pass
							<input type="radio" class="form-radio" name="admission_result" value="fail" <?=$student->admission_result=='fail'?'checked="checked"':''?> >Fail
							<input type="radio" class="form-radio" name="admission_result" value="s_permit" <?=$student->admission_result=='s_permit'?'checked="checked"':''?> >Special Permit
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('admission_result'); ?>
                        </span>
                    </div>
					
					<div class="form-group <?=form_error('remarks') ? ' has-error' : ''  ?>">
                        <label for="remarks" class="col-sm-2 control-label">
                            <?=$this->lang->line("student_remarks")?>
                        </label>
                        <div class="col-sm-6">
                            <textarea class="form-control" id="remarks" name="remarks"  ><?=set_value('remarks', $student->remarks)?></textarea>
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('remarks'); ?>
                        </span>
                    </div>
					
					<?php
                        if(form_error('monthly_tuttion_fee'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="monthly_tuttion_fee" class="col-sm-2 control-label">
                            <?=$this->lang->line("monthly_tuttion_fee")?> 
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="monthly_tuttion_fee" name="monthly_tuttion_fee" value="<?=set_value('monthly_tuttion_fee', $student->monthly_tuttion_fee)?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('monthly_tuttion_fee'); ?>
                        </span>
                    </div>
					<?php
                        if(form_error('admission_fee'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="admission_fee" class="col-sm-2 control-label">
                            <?=$this->lang->line("admission_fee")?> 
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="admission_fee" name="admission_fee" value="<?=set_value('admission_fee', $student->admission_fee)?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('admission_fee'); ?>
                        </span>
                    </div>
					<?php
                        if(form_error('registration_fee'))
                            echo "<div class='form-group has-error' >";
                        else
                            echo "<div class='form-group' >";
                    ?>
                        <label for="registration_fee" class="col-sm-2 control-label">
                            <?=$this->lang->line("registration_fee")?> 
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="registration_fee" name="registration_fee" value="<?=set_value('registration_fee', $student->registration_fee)?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('registration_fee'); ?>
                        </span>
                    </div>

					<div class="form-group <?=form_error('admission_status') ? ' has-error' : ''  ?>">
                        <label for="remarks" class="col-sm-2 control-label">
                            <?=$this->lang->line("admission_status")?>
                        </label>
                        <div class="col-sm-6">
                            <input type="radio" class="form-radio" name="admission_status" value="TRUE" <?php if($student->admission_status != '' || $student->admission_status=='TRUE'){echo 'checked="checked"'; } ?>>Admitted
							<input type="radio" class="form-radio" name="admission_status" value="FALSE" <?=$student->admission_status=='FALSE'?'checked="checked"':''?> >Not Admitted
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('admission_status'); ?>
                        </span>
                    </div>
					
					
                    <?php 
                        if(form_error('username')) 
                            echo "<div class='form-group has-error' >";
                        else     
                            echo "<div class='form-group' >";
                    ?>
                        <label for="username" class="col-sm-2 control-label">
                            <?=$this->lang->line("student_username")?> <span class="text-red">*</span>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="username" name="username" value="<?=set_value('username', $student->username)?>" >
                        </div>
                         <span class="col-sm-4 control-label">
                            <?php echo form_error('username'); ?>
                        </span>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-8">
                            <input type="submit" class="btn btn-success" value="<?=$this->lang->line("update_student")?>" >
                        </div>
                    </div>

				</div> <!-- col-sm-9 -->
				<div class="col-sm-3">
					<input type="button" id="add_degree" name="add_degree" value="Add Education">
					<div id="education_feilds_div" style="">
						   <?php
								$html = '';
								if(!empty($student->education_detail)){
									$edu_bacground = json_decode($student->education_detail,true);
									foreach($edu_bacground as $key =>$edu_bac){
										$html .= '<div class="degree_block col-lg-12 col-md-12" style="border: 1px #002166 dashed;margin-top: 5px;">
													<div class="col-lg-12 col-md-12">
														<div class="form-group " style="">
															<label class="" for="name_of_school">Name of School</label>
															<button type="button" name="delete_degree" title="Delete"  class="delete_degree pull-right btn btn-xs btn-danger" ><i class="fa fa-trash fa-white"></i></button>
															<input type="text" name="name_of_school[]" class="name_of_school form-control" value="'.$edu_bac['name_of_school'].'">
														</div>
													</div>
													<div class="col-lg-6 col-md-6">
														<div class="form-group " style="">
															<label class="" for="start_year">From</label>
															<input type="text" name="start_year[]" class="start_year form-control" value="'.$edu_bac['start_year'].'">
														</div>
													</div>
													<div class="col-lg-6 col-md-6">
														<div class="form-group " style="">
															<label class="" for="status">To</label>
															<input type="text" name="end_year[]"  class="end_year form-control" value="'.$edu_bac['end_year'].'">
														</div>
													</div>
													<div class="col-lg-12 col-md-12">
														<div class="form-group " style="margin-top: 5px;">
															<label class="" for="up_to_class">Up to class</label>
															<input type="text" name="up_to_class[]" class="up_to_class form-control" value="'.$edu_bac['up_to_class'].'">
														</div>
													</div>
													<div class="col-lg-12 col-md-12">
														<div class="form-group " style="">
															<label class="" for="reason_for_leaving">Reason for Leaving</label>
															<input type="text" name="reason_for_leaving[]"  class="reason_for_leaving form-control" value="'.$edu_bac['reason_for_leaving'].'">
														</div>
													</div>
												</div>';
									}
								}
								echo $html;	
						   ?>
					</div>
				</div> <!-- col-sm-3 -->	
			</form>

            
        </div><!-- row -->
    </div><!-- Body -->
</div><!-- /.box -->

<script type="text/javascript">
$( ".select2" ).select2();
$('#dob').datepicker({ startView: 2 });

$('#username').keyup(function() {
    $(this).val($(this).val().replace(/\s/g, ''));
});

$('#campusID').change(function(event) {
    var campusID = $(this).val();
    window.location.href = "<?=base_url('student/edit/'.$studentID.'/'.$urlID.'/')?>"+campusID;
});

$('#classesID').change(function(event) {
    var classesID = $(this).val();
    if(classesID === '0') {
        $('#classesID').val(0);
    } else {
        $.ajax({
            type: 'POST',
            url: "<?=base_url('student/sectioncall')?>",
            data: "id=" + classesID,
            dataType: "html",
            success: function(data) {
               $('#sectionID').html(data);
            }
        });

        $.ajax({
            type: 'POST',
            url: "<?=base_url('student/optionalsubjectcall')?>",
            data: "id=" + classesID,
            dataType: "html",
            success: function(data2) {
                $('#optionalSubjectID').html(data2);
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
            $('.content').css('padding-bottom', '130px');
        }        
        reader.readAsDataURL(file);
    });  
});

$(document).ready(function() {
  $("#add_degree").on('click',function(){
	  var html = `<div class="degree_block col-lg-12 col-md-12" style="border: 1px #002166 dashed;margin-top: 5px;">
						<div class="col-lg-12 col-md-12">
							<div class="form-group " style="">
								<label class="" for="name_of_school">Name of School</label>
								<button type="button" name="delete_degree" title="Delete"  class="delete_degree pull-right btn btn-xs btn-danger" ><i class="fa fa-trash fa-white"></i></button>
								<input type="text" name="name_of_school[]" class="name_of_school form-control" value="">
							</div>
						</div>
						<div class="col-lg-6 col-md-6">
							<div class="form-group " style="">
								<label class="" for="start_year">From</label>
								<input type="text" name="start_year[]" class="start_year form-control" value="">
							</div>
						</div>
						<div class="col-lg-6 col-md-6">
							<div class="form-group " style="">
								<label class="" for="status">To</label>
								<input type="text" name="end_year[]"  class="end_year form-control" value="">
							</div>
						</div>
						<div class="col-lg-12 col-md-12">
							<div class="form-group " style="margin-top: 5px;">
								<label class="" for="up_to_class">Up to class</label>
								<input type="text" name="up_to_class[]" class="up_to_class form-control" value="">
							</div>
						</div>
						<div class="col-lg-12 col-md-12">
							<div class="form-group " style="">
								<label class="" for="reason_for_leaving">Reason for Leaving</label>
								<input type="text" name="reason_for_leaving[]"  class="reason_for_leaving form-control" value="">
							</div>
						</div>
					</div> `;
      $('#education_feilds_div').append(html);
  });  
  $(document).on('click','.delete_degree',function(){
      $(this).parents(".degree_block").remove();
  });
});  


</script>
