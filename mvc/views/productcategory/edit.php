
<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa iniicon-productcategory"></i> <?=$this->lang->line('panel_title')?></h3>
        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
            <li><a href="<?=base_url("productcategory/index/$campusID")?>"><?=$this->lang->line('menu_productcategory')?></a></li>
            <li class="active"><?=$this->lang->line('menu_edit')?> <?=$this->lang->line('menu_productcategory')?></li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">
            <div class="col-sm-10">
                <form class="form-horizontal" role="form" method="post">
                    <?php getCampus($campusID); ?>
                    <div class="form-group <?=form_error('productcategoryname') ? 'has-error' : '' ?>">
                        <label for="productcategoryname" class="col-sm-2 control-label">
                            <?=$this->lang->line("productcategory_name")?> <span class="text-red">*</span>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="productcategoryname" name="productcategoryname" value="<?=set_value('productcategoryname', $productcategorys->productcategoryname)?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('productcategoryname'); ?>
                        </span>
                    </div>

                      <div class="form-group <?=form_error('productcategorytypeID') ? 'has-error' : '' ?>" >
                        <label for="productcategorytypeID" class="col-sm-2 control-label">
                            <?=$this->lang->line("productcategory_type")?> <span class="text-red">*</span>
                        </label>
                        <div class="col-sm-6">
                            <?php
                            $productcategorytypeArray[0] = $this->lang->line("productcategory_type_select");
                                foreach ($productcategorystype as $productcategory) {
                                $productcategorytypeArray[$productcategory->productcategorytypeID] = 
                                $productcategory->productcategorytype;
                                }
                                echo form_dropdown("productcategorytypeID", $productcategorytypeArray, set_value("productcategorytypeID", $productcategorys->productcategorytypeID), "id='productcategorytypeID' class='form-control select2'");
                            ?>
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('productcategoryID'); ?>
                        </span>
                    </div>

                    <div class="form-group <?=form_error('productcategorydesc') ? 'has-error' : '' ?>" >
                        <label for="productcategorydesc" class="col-sm-2 control-label">
                            <?=$this->lang->line("productcategory_desc")?>
                        </label>
                        <div class="col-sm-6">
                            <textarea class="form-control" style="resize:none;" id="productcategorydesc" name="productcategorydesc"><?=set_value('productcategorydesc', $productcategorys->productcategorydesc)?></textarea>
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('productcategorydesc'); ?>
                        </span>
                    </div>
                  


                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-8">
                            <input type="submit" class="btn btn-success" value="<?=$this->lang->line("update_productcategory")?>" >
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>