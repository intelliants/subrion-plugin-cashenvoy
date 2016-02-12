<?php
//##copyright##

$cemertid = $iaCore->get('cashenvoy_id');
$key = $iaCore->get('cashenvoy_key');
$cetxref = $transaction['sec_key'];
$ceamt = $transaction['amount'];
$cecustomerid = $iaCore->get('site_email');
$cememo = $transaction['operation'];
$cenurl = IA_RETURN_URL . 'completed' . IA_URL_DELIMITER;

$data = $key.$cetxref.$ceamt;
$signature = hash_hmac('sha256', $data, $key, false);

$formUrl = $iaCore->get('cashenvoy_demo') ? 'https://www.cashenvoy.com/sandbox/?cmd=cepay' : 'https://www.cashenvoy.com/webservice/?cmd=cepay';
?>

<body onLoad="document.submit2cepay_form.submit()">
	<form method="post" name="submit2cepay_form" action="<?= $formUrl ?>">
		<input type="hidden" name="ce_merchantid" value="<?= $cemertid ?>"/>
		<input type="hidden" name="ce_transref" value="<?= $cetxref ?>"/>
		<input type="hidden" name="ce_amount" value="<?= $ceamt ?>"/>
		<input type="hidden" name="ce_customerid" value="<?= $cecustomerid ?>"/>
		<input type="hidden" name="ce_memo" value="<?= $cememo ?>"/>
		<input type="hidden" name="ce_notifyurl" value="<?= $cenurl ?>"/>
		<input type="hidden" name="ce_window" value="parent"/><!-- self or parent -->
		<input type="hidden" name="ce_signature" value="<?= $signature ?>"/>
	</form>
</body>