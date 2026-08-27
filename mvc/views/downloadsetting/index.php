
<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa icon-syllabus"></i> <?=$this->lang->line('panel_title')?></h3>

        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i><?=$this->lang->line('menu_dashboard')?></a></li>
            <li class="active"><?=$this->lang->line('menu_lecture')?></li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">
            <div class="col-sm-12">
                

                <div id="hide-table">
                    <table id="example1" class="table table-striped table-bordered table-hover dataTable no-footer">
                        <thead>
                            <tr>
                                <th class="col-lg-1"><?=$this->lang->line('slno')?></th>
                                <th><?=$this->lang->line('client_title')?></th>
                                <th><?=$this->lang->line('client_date')?></th>
                                <th><?=$this->lang->line('client_file')?></th>
                                <th class="col-lg-2"><?=$this->lang->line('action')?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($bankdetails)) {$i = 1; foreach($bankdetails as $bankdetail) { ?>
                                <tr>
                                    <td data-title="<?=$this->lang->line('slno')?>">
                                        <?php echo $i; ?>
                                    </td>
                                    <td data-title="<?=$this->lang->line('client_title')?>">
                                        <?php echo $bankdetail->name; ?>
                                    </td>
                                   <td data-title="<?=$this->lang->line('client_date')?>">
                                        <?php echo $bankdetail->date; ?>
                                    </td>
                                   
                                    <td data-title="<?=$this->lang->line('client_file')?>">
                                        <?php echo namesorting($bankdetail->photo, 20); ?>
                                    </td>
                                    <td data-title="<?=$this->lang->line('action')?>">
                                       <!--  <?php echo btn_download('bankdetail/download/'.$bankdetail->bankdetailID, $this->lang->line('download')) ?> -->
                                          <?php if(permissionChecker('bankdetail_edit') || permissionChecker('bankdetail_delete')) { ?>
                                            <?php echo btn_edit('bankdetail/edit/'.$bankdetail->bankdetailID.'/'.$set, $this->lang->line('edit')) ?>
                                            <?php echo btn_delete('bankdetail/delete/'.$bankdetail->lectureID.'/'.$set, $this->lang->line('delete')) ?>
                                        <?php } ?>
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
    $(".select2").select2();
    $('#classesID').change(function() {
        var classesID = $(this).val();
        if(classesID == 0) {
            $('#hide-table').hide();
            $('.nav-tabs-custom').hide();
        } else {
            $.ajax({
                type: 'POST',
                url: "<?=base_url('lecture/lecture_list')?>",
                data: "id=" + classesID,
                dataType: "html",
                success: function(data) {
                    // alert(data);
                    window.location.href = data;
                }
            });
        }
    });
</script>
