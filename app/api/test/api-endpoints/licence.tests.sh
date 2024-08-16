
echo " |- Licence\CreateVariation"
executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "licence/$userLicenceId/variation" 201 POST '{"id":'$userLicenceId'}'

executeSql "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "licence/$userLicenceId/variation" 403 POST '{"id":'$userLicenceId'}'


echo " |- Licence\UpdateAddresses"
executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "licence/$userLicenceId/addresses" 200 PUT '{"id":'$userLicenceId'}'

executeSql "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "licence/$userLicenceId/addresses" 403 PUT '{"id":'$userLicenceId'}'

echo " |- Licence\UpdateBusinessDetails"

executeSql "UPDATE licence SET in_force_date = NULL WHERE organisation_id = $userOrganisationId"
executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
# assert 409, this means it got past the validation
assertHttpCode "organisation/business-details/licence/$userLicenceId" 409 PUT '{"id":'$userLicenceId',"version":999999,"name":"Foo","natureOfBusiness":"Bar"}'

executeSql "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "organisation/business-details/licence/$userLicenceId" 403 PUT '{"id":'$userLicenceId',"version":999999,"name":"Foo","natureOfBusiness":"Bar"}'

echo " |- Licence\UpdateOperatingCentres"

executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
# assert 409, this means it got past the validation
assertHttpCode "licence/$userLicenceId/operating-centres" 409 PUT '{"id":'$userLicenceId',"version":999999}'

executeSql "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "licence/$userLicenceId/operating-centres" 403 PUT '{"id":'$userLicenceId',"version":999999}'

echo " |- Licence\UpdateTypeOfLicence"

executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
# assert 409, this means it got past the validation
assertHttpCode "licence/$userLicenceId/type-of-licence" 409 PUT '{"id":'$userLicenceId',"version":999999,"licenceType":"ltyp_r"}'

executeSql "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "licence/$userLicenceId/type-of-licence" 403 PUT '{"id":'$userLicenceId',"version":999999,"licenceType":"ltyp_r"}'

echo " |- QueryHandler\Licence\Addresses"

executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "licence/$userLicenceId/addresses"

executeSql "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "licence/$userLicenceId/addresses" 403

echo " |- QueryHandler\Licence\BusinessDetails"

executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "organisation/business-details/licence/$userLicenceId"

executeSql "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "organisation/business-details/licence/$userLicenceId" 403

echo " |- QueryHandler\Licence\ConditionUndertaking"

executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "licence/$userLicenceId/condition-undertaking"

executeSql "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "licence/$userLicenceId/condition-undertaking" 403

echo " |- QueryHandler\Licence\Licence"

executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "licence/$userLicenceId"

executeSql "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "licence/$userLicenceId" 403

echo " |- QueryHandler\Licence\LicenceByNumber"
licNo="OB1234567"
executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "licence/by-number/$licNo"

executeSql "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "licence/by-number/$licNo" 403

echo " |- QueryHandler\Licence\LicenceRegisteredAddress"
executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
# assert 400, as this licence doesn't have an address, but this means it passed validation
assertHttpCode "licence/registered-address/$licNo" 400

executeSql "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
# assert 400, as this licence doesn't have an address, but this means it passed validation
assertHttpCode "licence/registered-address/$licNo" 400

echo " |- QueryHandler\Licence\OtherActiveLicences"
executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "licence/$userLicenceId/other-active-licences"

executeSql "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "licence/$userLicenceId/other-active-licences" 403

echo " |- QueryHandler\Licence\TaxiPhv"
executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "licence/$userLicenceId/taxi-phv"

executeSql "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "licence/$userLicenceId/taxi-phv" 403

echo " |- QueryHandler\Licence\TypeOfLicence"
executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "licence/$userLicenceId/type-of-licence"

executeSql "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "licence/$userLicenceId/type-of-licence" 403






