
echo " |- QueryHandler\Transaction\Transaction"
txnId=90000
feeId=999999
executeSql "DELETE FROM fee_txn WHERE txn_id = $txnId"
executeSql "REPLACE INTO fee (id, fee_type_id, fee_status, net_amount, gross_amount, application_id) VALUES ($feeId, 1, 'lfs_ot', 100, 100, $userApplicationId)"
executeSql "REPLACE INTO txn (id, status, reference, type, payment_method) VALUES ($txnId, 'pay_s_pd', 'OLCS-1', 'trt_payment', 'fpm_card_online')"
executeSql "INSERT INTO fee_txn (fee_id, txn_id) VALUES($feeId, $txnId)"

executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "transaction/$txnId"

executeSql "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "transaction/$txnId" 403

executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "transaction/ref/OLCS-1"

executeSql "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "transaction/ref/OLCS-1" 403


echo " |- PayOutstandingFees"

# disable card payments so we can just test the validation
executeSql "UPDATE system_parameter SET param_value = 1 WHERE id='DISABLED_SELFSERVE_CARD_PAYMENTS'"

executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "transaction/pay-outstanding-fees" 201 POST '{"applicationId":"'$userApplicationId'","paymentMethod":"fpm_card_online"}'

executeSql  "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "transaction/pay-outstanding-fees" 403 POST '{"applicationId":"'$userApplicationId'","paymentMethod":"fpm_card_online"}'

executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "transaction/pay-outstanding-fees" 201 POST '{"organisationId":"'$userOrganisationId'","paymentMethod":"fpm_card_online"}'
assertHttpCode "transaction/pay-outstanding-fees" 403 POST '{"organisationId":"'$notUserOrganisationId'","paymentMethod":"fpm_card_online"}'


echo " |- Complete transaction"
# set status to "pay_s_pd" so that not call is made to CPMS
executeSql "DELETE FROM fee_txn WHERE txn_id = $txnId"
executeSql "DELETE FROM fee WHERE id = $feeId"
executeSql "REPLACE INTO txn (id, status, reference, type) VALUES ($txnId, 'pay_s_pd', 'OLCS-1', 'trt_payment')"

executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
# assert 400 bad request as the payment has already been paid, prevent call to CPMS
assertHttpCode "transaction/ref/OLCS-1" 403 POST '{"reference":"OLCS-1","paymentMethod":"fpm_card_offline"}'

executeSql "DELETE FROM fee_txn WHERE txn_id = $txnId"
executeSql "REPLACE INTO fee (id, fee_type_id, fee_status, net_amount, gross_amount) VALUES ($feeId, 1, 'lfs_ot', 100, 100)"
assertHttpCode "transaction/ref/OLCS-1" 403 POST '{"reference":"OLCS-1","paymentMethod":"fpm_card_offline"}'

executeSql "DELETE FROM fee_txn WHERE txn_id = $txnId"
executeSql "REPLACE INTO fee (id, fee_type_id, fee_status, net_amount, gross_amount, application_id) VALUES ($feeId, 1, 'lfs_ot', 100, 100, $userApplicationId)"
executeSql "INSERT INTO fee_txn (fee_id, txn_id) VALUES($feeId, $txnId)"
assertHttpCode "transaction/ref/OLCS-1" 400 POST '{"reference":"OLCS-1","paymentMethod":"fpm_card_offline"}'

executeSql  "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "transaction/ref/OLCS-1" 403 POST '{"reference":"OLCS-1","paymentMethod":"fpm_card_offline"}'



