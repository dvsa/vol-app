
echo
echo "Testing document download"
documentId=10000

echo " |- Document Upload"
# minimum testing as same validator as document create
executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "document/upload" 201 POST '{"filename":"foo","category":1,"content":"xx","application":7}'

executeSql  "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "document/upload" 403 POST '{"filename":"foo","category":1,"content":"xx","application":7}'

echo " |- QueryHandler\Correspondence\Correspondences"
executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"

assertHttpCode "correspondence?organisation=$userOrganisationId"
assertHttpCode "correspondence?organisation=$notUserOrganisationId" 403

echo " |- Document Create"

echo "   |- Not associated to anything"
executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "document" 403 POST '{"filename":"foo","identifier":"bar","size":100,"category":1}'

echo "   |- Licence"
executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "document" 201 POST '{"filename":"foo","identifier":"bar","size":100,"category":1,"licence":'$userLicenceId'}'

executeSql  "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "document" 403 POST '{"filename":"foo","identifier":"bar","size":100,"category":1,"licence":'$userLicenceId'}'

echo "   |- Application"
executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "document" 201 POST '{"filename":"foo","identifier":"bar","size":100,"category":1,"application":'$userApplicationId'}'

executeSql  "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "document" 403 POST '{"filename":"foo","identifier":"bar","size":100,"category":1,"application":'$userApplicationId'}'

echo "   |- Case"
caseId=24
executeSql "UPDATE cases SET \
    application_id = NULL, \
    transport_manager_id = NULL, \
    licence_id = $userLicenceId \
    WHERE id = $caseId"
executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "document" 201 POST '{"filename":"foo","identifier":"bar","size":100,"category":1,"case":'$caseId'}'

executeSql  "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "document" 403 POST '{"filename":"foo","identifier":"bar","size":100,"category":1,"case":'$caseId'}'

echo "   |- TransportManager"
transportManagerId=1
executeSql "UPDATE transport_manager_application SET transport_manager_id = $transportManagerId, application_id = $userApplicationId"
executeSql "UPDATE transport_manager_licence SET licence_id = 114"

executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "document" 201 POST '{"filename":"foo","identifier":"bar","size":100,"category":1,"transportManager":'$transportManagerId'}'

executeSql  "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "document" 403 POST '{"filename":"foo","identifier":"bar","size":100,"category":1,"transportManager":'$transportManagerId'}'

echo "   |- Operating centre"
operatingCentreId=16
executeSql "UPDATE application_operating_centre SET application_id = $userApplicationId"
executeSql "UPDATE licence_operating_centre SET licence_id = 114"

executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "document" 201 POST '{"filename":"foo","identifier":"bar","size":100,"category":1,"operatingCentre":'$operatingCentreId'}'

executeSql  "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "document" 403 POST '{"filename":"foo","identifier":"bar","size":100,"category":1,"operatingCentre":'$operatingCentreId'}'

echo "   |- Bus reg"
busRegId=16
executeSql "UPDATE bus_reg SET licence_id = $userLicenceId"

executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "document" 201 POST '{"filename":"foo","identifier":"bar","size":100,"category":1,"busReg":'$busRegId'}'

executeSql  "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "document" 403 POST '{"filename":"foo","identifier":"bar","size":100,"category":1,"busReg":'$busRegId'}'

echo "   |- IRFO Organisation"
executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "document" 201 POST '{"filename":"foo","identifier":"bar","size":100,"category":1,"irfoOrganisation":'$userOrganisationId'}'
assertHttpCode "document" 403 POST '{"filename":"foo","identifier":"bar","size":100,"category":1,"irfoOrganisation":'$notUserOrganisationId'}'

echo "   |- Submission"
submissionId=1
caseId=24
executeSql "UPDATE cases SET \
    application_id = $userApplicationId, \
    transport_manager_id = NULL, \
    licence_id = NULL \
    WHERE id = $caseId"
executeSql "UPDATE submission SET case_id = $caseId WHERE id = $submissionId"

executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "document" 201 POST '{"filename":"foo","identifier":"bar","size":100,"category":1,"submission":'$submissionId'}'

executeSql  "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "document" 403 POST '{"filename":"foo","identifier":"bar","size":100,"category":1,"submission":'$submissionId'}'



echo " |- Licence"
executeSql "UPDATE document SET \
    licence_id = $userLicenceId, \
    application_id = NULL, \
    case_id = NULL, \
    transport_manager_id = NULL, \
    operating_centre_id = NULL, \
    bus_reg_id = NULL, \
    irfo_organisation_id = NULL, \
    submission_id = NULL, \
    statement_id = NULL \
    WHERE id = $documentId"

executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "document/download?identifier=$documentId"

executeSql  "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "document/download?identifier=$documentId" 403

echo  " |- Application"
executeSql "UPDATE document SET \
    licence_id = NULL, \
    application_id = $userApplicationId, \
    case_id = NULL, \
    transport_manager_id = NULL, \
    operating_centre_id = NULL, \
    bus_reg_id = NULL, \
    irfo_organisation_id = NULL, \
    submission_id = NULL, \
    statement_id = NULL \
    WHERE id = $documentId"

executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "document/download?identifier=$documentId"

executeSql  "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "document/download?identifier=$documentId" 403

echo  " |- Case"
caseId=24
executeSql "UPDATE document SET \
    licence_id = NULL, \
    application_id = NULL, \
    case_id = $caseId, \
    transport_manager_id = NULL, \
    operating_centre_id = NULL, \
    bus_reg_id = NULL, \
    irfo_organisation_id = NULL, \
    submission_id = NULL, \
    statement_id = NULL \
    WHERE id = $documentId"

echo  "   |- Application"
executeSql "UPDATE cases SET \
    application_id = $userApplicationId, \
    transport_manager_id = NULL, \
    licence_id = NULL \
    WHERE id = $caseId"

executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "document/download?identifier=$documentId"

executeSql  "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "document/download?identifier=$documentId" 403

echo  "   |- Transport Manager"
transportManagerId=1
executeSql "UPDATE cases SET \
    application_id = NULL, \
    transport_manager_id = $transportManagerId, \
    licence_id = NULL \
    WHERE id = $caseId"

echo  "     |- Transport Manager Application"
executeSql "UPDATE transport_manager_application SET application_id = $userApplicationId"
executeSql "UPDATE transport_manager_licence SET licence_id = 114"

executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "document/download?identifier=$documentId"

executeSql  "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "document/download?identifier=$documentId" 403

echo  "     |- Transport Manager Licence"
executeSql "UPDATE transport_manager_application SET application_id = 6"
executeSql "UPDATE transport_manager_licence SET licence_id = $userLicenceId"

executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "document/download?identifier=$documentId"

executeSql  "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "document/download?identifier=$documentId" 403

echo  "   |- Licence"
executeSql "UPDATE cases SET \
    application_id = NULL, \
    transport_manager_id = NULL, \
    licence_id = $userLicenceId \
    WHERE id = $caseId"

executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "document/download?identifier=$documentId"

executeSql  "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "document/download?identifier=$documentId" 403

echo  " |- Transport Manager"
transportManagerId=1
executeSql "UPDATE document SET \
    licence_id = NULL, \
    application_id = NULL, \
    case_id = NULL, \
    transport_manager_id = $transportManagerId, \
    operating_centre_id = NULL, \
    bus_reg_id = NULL, \
    irfo_organisation_id = NULL, \
    submission_id = NULL, \
    statement_id = NULL \
    WHERE id = $documentId"

echo  "   |- Transport Manager Application"
executeSql "UPDATE transport_manager_application SET application_id = $userApplicationId"
executeSql "UPDATE transport_manager_licence SET licence_id = 114"

executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "document/download?identifier=$documentId"

executeSql  "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "document/download?identifier=$documentId" 403

echo  "   |- Transport Manager Licence"
executeSql "UPDATE transport_manager_application SET application_id = 6"
executeSql "UPDATE transport_manager_licence SET licence_id = $userLicenceId"

executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "document/download?identifier=$documentId"

executeSql  "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "document/download?identifier=$documentId" 403

echo  " |- Operating Centre"
operatingCentreId=16
executeSql "UPDATE document SET \
    licence_id = NULL, \
    application_id = NULL, \
    case_id = NULL, \
    transport_manager_id = NULL, \
    operating_centre_id = $operatingCentreId, \
    bus_reg_id = NULL, \
    irfo_organisation_id = NULL, \
    submission_id = NULL, \
    statement_id = NULL \
    WHERE id = $documentId"

executeSql "UPDATE application_operating_centre SET application_id = $userApplicationId"

executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "document/download?identifier=$documentId"

executeSql  "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "document/download?identifier=$documentId" 403

echo  " |- Bus Reg"
busRegId=1
executeSql "UPDATE document SET \
    licence_id = NULL, \
    application_id = NULL, \
    case_id = NULL, \
    transport_manager_id = NULL, \
    operating_centre_id = NULL, \
    bus_reg_id = $busRegId, \
    irfo_organisation_id = NULL, \
    submission_id = NULL, \
    statement_id = NULL \
    WHERE id = $documentId"

executeSql "UPDATE bus_reg SET licence_id = $userLicenceId"

executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "document/download?identifier=$documentId"

executeSql  "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "document/download?identifier=$documentId" 403

echo  " |- IRFO Organisation"
executeSql "UPDATE document SET \
    licence_id = NULL, \
    application_id = NULL, \
    case_id = NULL, \
    transport_manager_id = NULL, \
    operating_centre_id = NULL, \
    bus_reg_id = NULL, \
    irfo_organisation_id = $userOrganisationId, \
    submission_id = NULL, \
    statement_id = NULL \
    WHERE id = $documentId"

executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "document/download?identifier=$documentId"

executeSql  "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "document/download?identifier=$documentId" 403

echo  " |- Submission"
submissionId=1
# Use caseId from previos test

executeSql "UPDATE document SET \
    licence_id = NULL, \
    application_id = NULL, \
    case_id = NULL, \
    transport_manager_id = NULL, \
    operating_centre_id = NULL, \
    bus_reg_id = NULL, \
    irfo_organisation_id = NULL, \
    submission_id = $submissionId, \
    statement_id = NULL \
    WHERE id = $documentId"

executeSql "UPDATE submission SET case_id = $caseId WHERE id = $submissionId"

executeSql "UPDATE cases SET \
    application_id = $userApplicationId, \
    transport_manager_id = NULL, \
    licence_id = NULL \
    WHERE id = $caseId"

executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "document/download?identifier=$documentId"

executeSql  "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "document/download?identifier=$documentId" 403

echo  " |- Statement"
statementId=1
# Use caseId from previous test

executeSql "UPDATE document SET \
    licence_id = NULL, \
    application_id = NULL, \
    case_id = NULL, \
    transport_manager_id = NULL, \
    operating_centre_id = NULL, \
    bus_reg_id = NULL, \
    irfo_organisation_id = NULL, \
    submission_id = NULL, \
    statement_id = $statementId \
    WHERE id = $documentId"

executeSql "UPDATE statement SET case_id = $caseId WHERE id = $statementId"

executeSql "UPDATE cases SET \
    application_id = $userApplicationId, \
    transport_manager_id = NULL, \
    licence_id = NULL \
    WHERE id = $caseId"

executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "document/download?identifier=$documentId"

executeSql  "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "document/download?identifier=$documentId" 403

