
<?php if(!empty($maininvoice)) { ?>
    <div class="well">
        <div class="row">

            <div class="col-sm-6">
                <button class="btn-cs btn-sm-cs" onclick="javascript:printDiv('printablediv')"><span class="fa fa-print"></span> <?=$this->lang->line('print')?> </button>
                <?php
                 echo btn_add_pdf('invoice/print_preview/'.$maininvoice->maininvoiceID, $this->lang->line('pdf_preview')) 
                ?>
                <?php if(($siteinfos->school_year == $this->session->userdata('defaultschoolyearID')) || ($this->session->userdata('usertypeID') == 1) || ($this->session->userdata('usertypeID') == 5)) { ?>
                    <?php
                        if(permissionChecker('invoice_edit')) {
                            if($maininvoice->maininvoicestatus != 1 && $maininvoice->maininvoicestatus != 2) {
                                echo btn_sm_edit('invoice/edit/'.$maininvoice->maininvoiceID, $this->lang->line('edit'));
                            }
                        }
                    ?>
                <?php } ?>
                <?php
                    if($maininvoice->maininvoicestatus != 2) {
                        echo btn_payment('invoice/payment/'.$maininvoice->maininvoiceID, $this->lang->line('payment')); 
                    }
                ?>
                <button class="btn-cs btn-sm-cs" data-toggle="modal" data-target="#mail"><span class="fa fa-envelope-o"></span> <?=$this->lang->line('mail')?></button>                
            </div>

            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
                    <li><a href="<?=base_url("invoice/index/$campusID")?>"><?=$this->lang->line('menu_invoice')?></a></li>
                    <li class="active"><?=$this->lang->line('view')?></li>
                </ol>
            </div>
        </div>
    </div>

    <div id="printablediv">
		<style>
			.table.table-bordered.product-style > thead > tr > th, .table.table-bordered.product-style > tbody > tr > td {
				padding: 4px 2px !important;
				line-height: 1;
				font-size:10px;
			}
		.invoice img	
			{
			    max-height: 70px;
			} 
			
			
			.invoice{
			
				padding-top:5px!important;
			}
		</style>
		<div class="col-xs-6">
			<section class="content invoice " >
				<div class="row">
					<div class="col-xs-12">
						<h2 class="page-header text-center" style="margin-bottom: 0px;padding: 5px;">
							<span  >School Copy</span></br>
							<?php
								if($siteinfos->photo) {
									$array = array(
										"src" => base_url('uploads/images/'.$siteinfos->photo),
									);
									echo img($array);
								} 
							?>
						</h2>
						<p class="text-center">We build your child creative and competent</p>
						<p class="text-center"><?php  echo $siteinfos->address; ?></p>
						<p class="text-center"><?php  echo 'Phone No: 02136968700  Cell No: 03002125564'; ?></p>
					</div><!-- /.col -->
				</div>
				<div class="row invoice-info">
					<div class="col-sm-12" style="font-size: 13px;">
						<p>Issue Date:<?=date('d M Y', strtotime($maininvoice->maininvoicecreate_date))?> 
						<span class="pull-right">Due Date:<?=date('d M Y', strtotime($maininvoice->maininvoicecreate_date.'+10 days')) ?></span></p>
					</div><!-- /.col -->
					<div class="col-sm-12 " style="font-size: 13px;">
						<p>Student's Name: 
						<span class="pull-right"><?php echo $maininvoice->srname; ?></span></p>
					</div><!-- /.col -->
					<div class="col-sm-12" style="font-size: 13px;">
						<p>Father's Name: 
						<span class="pull-right"><?php echo @$parent->father_name; ?></span></p>
					</div><!-- /.col -->
					<div class="col-sm-12 " style="font-size: 13px;">
						<p><?=$this->lang->line("student_registerNO"); ?>  
						<span class="pull-right"><?php echo $maininvoice->srregisterNO?></span></p>
					</div><!-- /.col -->
					<div class="col-sm-12" style="font-size: 13px;">
						<p><?=$this->lang->line("invoice_classesID"). " : ". $maininvoice->srclasses ?>
						<span class="pull-right"><?php echo "Section: ".$maininvoice->srsection; ?></span></p>
					</div><!-- /.col -->
					<div class="col-sm-12" style="font-size: 13px;">
						<p>Fee for:
						<span class="pull-right"><?=date('M', strtotime($maininvoice->maininvoicecreate_date))?></span></p>

						<!--<span class="pull-right"><?=date('M', strtotime('+1 month',strtotime($maininvoice->maininvoicecreate_date)))?></span></p>-->
					</div><!-- /.col -->
					<!--<div class="col-sm-4 invoice-col" style="font-size: 13px;">
						<?=$this->lang->line("invoice_to")?>
						<address >
							<strong><?=$maininvoice->srname?></strong><br>
							<?=$this->lang->line("invoice_roll"). " : ". $maininvoice->srroll?><br>
							<?=$this->lang->line("invoice_classesID"). " : ". $maininvoice->srclasses?><br>
							<?=$this->lang->line("student_registerNO"). " : ". $maininvoice->srregisterNO?><br>
							<?php if(!empty($student)) { echo $this->lang->line("invoice_email"). " : ". $student->email; } ?><br>
						</address>
					</div>--><!-- /.col -->
					<!--<div class="col-sm-4 invoice-col" style="font-size: 13px;">
						<b><?=$this->lang->line("invoice_invoice").$maininvoice->maininvoiceID?></b><br>
						<?php 
							$status = $maininvoice->maininvoicestatus;
							$setButton = '';
							if($status == 0) {
								$status = $this->lang->line('invoice_notpaid');
								$setButton = 'text-red';
							} elseif($status == 1) {
								$status = $this->lang->line('invoice_partially_paid');
								$setButton = 'text-yellow';
							} elseif($status == 2) {
								$status = $this->lang->line('invoice_fully_paid');
								$setButton = 'text-green';
							}

							echo $this->lang->line('invoice_status'). " : ". "<span class='".$setButton."'>".$status."</span>";
						?>

					</div>-->
				</div>
				<br />

				<div class="row">
					<div class="col-xs-12">
						<div class="table-responsive">
							<table class="table table-bordered product-style" >
								<thead>
									<tr>
										<th class="col-lg-2" ><?=$this->lang->line('slno')?></th>
										<th class="col-lg-4"><?=$this->lang->line('invoice_feetype')?></th>
										<th class="col-lg-2"><?=$this->lang->line('invoice_amount')?></th>
										<th class="col-lg-2"><?=$this->lang->line('invoice_discount')?></th>
										<th class="col-lg-2"><?=$this->lang->line('invoice_subtotal')?></th>
									</tr>
								</thead>
								<tbody>
									<?php $subtotal = 0; $totalsubtotal = 0; $i = 1; if(!empty($invoices)) { foreach($invoices as $invoice) { $discount = 0; if($invoice->discount > 0) { $discount = (($invoice->amount/100) * $invoice->discount); } $subtotal = ($invoice->amount - $discount); $totalsubtotal += $subtotal; ?>
										<tr>
											<td data-title="<?=$this->lang->line('slno')?>">
												<?php echo $i; ?>
											</td>
											
											<td data-title="<?=$this->lang->line('invoice_feetype')?>">
												<?=isset($feetypes[$invoice->feetypeID]) ? $feetypes[$invoice->feetypeID] : ''?>
											</td>
											
											<td data-title="<?=$this->lang->line('invoice_amount')?>">
												<?=number_format($invoice->amount, 2)?>
											</td>

											<td data-title="<?=$this->lang->line('invoice_discount')?>">
												<?=number_format($discount, 2)?>
											</td>

											<td data-title="<?=$this->lang->line('invoice_subtotal')?>">
												<?=number_format($subtotal, 2)?>
											</td>
										</tr>
									<?php $i++; } } ?>
								</tbody>
								<tfoot>
									<tr>
										<td colspan="4"><span class="pull-right"><b><?=$this->lang->line('invoice_totalamount')?> <?=!empty($siteinfos->currency_code) ? '('.$siteinfos->currency_code.')' : ''?></b></span></td>
										<td><b><?=number_format($totalsubtotal, 2)?></b></td>
									</tr>
									<tr>
										<td colspan="4"><span class="pull-right"><b><?=$this->lang->line('invoice_paid')?> <?=!empty($siteinfos->currency_code) ? '('.$siteinfos->currency_code.')' : ''?></b></span></td>
										<td><b><?=number_format($grandtotalandpayment['totalpayment'], 2)?></b></td>
									</tr> 
									<tr>
										<td colspan="4"><span class="pull-right"><b><?=$this->lang->line('invoice_weaver')?> <?=!empty($siteinfos->currency_code) ? '('.$siteinfos->currency_code.')' : ''?></b></span></td>
										<td><b><?=number_format($grandtotalandpayment['totalweaver'], 2)?></b></td>
									</tr>
									<tr>
										<td colspan="4"><span class="pull-right"><b><?=$this->lang->line('invoice_balance');?> <?=!empty($siteinfos->currency_code) ? '('.$siteinfos->currency_code.')' : ''?></b></span></td>
										<td><b><?=number_format(($totalsubtotal - ($grandtotalandpayment['totalpayment'] + $grandtotalandpayment['totalweaver'])), 2)?></b></td>
									</tr>
									<tr>
										<td colspan="4"><span class="pull-right"><b><?=$this->lang->line('invoice_fine');?> <?=!empty($siteinfos->currency_code) ? '('.$siteinfos->currency_code.')' : ''?></b></span></td>
										<td><b><?=number_format($grandtotalandpayment['totalfine'], 2)?></b></td>
									</tr>
								</tfoot>
							</table>
						</div>
					</div>

					<div class="col-xs-12">
						<div class="well well-sm" style="background-color: black;color:white;-webkit-print-color-adjust: exact;">
							<ul class="list-unstyled">
								<li>1. A fine of Rs. 200 will be charged after due date.</li>
								<li>2. Payment mode is cash only </li>
								<li>3. Payment shall be paid on due date. After due date, penalty will be imposed as per policy. </li>
							</ul>
						</div>
					</div>

					<br/>

					<div class="col-xs-6">
						<div style="width:70%;border:2px solid">
							<br/>
							<br/>
							<br/>
							<br/>
						</div>
					</div>
					<div class="col-xs-6">
						<div style="border-bottom:1px solid">
							<br/>
							<br/>
							<br/>
						</div>
						Head of Institution
					</div>

					
					<div class="col-xs-12">
						<br/>
						<br/>
						<div class="well well-sm" style="background-color: white;color:black;-webkit-print-color-adjust: exact;">
							<p>
							| Azam System | Azam Eims |			
								Royal Excellent Facility Management			
								|  |  |					
							</p>
						</div>
					</div>

					<!--<div class="col-sm-3 col-xs-6 pull-right">
						<div class="well well-sm">
							<p>
								<?=$this->lang->line('invoice_create_by')?> : <?=$createuser?>
								<br>
								<?=$this->lang->line('invoice_date')?> : <?=date('d M Y', strtotime($maininvoice->maininvoicecreate_date))?>
							</p>
						</div>
					</div>-->
				</div>


				<!-- this row will not appear when printing -->
			</section><!-- /.content -->
		</div>
    
		<div class="col-xs-6">
			<section class="content invoice " >
				<div class="row">
					<div class="col-xs-12">
						<h2 class="page-header text-center" style="margin-bottom: 0px;padding: 5px;">
							<span  >Student Copy</span></br>
							<?php
								if($siteinfos->photo) {
									$array = array(
										"src" => base_url('uploads/images/'.$siteinfos->photo),
									);
									echo img($array);
								} 
							?>
						</h2>
						<p class="text-center">We build your child creative and competent</p>
						<p class="text-center"><?php  echo $siteinfos->address; ?></p>
						<p class="text-center"><?php  echo 'Phone No: 02136968700  Cell No: 03002125564'; ?></p>
					</div><!-- /.col -->
				</div>
				<div class="row invoice-info">
					<div class="col-sm-12 " style="font-size: 13px;">
						<p>Issue Date:<?=date('d M Y', strtotime($maininvoice->maininvoicecreate_date))?> 
						<span class="pull-right">Due Date:<?=date('5 M Y', strtotime('+10 days',strtotime($maininvoice->maininvoicecreate_date)))?></span></p>
					</div><!-- /.col -->
					<div class="col-sm-12" style="font-size: 13px;">
						<p>Student's Name: 
						<span class="pull-right"><?php echo $maininvoice->srname; ?></span></p>
					</div><!-- /.col -->
					<div class="col-sm-12 " style="font-size: 13px;">
						<p>Father's Name: 
						<span class="pull-right"><?php echo @$parent->father_name; ?></span></p>
					</div><!-- /.col -->
					<div class="col-sm-12 " style="font-size: 13px;">
						<p><?=$this->lang->line("student_registerNO"); ?>  
						<span class="pull-right"><?php echo $maininvoice->srregisterNO?></span></p>
					</div><!-- /.col -->
					<div class="col-sm-12 " style="font-size: 13px;">
						<p><?=$this->lang->line("invoice_classesID"). " : ". $maininvoice->srclasses ?>
						<span class="pull-right"><?php echo "Section: ".$maininvoice->srsection; ?></span></p>
					</div><!-- /.col -->
					<div class="col-sm-12 " style="font-size: 13px;">
						<p>Fee for:
						<span class="pull-right"><?=date('M', strtotime('+1 month',strtotime($maininvoice->maininvoicecreate_date)))?></span></p>
					</div><!-- /.col -->
					<!--<div class="col-sm-4 invoice-col" style="font-size: 13px;">
						<?=$this->lang->line("invoice_to")?>
						<address >
							<strong><?=$maininvoice->srname?></strong><br>
							<?=$this->lang->line("invoice_roll"). " : ". $maininvoice->srroll?><br>
							<?=$this->lang->line("invoice_classesID"). " : ". $maininvoice->srclasses?><br>
							<?=$this->lang->line("student_registerNO"). " : ". $maininvoice->srregisterNO?><br>
							<?php if(!empty($student)) { echo $this->lang->line("invoice_email"). " : ". $student->email; } ?><br>
						</address>
					</div>--><!-- /.col -->
					<!--<div class="col-sm-4 invoice-col" style="font-size: 13px;">
						<b><?=$this->lang->line("invoice_invoice").$maininvoice->maininvoiceID?></b><br>
						<?php 
							$status = $maininvoice->maininvoicestatus;
							$setButton = '';
							if($status == 0) {
								$status = $this->lang->line('invoice_notpaid');
								$setButton = 'text-red';
							} elseif($status == 1) {
								$status = $this->lang->line('invoice_partially_paid');
								$setButton = 'text-yellow';
							} elseif($status == 2) {
								$status = $this->lang->line('invoice_fully_paid');
								$setButton = 'text-green';
							}

							echo $this->lang->line('invoice_status'). " : ". "<span class='".$setButton."'>".$status."</span>";
						?>

					</div>-->
				</div>
				<br />

				<div class="row">
					<div class="col-xs-12">
						<div class="table-responsive">
							<table class="table table-bordered product-style">
								<thead>
									<tr>
										<th class="col-lg-2" ><?=$this->lang->line('slno')?></th>
										<th class="col-lg-4"><?=$this->lang->line('invoice_feetype')?></th>
										<th class="col-lg-2"><?=$this->lang->line('invoice_amount')?></th>
										<th class="col-lg-2"><?=$this->lang->line('invoice_discount')?></th>
										<th class="col-lg-2"><?=$this->lang->line('invoice_subtotal')?></th>
									</tr>
								</thead>
								<tbody>
									<?php $subtotal = 0; $totalsubtotal = 0; $i = 1; if(!empty($invoices)) { foreach($invoices as $invoice) { $discount = 0; if($invoice->discount > 0) { $discount = (($invoice->amount/100) * $invoice->discount); } $subtotal = ($invoice->amount - $discount); $totalsubtotal += $subtotal; ?>
										<tr>
											<td data-title="<?=$this->lang->line('slno')?>">
												<?php echo $i; ?>
											</td>
											
											<td data-title="<?=$this->lang->line('invoice_feetype')?>">
												<?=isset($feetypes[$invoice->feetypeID]) ? $feetypes[$invoice->feetypeID] : ''?>
											</td>
											
											<td data-title="<?=$this->lang->line('invoice_amount')?>">
												<?=number_format($invoice->amount, 2)?>
											</td>

											<td data-title="<?=$this->lang->line('invoice_discount')?>">
												<?=number_format($discount, 2)?>
											</td>

											<td data-title="<?=$this->lang->line('invoice_subtotal')?>">
												<?=number_format($subtotal, 2)?>
											</td>
										</tr>
									<?php $i++; } } ?>
								</tbody>
								<tfoot>
									<tr>
										<td colspan="4"><span class="pull-right"><b><?=$this->lang->line('invoice_totalamount')?> <?=!empty($siteinfos->currency_code) ? '('.$siteinfos->currency_code.')' : ''?></b></span></td>
										<td><b><?=number_format($totalsubtotal, 2)?></b></td>
									</tr>
									<tr>
										<td colspan="4"><span class="pull-right"><b><?=$this->lang->line('invoice_paid')?> <?=!empty($siteinfos->currency_code) ? '('.$siteinfos->currency_code.')' : ''?></b></span></td>
										<td><b><?=number_format($grandtotalandpayment['totalpayment'], 2)?></b></td>
									</tr> 
									<tr>
										<td colspan="4"><span class="pull-right"><b><?=$this->lang->line('invoice_weaver')?> <?=!empty($siteinfos->currency_code) ? '('.$siteinfos->currency_code.')' : ''?></b></span></td>
										<td><b><?=number_format($grandtotalandpayment['totalweaver'], 2)?></b></td>
									</tr>
									<tr>
										<td colspan="4"><span class="pull-right"><b><?=$this->lang->line('invoice_balance');?> <?=!empty($siteinfos->currency_code) ? '('.$siteinfos->currency_code.')' : ''?></b></span></td>
										<td><b><?=number_format(($totalsubtotal - ($grandtotalandpayment['totalpayment'] + $grandtotalandpayment['totalweaver'])), 2)?></b></td>
									</tr>
									<tr>
										<td colspan="4"><span class="pull-right"><b><?=$this->lang->line('invoice_fine');?> <?=!empty($siteinfos->currency_code) ? '('.$siteinfos->currency_code.')' : ''?></b></span></td>
										<td><b><?=number_format($grandtotalandpayment['totalfine'], 2)?></b></td>
									</tr>
								</tfoot>
							</table>
						</div>
					</div>

					<div class="col-xs-12">
						<div class="well well-sm" style="background-color: black;color:white;-webkit-print-color-adjust: exact;">
							<ul class="list-unstyled">
								<li>1. A fine of Rs. 200 will be charged after due date.</li>
								<li>2. Payment mode is cash only </li>
								<li>3. Payment shall be paid on due date. After due date, penalty will be imposed as per policy. </li>
							</ul>
						</div>
					</div>

					<br/>

					<div class="col-xs-6">
						<div style="width:70%;border:2px solid">
							<br/>
							<br/>
							<br/>
							<br/>
						</div>
					</div>
					<div class="col-xs-6">
						<div style="border-bottom:1px solid">
							<br/>
							<br/>
							<br/>
						</div>
						Head of Institution
					</div>

					
					<div class="col-xs-12">
						<br/>
						<br/>
						<div class="well well-sm" style="background-color: white;color:black;-webkit-print-color-adjust: exact;">
							<p>
								<!--| iREX | School Management System |			-->
								<!--Royal Excellent Facility Management			-->
								<!--| info@royalexcellentfm.pk | 0300 - 2546363
								
								|			-->
								
								| Azam System | Azam Eims |			
								Royal Excellent Facility Management			
								|  |  |		
								
								
							</p>
						</div>
					</div>

					<!--<div class="col-sm-3 col-xs-6 pull-right">
						<div class="well well-sm">
							<p>
								<?=$this->lang->line('invoice_create_by')?> : <?=$createuser?>
								<br>
								<?=$this->lang->line('invoice_date')?> : <?=date('d M Y', strtotime($maininvoice->maininvoicecreate_date))?>
							</p>
						</div>
					</div>-->
				</div>


				<!-- this row will not appear when printing -->
			</section><!-- /.content -->
		</div>

					<div class="col-xs-6">
			<section class="content invoice " >
				<div class="row">
					<div class="col-xs-12">
						<h2 class="page-header text-center" style="margin-bottom: 0px;padding: 5px;">
							<span  >Bank Copy</span></br>
							<?php
								if($siteinfos->photo) {
									$array = array(
										"src" => base_url('uploads/images/'.$siteinfos->photo),
									);
									echo img($array);
								} 
							?>
						</h2>
						<p class="text-center">We build your child creative and competent</p>
						<p class="text-center"><?php  echo $siteinfos->address; ?></p>
						<p class="text-center"><?php  echo 'Phone No: 02136968700  Cell No: 03002125564'; ?></p>
					</div><!-- /.col -->
				</div>
				<div class="row invoice-info">
					<div class="col-sm-12" style="font-size: 13px;">
						<p>Issue Date:<?=date('d M Y', strtotime($maininvoice->maininvoicecreate_date))?> 
						<span class="pull-right">Due Date:<?=date('5 M Y', strtotime('+10 days',strtotime($maininvoice->maininvoicecreate_date)))?></span></p>
					</div><!-- /.col -->
					<div class="col-sm-12 " style="font-size: 13px;">
						<p>Student's Name: 
						<span class="pull-right"><?php echo $maininvoice->srname; ?></span></p>
					</div><!-- /.col -->
					<div class="col-sm-12" style="font-size: 13px;">
						<p>Father's Name: 
						<span class="pull-right"><?php echo @$parent->father_name; ?></span></p>
					</div><!-- /.col -->
					<div class="col-sm-12 " style="font-size: 13px;">
						<p><?=$this->lang->line("student_registerNO"); ?>  
						<span class="pull-right"><?php echo $maininvoice->srregisterNO?></span></p>
					</div><!-- /.col -->
					<div class="col-sm-12" style="font-size: 13px;">
						<p><?=$this->lang->line("invoice_classesID"). " : ". $maininvoice->srclasses ?>
						<span class="pull-right"><?php echo "Section: ".$maininvoice->srsection; ?></span></p>
					</div><!-- /.col -->
					<div class="col-sm-12" style="font-size: 13px;">
						<p>Fee for:
						<span class="pull-right"><?=date('M', strtotime('+1 month',strtotime($maininvoice->maininvoicecreate_date)))?></span></p>
					</div><!-- /.col -->
					<!--<div class="col-sm-4 invoice-col" style="font-size: 13px;">
						<?=$this->lang->line("invoice_to")?>
						<address >
							<strong><?=$maininvoice->srname?></strong><br>
							<?=$this->lang->line("invoice_roll"). " : ". $maininvoice->srroll?><br>
							<?=$this->lang->line("invoice_classesID"). " : ". $maininvoice->srclasses?><br>
							<?=$this->lang->line("student_registerNO"). " : ". $maininvoice->srregisterNO?><br>
							<?php if(!empty($student)) { echo $this->lang->line("invoice_email"). " : ". $student->email; } ?><br>
						</address>
					</div>--><!-- /.col -->
					<!--<div class="col-sm-4 invoice-col" style="font-size: 13px;">
						<b><?=$this->lang->line("invoice_invoice").$maininvoice->maininvoiceID?></b><br>
						<?php 
							$status = $maininvoice->maininvoicestatus;
							$setButton = '';
							if($status == 0) {
								$status = $this->lang->line('invoice_notpaid');
								$setButton = 'text-red';
							} elseif($status == 1) {
								$status = $this->lang->line('invoice_partially_paid');
								$setButton = 'text-yellow';
							} elseif($status == 2) {
								$status = $this->lang->line('invoice_fully_paid');
								$setButton = 'text-green';
							}

							echo $this->lang->line('invoice_status'). " : ". "<span class='".$setButton."'>".$status."</span>";
						?>

					</div>-->
				</div>
				<br />

				<div class="row">
					<div class="col-xs-12">
						<div class="table-responsive">
							<table class="table table-bordered product-style" >
								<thead>
									<tr>
										<th class="col-lg-2" ><?=$this->lang->line('slno')?></th>
										<th class="col-lg-4"><?=$this->lang->line('invoice_feetype')?></th>
										<th class="col-lg-2"><?=$this->lang->line('invoice_amount')?></th>
										<th class="col-lg-2"><?=$this->lang->line('invoice_discount')?></th>
										<th class="col-lg-2"><?=$this->lang->line('invoice_subtotal')?></th>
									</tr>
								</thead>
								<tbody>
									<?php $subtotal = 0; $totalsubtotal = 0; $i = 1; if(!empty($invoices)) { foreach($invoices as $invoice) { $discount = 0; if($invoice->discount > 0) { $discount = (($invoice->amount/100) * $invoice->discount); } $subtotal = ($invoice->amount - $discount); $totalsubtotal += $subtotal; ?>
										<tr>
											<td data-title="<?=$this->lang->line('slno')?>">
												<?php echo $i; ?>
											</td>
											
											<td data-title="<?=$this->lang->line('invoice_feetype')?>">
												<?=isset($feetypes[$invoice->feetypeID]) ? $feetypes[$invoice->feetypeID] : ''?>
											</td>
											
											<td data-title="<?=$this->lang->line('invoice_amount')?>">
												<?=number_format($invoice->amount, 2)?>
											</td>

											<td data-title="<?=$this->lang->line('invoice_discount')?>">
												<?=number_format($discount, 2)?>
											</td>

											<td data-title="<?=$this->lang->line('invoice_subtotal')?>">
												<?=number_format($subtotal, 2)?>
											</td>
										</tr>
									<?php $i++; } } ?>
								</tbody>
								<tfoot>
									<tr>
										<td colspan="4"><span class="pull-right"><b><?=$this->lang->line('invoice_totalamount')?> <?=!empty($siteinfos->currency_code) ? '('.$siteinfos->currency_code.')' : ''?></b></span></td>
										<td><b><?=number_format($totalsubtotal, 2)?></b></td>
									</tr>
									<tr>
										<td colspan="4"><span class="pull-right"><b><?=$this->lang->line('invoice_paid')?> <?=!empty($siteinfos->currency_code) ? '('.$siteinfos->currency_code.')' : ''?></b></span></td>
										<td><b><?=number_format($grandtotalandpayment['totalpayment'], 2)?></b></td>
									</tr> 
									<tr>
										<td colspan="4"><span class="pull-right"><b><?=$this->lang->line('invoice_weaver')?> <?=!empty($siteinfos->currency_code) ? '('.$siteinfos->currency_code.')' : ''?></b></span></td>
										<td><b><?=number_format($grandtotalandpayment['totalweaver'], 2)?></b></td>
									</tr>
									<tr>
										<td colspan="4"><span class="pull-right"><b><?=$this->lang->line('invoice_balance');?> <?=!empty($siteinfos->currency_code) ? '('.$siteinfos->currency_code.')' : ''?></b></span></td>
										<td><b><?=number_format(($totalsubtotal - ($grandtotalandpayment['totalpayment'] + $grandtotalandpayment['totalweaver'])), 2)?></b></td>
									</tr>
									<tr>
										<td colspan="4"><span class="pull-right"><b><?=$this->lang->line('invoice_fine');?> <?=!empty($siteinfos->currency_code) ? '('.$siteinfos->currency_code.')' : ''?></b></span></td>
										<td><b><?=number_format($grandtotalandpayment['totalfine'], 2)?></b></td>
									</tr>
								</tfoot>
							</table>
						</div>
					</div>

					<div class="col-xs-12">
						<div class="well well-sm" style="background-color: black;color:white;-webkit-print-color-adjust: exact;">
							<ul class="list-unstyled">
								<li>1. A fine of Rs. 200 will be charged after due date.</li>
								<li>2. Payment mode is cash only </li>
								<li>3. Payment shall be paid on due date. After due date, penalty will be imposed as per policy. </li>
							</ul>
						</div>
					</div>

					<br/>

					<div class="col-xs-6">
						<div style="width:70%;border:2px solid">
							<br/>
							<br/>
							<br/>
							<br/>
						</div>
					</div>
					<div class="col-xs-6">
						<div style="border-bottom:1px solid">
							<br/>
							<br/>
							<br/>
						</div>
						Head of Institution
					</div>

					
					<div class="col-xs-12">
						<br/>
						<br/>
						<div class="well well-sm" style="background-color: white;color:black;-webkit-print-color-adjust: exact;">
							<p>
							| Azam System | Azam Eims |			
								Royal Excellent Facility Management			
								|  |  |					
							</p>
						</div>
					</div>

					<!--<div class="col-sm-3 col-xs-6 pull-right">
						<div class="well well-sm">
							<p>
								<?=$this->lang->line('invoice_create_by')?> : <?=$createuser?>
								<br>
								<?=$this->lang->line('invoice_date')?> : <?=date('d M Y', strtotime($maininvoice->maininvoicecreate_date))?>
							</p>
						</div>
					</div>-->
				</div>


				<!-- this row will not appear when printing -->
			</section><!-- /.content -->
		</div>












    
	</div>
    <!-- email modal starts here -->
    <form class="form-horizontal" role="form" action="<?=base_url('teacher/send_mail');?>" method="post">
        <div class="modal fade" id="mail">
          <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title"><?=$this->lang->line('mail')?></h4>
                </div>
                <div class="modal-body">
                
                    <?php 
                        if(form_error('to')) 
                            echo "<div class='form-group has-error' >";
                        else     
                            echo "<div class='form-group' >";
                    ?>
                        <label for="to" class="col-sm-2 control-label">
                            <?=$this->lang->line("to")?> <span class="text-red">*</span>
                        </label>
                        <div class="col-sm-6">
                            <input type="email" class="form-control" id="to" name="to" value="<?=set_value('to')?>" >
                        </div>
                        <span class="col-sm-4 control-label" id="to_error">
                        </span>
                    </div>

                    <?php 
                        if(form_error('subject')) 
                            echo "<div class='form-group has-error' >";
                        else     
                            echo "<div class='form-group' >";
                    ?>
                        <label for="subject" class="col-sm-2 control-label">
                            <?=$this->lang->line("subject")?> <span class="text-red">*</span>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="subject" name="subject" value="<?=set_value('subject')?>" >
                        </div>
                        <span class="col-sm-4 control-label" id="subject_error">
                        </span>

                    </div>

                    <?php 
                        if(form_error('message')) 
                            echo "<div class='form-group has-error' >";
                        else     
                            echo "<div class='form-group' >";
                    ?>
                        <label for="message" class="col-sm-2 control-label">
                            <?=$this->lang->line("message")?>
                        </label>
                        <div class="col-sm-6">
                            <textarea class="form-control" id="message" name="message" style="resize: vertical;" value="<?=set_value('message')?>" ></textarea>
                        </div>
                    </div>

                
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" style="margin-bottom:0px;" data-dismiss="modal"><?=$this->lang->line('close')?></button>
                    <input type="button" id="send_pdf" class="btn btn-success" value="<?=$this->lang->line("send")?>" />
                </div>
            </div>
          </div>
        </div>
    </form>
    <!-- email end here -->
    <script language="javascript" type="text/javascript">
        function printDiv(divID) {
            //Get the HTML of div
            var divElements = document.getElementById(divID).innerHTML;
            //Get the HTML of whole page
            var oldPage = document.body.innerHTML;

            //Reset the page's HTML with div's HTML only
            document.body.innerHTML = 
              "<html><head><title></title></head><body>" + 
              divElements + "</body>";

            //Print Page
			setTimeout(function(){ 
		window.print(); 

				//Restore orignal HTML
            document.body.innerHTML = oldPage;
            window.location.reload();
			}, 1000);
            

            
        }
        function closeWindow() {
            location.reload(); 
        }

        function check_email(email) {
            var status = false;     
            var emailRegEx = /^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i;
            if (email.search(emailRegEx) == -1) {
                $("#to_error").html('');
                $("#to_error").html("<?=$this->lang->line('mail_valid')?>").css("text-align", "left").css("color", 'red');
            } else {
                status = true;
            }
            return status;
        }


        $("#send_pdf").click(function(){
            var field = {
                'to'                : $('#to').val(), 
                'subject'           : $('#subject').val(), 
                'message'           : $('#message').val(),
                'id'                : "<?=$maininvoice->maininvoiceID;?>",
            };

            var to = $('#to').val();
            var subject = $('#subject').val();
            var error = 0;

            $("#to_error").html("");
            $("#subject_error").html("");

            if(to == "" || to == null) {
                error++;
                $("#to_error").html("<?=$this->lang->line('mail_to')?>").css("text-align", "left").css("color", 'red');
            } else {
                if(check_email(to) == false) {
                    error++
                }
            }

            if(subject == "" || subject == null) {
                error++;
                $("#subject_error").html("<?=$this->lang->line('mail_subject')?>").css("text-align", "left").css("color", 'red');
            } else {
                $("#subject_error").html("");
            }

            if(error == 0) {
                $('#send_pdf').attr('disabled','disabled');
                $.ajax({
                    type: 'POST',
                    url: "<?=base_url('invoice/send_mail')?>",
                    data: field,
                    dataType: "html",
                    success: function(data) {
                        var response = JSON.parse(data);
                        if (response.status == false) {
                            $('#send_pdf').removeAttr('disabled');
                            if(response.to) {
                                $("#to_error").html("<?=$this->lang->line('mail_to')?>").css("text-align", "left").css("color", 'red');
                            } 
                            
                            if(response.subject) {
                                $("#subject_error").html("<?=$this->lang->line('mail_subject')?>").css("text-align", "left").css("color", 'red');
                            }
                            
                            if(response.message) {
                                toastr["error"](response.message)
                                toastr.options = {
                                  "closeButton": true,
                                  "debug": false,
                                  "newestOnTop": false,
                                  "progressBar": false,
                                  "positionClass": "toast-top-right",
                                  "preventDuplicates": false,
                                  "onclick": null,
                                  "showDuration": "500",
                                  "hideDuration": "500",
                                  "timeOut": "5000",
                                  "extendedTimeOut": "1000",
                                  "showEasing": "swing",
                                  "hideEasing": "linear",
                                  "showMethod": "fadeIn",
                                  "hideMethod": "fadeOut"
                                }
                            }
                        } else {
                            location.reload();
                        }
                    }
                });
            }
        });
    </script>
<?php } ?>
