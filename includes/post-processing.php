<?php
/******************************************************************************
 *
 * Subrion - open source content management system
 * Copyright (C) 2016 Intelliants, LLC <http://www.intelliants.com>
 *
 * This file is part of Subrion.
 *
 * Subrion is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Subrion is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Subrion. If not, see <http://www.gnu.org/licenses/>.
 *
 *
 * @link http://www.subrion.org/
 *
 ******************************************************************************/

if (!function_exists('getStatus')) {
    function getStatus($transref, $mertid, $type = '', $sign, $demo = true, $sslVerify = false)
    {
        $request = 'mertid=' . $mertid . '&transref=' . $transref . '&respformat=' . $type . '&signature=' . $sign;

        $url = $demo ? 'https://www.cashenvoy.com/sandbox/?cmd=requery' : 'https://www.cashenvoy.com/webservice/?cmd=requery';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $sslVerify);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $sslVerify);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}

if (isset($action) && in_array($action, array('canceled', 'completed'))) {
    if ('canceled' == $action) {
        $error = true;
        $msg[] = _t('oops');
    }
    if ('completed' == $action) {
        $error = false;

        $iaTransaction = $iaCore->factory('transaction');
        $transaction = $temp_transaction;

        $key = $iaCore->get('cashenvoy_key');
        $transref = $transaction['sec_key'];
        $mertid = (int)$iaCore->get('cashenvoy_id');
        $cdata = $key . $transref . $mertid;
        $signature = hash_hmac('sha256', $cdata, $key, false);
        $type = '';

        $demo = (bool)$iaCore->get('cashenvoy_demo');
        $sslVerify = (bool)$iaCore->get('cashenvoy_ssl_verify');

        $response = getStatus($transref, $mertid, $type, $signature, $demo, $sslVerify);
        $response = strip_tags(preg_replace('#(<title.*?>).*?(</title>)#', '$1$2', $response));

        $cash_data = explode('-', $response);

        $cnt = count($cash_data);
        if ($cnt == 3) {
            $transaction['reference_id'] = $cash_data[0];
            $transaction['payment_status'] = $cash_data[1];
            $transaction['total'] = $cash_data[2];
        } else {
            $error = true;
            $msg = iaLanguage::get('cashenvoy_payment_unknown_error');
            $transaction['status'] = iaTransaction::PENDING;
        }

        if (!$error) {
            switch ($transaction['payment_status']) {
                case 'C00':
                    $error = false;
                    $msg = iaLanguage::get('payment_done');
                    $transaction['status'] = iaTransaction::PASSED;
                    break;
                case 'C01':
                    $error = true;
                    $msg = iaLanguage::get('payment_canceled');
                    $transaction['status'] = iaTransaction::FAILED;
                    break;
                case 'C02':
                    $error = true;
                    $msg = iaLanguage::get('cashenvoy_payment_canceled_by_inactivity');
                    $transaction['status'] = iaTransaction::FAILED;
                    break;
                case 'C03':
                    $error = true;
                    $msg = iaLanguage::get('cashenvoy_payment_no_transaction');
                    $transaction['status'] = iaTransaction::FAILED;
                    break;
                case 'C04':
                    $error = true;
                    $msg = iaLanguage::get('cashenvoy_payment_insufficient_funds');
                    $transaction['status'] = iaTransaction::PENDING;
                    break;
                case 'C05':
                    $error = true;
                    $msg = iaLanguage::get('cashenvoy_payment_transaction_failed');
                    $transaction['status'] = iaTransaction::FAILED;
                    break;
            }
        }

        unset($transaction['payment_status']);
        unset($transaction['total']);

        $order['txn_id'] = $transaction['reference_id'];
        $order['payment_status'] = iaLanguage::get($transaction['status'], ucfirst($transaction['status']));
        $order['payer_email'] = $transaction['email'];
        $order['payment_gross'] = $transaction['amount'];
        $order['payment_date'] = $transaction['date'];
        $order['mc_currency'] = $transaction['currency'];
        $order['first_name'] = '';
        $order['last_name'] = '';

        $iaView->setMessages($msg, $error ? iaView::ERROR : iaView::SUCCESS);
    }
}