      <div class="box">
        <div class="box-header" style="background-color: #fff;">
          <h4 class="text-black" style="text-align: center;padding: 15px;">
             <?=$this->lang->line("zoom_setting")?>
            </h4>
        </div>


        <div class="box-body" style="padding: 0px;">
          <table class="table table-hover">
                        <thead>
                            <tr>
<th><?=$this->lang->line('slno')?></th>               
 <th><?php echo $this->lang->line('class') . " " . $this->lang->line('title'); ?></th>
<th><?php echo $this->lang->line('date'); ?></th>
<th><?php echo $this->lang->line('password'); ?></th>
<th><?php echo $this->lang->line('class_teacher'); ?></th>
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
                                    <td data-title="<?=$this->lang->line('password')?>">
                                        <?php echo ($conference_value->password); ?>
                                    </td>
                                    <td data-title="<?=$this->lang->line('exam_name')?>">
                                        <?php
                                                    $name = ($conference_value->create_for_surname == "") ? $conference_value->create_for_name : $conference_value->create_for_name;
                                                    echo $name;
                                                    ?>
                                                </td>
                                    <td>
                                       <?php
                                       echo btn_dash_view('student/timetable_student', $this->lang->line('view'), 'bg-maroon-light');
                                       ?> 
                                    </td>
                                     </tr>
                            <?php $i++; }} ?>
                        </tbody>
                    </table>
                

        </div>
      </div>