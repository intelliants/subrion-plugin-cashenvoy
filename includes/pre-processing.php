<?php
//##copyright##

$formValues['ce_merchantid'] = $iaCore->get('cashenvoy_id');
$key = $iaCore->get('cashenvoy_key');
$formValues['ce_transref'] = $transaction['sec_key'];
$formValues['ce_amount'] = $transaction['amount'];
$formValues['ce_customerid'] = iaUsers::hasIdentity() ? iaUsers::getIdentity()->email : $iaCore->get('site_email');
$formValues['ce_memo'] = $transaction['operation'];
$formValues['ce_notifyurl'] = IA_RETURN_URL . 'completed' . IA_URL_DELIMITER;

$data = $key.$formValues['ce_transref'].$formValues['ce_amount'];
$formValues['ce_signature'] = hash_hmac('sha256', $data, $key, false);

$iaView->assign('formValues', $formValues);