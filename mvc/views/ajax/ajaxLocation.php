<?php
    if(form_error('asset_locationID'))
        echo "<div class='form-group has-error' >";
    else
        echo "<div class='form-group' >";
?>
    <label for="asset_locationID" class="col-sm-2 control-label">
        <?=$this->lang->line("asset_locationID")?>
    </label>
    <div class="col-sm-6">
        <select name="asset_locationID"  id="asset_locationID"  class="form-control select2">
    <?php if(!empty($locations)) {foreach($locations as $location){ ?>
    <option value="<?php echo $location->locationID;?>"
  
><?php echo $location->location;?></option>
<?php } } ?>
</select>
    </div>
    <span class="col-sm-4 control-label">
        <?php echo form_error('asset_locationID'); ?>
    </span>
</div>

 
 
