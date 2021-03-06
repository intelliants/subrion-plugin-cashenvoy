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

$formValues['ce_merchantid'] = $iaCore->get('cashenvoy_id');
$key = $iaCore->get('cashenvoy_key');
$formValues['ce_transref'] = $transaction['sec_key'];
$formValues['ce_amount'] = $transaction['amount'];
$formValues['ce_customerid'] = iaUsers::hasIdentity() ? iaUsers::getIdentity()->email : $iaCore->get('site_email');
$formValues['ce_memo'] = $transaction['operation'];
$formValues['ce_notifyurl'] = IA_RETURN_URL . 'completed' . IA_URL_DELIMITER;

$data = $key . $formValues['ce_transref'] . $formValues['ce_amount'];
$formValues['ce_signature'] = hash_hmac('sha256', $data, $key, false);

$iaView->assign('formValues', $formValues);