<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa icon-campus"></i> <?=$this->lang->line('panel_title')?></h3>
        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
            <li class="active"><?=$this->lang->line('menu_campus')?></li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">
            <div class="col-sm-12">
                <?php //view(permissionChecker('campus_add'),1); ?>
                <?php if(permissionChecker('campus_add')) { ?>
                    <h5 class="page-header">
                        <a href="<?php echo base_url('campus/add') ?>">
                            <i class="fa fa-plus"></i> 
                            <?=$this->lang->line('add_campus')?>
                        </a>
                    </h5>
                <?php } ?>

                <div id="hide-table">
                    <table id="example1" class="table table-striped table-bordered table-hover dataTable no-footer">                    
                        <thead>
                            <tr>
                                <th class="col-sm-2"><?=$this->lang->line('slno')?></th>
                                <th class="col-sm-2"><?=$this->lang->line('campus_name')?></th> 
                                <?php if(permissionChecker('campus_edit') || permissionChecker('campus_delete')) { ?>
                                    <th class="col-sm-2"><?=$this->lang->line('action')?></th>
                                <?php } ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($campuse)) { $i = 1; foreach($campuse as $campus) { ?>
                                <tr>
                                    <td data-title="<?=$this->lang->line('slno')?>">
                                        <?php echo $i; ?>
                                    </td>
                                    <td data-title="<?=$this->lang->line('campus_name')?>">
                                        <?php echo $campus->name; ?>
                                    </td> 

                                    <?php if(permissionChecker('campus_edit') || permissionChecker('campus_delete')) { ?>
                                        <td data-title="<?=$this->lang->line('action')?>">
                                            <?php echo btn_edit('campus/edit/'.$campus->campusID, $this->lang->line('edit')) ?>
                                            <?php echo btn_delete('campus/delete/'.$campus->campusID, $this->lang->line('delete')) ?>
                                        </td>
                                    <?php } ?>
                                </tr>
                            <?php $i++; }} ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>