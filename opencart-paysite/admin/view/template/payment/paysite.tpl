<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <?php if ($error_warning) { ?>
  <div class="warning"><?php echo $error_warning; ?></div>
  <?php } ?>
    <div class="box">
    <div class="heading">
      <h1><img src="view/image/payment.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons"><a onclick="$('#form').submit();" class="button"><?php echo $button_save; ?></a><a onclick="location = '<?php echo $cancel; ?>';" class="button"><?php echo $button_cancel; ?></a></div>
    </div>
    <div class="content">
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
        <table class="form">
          <tr>
            <td><span class="required">*</span> <?php echo $entry_mercid; ?></td>
            <td>			  
			   <input type="text" name="paysite_mercid" value="<?php echo $paysite_mercid; ?>" placeholder="<?php echo $paysite_mercid; ?>" id="input-vendor" class="form-control" />
              <?php if ($error_mercid) { ?>
              <span class="error"><?php echo $error_mercid; ?></span>
              <?php } ?>
			  </td>
          </tr>      	
			<tr>
				<td><span class="required">*</span> <?php echo $entry_secretkey; ?></td>
				<td>
				  <input type="text" name="paysite_secretkey" value="<?php echo $paysite_secretkey; ?>" placeholder="<?php echo $paysite_secretkey; ?>" id="input-vendor" class="form-control" />
				  <?php if ($error_secretkey) { ?>
				  <span class="error"><?php echo $error_secretkey; ?></span>
				  <?php } ?>
				</td>
          </tr>	
		 <tr>
		<td><span class="required">*</span> <?php echo $entry_publickey; ?></td>
		<td>
		  <input type="text" name="paysite_publickey" value="<?php echo $paysite_publickey; ?>" placeholder="<?php echo $paysite_publickey; ?>" id="input-vendor" class="form-control" />
		  <?php if ($error_publickey) { ?>
		  <span class="error"><?php echo $error_publickey; ?></span>
		  <?php } ?>
		</td>
	  </tr>
	    <tr>
            <td><?php echo $entry_paymentoptions; ?></td>
            <td>
              <select name="paysite_paymentoptions[]" id="paysite_paymentoptions" multiple class="form-control">
					<?php 
					foreach ($paymentOptions as $key=>$value) 
					{
					echo "<option value=".$key.">".$value."</option>";
						
					} 
				?>							
              </select>
            </td>
          </tr>
		  	<tr>
            <td><?php echo $entry_processed_status; ?></td>
            <td>
              <select name="paysite_processed_status_id" id="input-order-status" class="form-control">
                <?php foreach ($order_statuses as $order_status) { ?>
                <?php if ($order_status['order_status_id'] == $paysite_processed_status_id) { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                <?php } ?>
                <?php } ?>
              </select>
            </td>
          </tr>
		    <tr>
            <td><?php echo $entry_geo_zone; ?></td>
            <td>
              <select name="paysite_geo_zone_id" id="input-geo-zone" class="form-control">
                <option value="0"><?php echo $text_all_zones; ?></option>
                <?php foreach ($geo_zones as $geo_zone) { ?>
                <?php if ($geo_zone['geo_zone_id'] == $paysite_geo_zone_id) { ?>
                <option value="<?php echo $geo_zone['geo_zone_id']; ?>" selected="selected"><?php echo $geo_zone['name']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $geo_zone['geo_zone_id']; ?>"><?php echo $geo_zone['name']; ?></option>
                <?php } ?>
                <?php } ?>
              </select>
            </td>
          </tr>
		   <tr>
            <td><?php echo $entry_status; ?></td>
            <td>
              <select name="paysite_status" id="input-status" class="form-control">
                <?php if ($paysite_status) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select>
			  </td>
            </tr>
			<tr>
            <td><?php echo $entry_sort_order; ?></td>
            <td>
              <input type="text" name="paysite_sort_order" value="<?php echo $paysite_sort_order; ?>" placeholder="<?php echo $paysite_sort_order; ?>" id="input-sort-order" class="form-control" />
            </td>
          </tr>
	
         </table>
      </form>
    </div>
  </div>

</div>
<script type="text/javascript">
	$(function() {
	var data = "<?php $paysite_paymentoptions; 
	$prefix = ''; 
	$paymentmodeList ='';
	foreach ($paysite_paymentoptions as $code=>$name){
	$paymentmodeList .= $prefix . $name;
	$prefix = ',';
	}
	echo $paymentmodeList;
	?>";
	var dataarray = data.split(",");
	$("#paysite_paymentoptions").val(dataarray);
		});
</script>
<?php echo $footer; ?> 