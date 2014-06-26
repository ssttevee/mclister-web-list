<div class="page-header">
	<h1>Confirm Order</h1>
</div>

<div class="panel panel-default">
	<div class="panel-body">
		<div class="col-lg-8">
			Review the payment details below and click <b>Pay</b> to complete your secure payment.<br/><br/>
			
			<div class="detail-list-group">
				<div class="detail-list-set"><div class="detail-list-title" style="text-align:right;">Pay to: </div><div class="detail-list-detail">StevieSoft LLC</div></div>
				<div class="detail-list-set"><div class="detail-list-title" style="text-align:right;">User Status:</div><div class="detail-list-detail"><?php echo $paypal_checkout_details['PAYERSTATUS']; ?></div></div>
				<div class="detail-list-set"><div class="detail-list-title" style="text-align:right;">Item:</div><div class="detail-list-detail"><?php echo $paypal_checkout_details['DESC']; ?></div></div>
				<div class="detail-list-set"><div class="detail-list-title" style="text-align:right;">Amount:</div><div class="detail-list-detail">$<?php echo $paypal_checkout_details['AMT']; ?> <?php echo $paypal_checkout_details['CURRENCYCODE']; ?></div></div>
				<div class="detail-list-set"><div class="detail-list-title" style="text-align:right;">Purchase for:</div><div class="detail-list-detail"><?php echo $get_current_user()['name']; ?></div></div>	
			</div>
			<a class="btn btn-danger pull-right">Cancel</a>
			<a href="javascript:;" class="btn btn-primary pull-right" style="margin-right:10px;" id="complete-payment">Pay</a>
		</div>
	</div>
</div>
<script>
$('#complete-payment').click(function() {
	var form = $('<form action="<?php echo $ALIASES['paypal_do_payment']; ?>" method="post">' +
		'<input type="hidden" name="token" value="<?php echo $GET['token']; ?>" />' +
		'</form>');
	$('body').append(form);
	$(form).submit();
	return false;
});
</script>