<style type="text/css">
  .center {
  display: block;
  margin-left: auto;
  margin-right: auto;
  width: 50%;
}
</style>      
     <!--  <div class="well box" style="background-image: url(<?php echo base_url('assets/img/bg/laptop5.png')?>);"> -->

      <?php if(!empty($user)) { ?>
        <section class="panel">
         <!--  <div class="profile-db-head" style="background-color: #12055a"> -->

            <img src="<?php echo base_url('uploads/images/'.$siteinfos->photo);?>" width="150" height="100" class="center">

         <!--  </div> -->
      
          <table class="table table-hover">
              <tbody>
                  <tr>
                   <!--  <td>
                      <i class="glyphicon glyphicon-user text-maroon-light" ></i>
                    </td> -->
                    <!-- <td><?=$this->lang->line('dashboard_username')?></td>
                    <td><?=$user->username?></td> -->
                    <td style="color: #007cac; text-align: center;"><h4><b>A Perfect ERP for Educational Institutes</b></h4></td>
                  </tr>
                  <tr>
                      <!-- <td>
                        <i class="fa fa-envelope text-maroon-light"></i>
                      </td> -->
                      <!-- <td><?=$this->lang->line('dashboard_email')?></td>
                    <td><?=$user->email?></td> -->
                    <td style="color: #71467d;text-align: center"><h4><b>School – College - University</b></h4></td>
                  </tr>
                  <!-- <tr>
                    <td>
                      <i class="fa fa-phone text-maroon-light"></i>
                    </td>
                    <td><?=$this->lang->line('dashboard_phone')?></td>
                    <td><?=$user->phone?></td>
                  </tr>
                <tr>
                    <td>
                      <i class=" fa fa-globe text-maroon-light"></i>
                    </td>
                    <td><?=$this->lang->line('dashboard_address')?></td>
                    <td><?=$user->address?></td>
                  </tr>  -->
              </tbody>
          </table>
        
        </section>
        <!-- </div> -->
       <!--  <img src="<?php echo base_url('assets/img/keypad3.png');?>"> -->
      <?php } ?>