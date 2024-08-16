
echo "Testing application"
applicationId=7

echo " |- Testing application cancel"
executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "application/$applicationId/cancel" 200 PUT '{"id":"'"$applicationId"'"}'

executeSql  "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "application/$applicationId/cancel" 403 PUT '{"id":"'"$applicationId"'"}'

echo " |- Testing application create"
executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "application" 201 POST '{"organisation":"'"$userOrganisationId"'"}'
assertHttpCode "application" 403 POST '{"organisation":"'"$notUserOrganisationId"'"}'

echo " |- Testing application snapshot"
executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "application/$applicationId/snapshot" 201 POST '{"id":"'"$applicationId"'","event":1}'

executeSql  "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "application/$applicationId/snapshot" 403 POST '{"id":"'"$applicationId"'","event":1}'

echo " |- Testing application CreateTaxiPhv"
executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "application/$applicationId/taxi-phv" 201 POST '{"id":"'"$applicationId"'","privateHireLicenceNo":"XX","councilName":"YY","licence":1}'

executeSql  "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "application/$applicationId/taxi-phv" 403 POST '{"id":"'"$applicationId"'","privateHireLicenceNo":"XX","councilName":"YY","licence":1}'

echo " |- Testing application DeleteTaxiPhv"
private_hire_licenceId=1
executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
executeSql "UPDATE private_hire_licence SET deleted_date = NULL WHERE id = $private_hire_licenceId"
assertHttpCode "application/$applicationId/taxi-phv" 200 DELETE '{"id":"'$applicationId'","ids":['$private_hire_licenceId'],"licence":1}'

executeSql "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
executeSql "UPDATE private_hire_licence SET deleted_date = NULL WHERE id = $private_hire_licenceId"
assertHttpCode "application/$applicationId/taxi-phv" 403 DELETE '{"id":"'$applicationId'","ids":['$private_hire_licenceId'],"licence":1}'

echo " |- Testing application GenerateOrganisationName"
executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
executeSql "UPDATE organisation SET type = 'org_t_st'"
assertHttpCode "application/$applicationId/generate-organisation-name" 200 PUT '{"id":"'$applicationId'"}'

executeSql "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "application/$applicationId/generate-organisation-name" 403 PUT '{"id":"'$applicationId'"}'

# For some reason TA gets wiped, so put it back
executeSql "UPDATE licence SET traffic_area_id = 'B'"

echo " |- Testing application SubmitApplication"
executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
executeSql "UPDATE application SET status = 'apsts_not_submitted' WHERE id = $applicationId"
assertHttpCode "application/$applicationId/submit" 200 PUT '{"id":"'$applicationId'"}'

executeSql "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
executeSql "UPDATE application SET status = 'apsts_not_submitted' WHERE id = $applicationId"
assertHttpCode "application/$applicationId/submit" 403 PUT '{"id":"'$applicationId'"}'

echo " |- Testing application UpdateAddresses"
executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "application/$applicationId/addresses" 200 PUT '{"id":"'$applicationId'"}'

executeSql "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "application/$applicationId/addresses" 403 PUT '{"id":"'$applicationId'"}'

echo " |- Testing application UpdateApplicationCompletion"
executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "application/$applicationId/people/update-completion" 201 POST '{"id":"'$applicationId'","section":"people"}'

executeSql "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "application/$applicationId/people/update-completion" 403 POST '{"id":"'$applicationId'","section":"people"}'

echo " |- Testing application UpdateBusinessDetails"
executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
executeSql "UPDATE organisation SET version=10 WHERE id = $userOrganisationId"
# Self servce user can't edit organisation name if has inforce licences
executeSql "UPDATE licence SET in_force_date=NULL WHERE organisation_id = $userOrganisationId"
assertHttpCode "organisation/business-details/application/$applicationId" 200 PUT '{"id":"'$applicationId'","licence":'$userLicenceId',"version":10,"name":"foo"}'

executeSql "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
executeSql "UPDATE organisation SET version=10 WHERE id = $userOrganisationId"
assertHttpCode "organisation/business-details/application/$applicationId" 403 PUT '{"id":"'$applicationId'","licence":'$userLicenceId',"version":10,"name":"foo"}'

echo " |- Testing application UpdateDeclaration"
executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
executeSql "UPDATE application SET version=10 WHERE id = $applicationId"
assertHttpCode "application/$applicationId/declaration" 200 PUT '{"id":"'$applicationId'","version":10,"declarationConfirmation":"Y"}'

executeSql "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "application/$applicationId/declaration" 403 PUT '{"id":"'$applicationId'","version":10,"declarationConfirmation":"Y"}'

echo " |- Testing application UpdateFinancialEvidence"
executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
executeSql "UPDATE application SET version=10 WHERE id = $applicationId"
assertHttpCode "application/$applicationId/financial-evidence" 200 PUT '{"id":"'$applicationId'","version":10,"financialEvidenceUploaded":"N"}'

executeSql "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "application/$applicationId/financial-evidence" 403 PUT '{"id":"'$applicationId'","version":10,"financialEvidenceUploaded":"N"}'

echo " |- Testing application UpdateFinancialHistory"
executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
executeSql "UPDATE application SET version=10 WHERE id = $applicationId"
assertHttpCode "application/$applicationId/financial-history" 200 PUT '{"id":"'$applicationId'","version":10,"bankrupt":"N","liquidation":"N","receivership":"N","administration":"N","disqualified":"N","insolvencyConfirmation":"N"}'

executeSql "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "application/$applicationId/financial-history" 403 PUT '{"id":"'$applicationId'","version":10,"bankrupt":"N","liquidation":"N","receivership":"N","administration":"N","disqualified":"N","insolvencyConfirmation":"N"}'

echo " |- Testing application UpdateTaxiPhv"
executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
# pass validation, but fails on traffic area validation
assertHttpCode "application/$applicationId/taxi-phv" 400 PUT '{"id":"'$applicationId'"}'

executeSql "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "application/$applicationId/taxi-phv" 403 PUT '{"id":"'$applicationId'"}'

echo " |- Testing application UpdateTypeOfLicence"
executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
executeSql "UPDATE application SET version=10 WHERE id = $applicationId"
assertHttpCode "application/$applicationId/type-of-licence" 200 PUT '{"id":"'$applicationId'", "version":10,"licenceType":"ltyp_sn","niFlag":"N","operatorType":"lcat_gv"}'

executeSql "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "application/$applicationId/type-of-licence" 403 PUT '{"id":"'$applicationId'", "version":10,"licenceType":"ltyp_sn","niFlag":"N","operatorType":"lcat_gv"}'

echo " |- Testing application WithdrawApplication"
executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "application/$applicationId/withdraw" 200 PUT '{"id":"'$applicationId'","reason":"withdrawn"}'

executeSql "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "application/$applicationId/withdraw" 403 PUT '{"id":"'$applicationId'","reason":"withdrawn"}'

echo " |- Testing application UpdatePrivateHireLicence"
executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
executeSql "UPDATE private_hire_licence SET version=10 WHERE id = 1"
assertHttpCode "application/$applicationId/taxi-phv/1" 200 PUT '{"id":"'$applicationId'","privateHireLicence":1,"version":10,"privateHireLicenceNo":1,"councilName":"foo","licence":1}'

executeSql "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "application/$applicationId/taxi-phv/1" 403 PUT '{"id":"'$applicationId'","privateHireLicence":1,"version":10,"privateHireLicenceNo":1,"councilName":"foo","licence":1}'

echo " |- Testing application Application"
executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "application/$applicationId"

executeSql "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "application/$applicationId" 403

echo " |- Testing application Declaration"
executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "application/$applicationId/declaration"

executeSql "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "application/$applicationId/declaration" 403

echo " |- Testing application DeclarationUndertakings"
executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "application/$applicationId/declaration-undertakings"

executeSql "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "application/$applicationId/declaration-undertakings" 403

echo " |- Testing application FinancialEvidence"
executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "application/$applicationId/financial-evidence"

executeSql "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "application/$applicationId/financial-evidence" 403

echo " |- Testing application FinancialHistory"
executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "application/$applicationId/financial-history"

executeSql "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "application/$applicationId/financial-history" 403

echo " |- Testing application Review"
executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "application/$applicationId/review"

executeSql "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "application/$applicationId/review" 403

echo " |- Testing application Schedule41Approve"
executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "application/$applicationId/approve-schedule-41"

executeSql "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "application/$applicationId/approve-schedule-41" 403

echo " |- Testing application Summary"
executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "application/$applicationId/summary"

executeSql "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "application/$applicationId/summary" 403

echo " |- Testing application TaxiPhv"
executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "application/$applicationId/taxi-phv"

executeSql "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "application/$applicationId/taxi-phv" 403
