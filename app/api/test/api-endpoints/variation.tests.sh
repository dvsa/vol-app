echo "Testing variation"
variationId=2

echo " |- Testing type of licence for variation query"

executeSql "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "variation/$variationId/type-of-licence" 403 GET ''

executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "variation/$variationId/type-of-licence" 200 GET ''

echo " |- Testing update type of licence for variation command"

executeSql "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
executeSql "UPDATE application SET version=1 WHERE id = $variationId"
assertHttpCode "variation/$variationId/type-of-licence" 403 PUT '{"id":"'"$variationId"'","version":1,"licenceType":"ltyp_si"}'

executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "variation/$variationId/type-of-licence" 200 PUT '{"id":"'"$variationId"'","version":1,"licenceType":"ltyp_si"}'

echo " |- Testing update address for variation command"

executeSql "UPDATE organisation_user SET organisation_id = $notUserOrganisationId WHERE user_id = $userId"
assertHttpCode "variation/$variationId/addresses" 403 PUT '{"id":"'$variationId'"}'

executeSql "UPDATE organisation_user SET organisation_id = $userOrganisationId WHERE user_id = $userId"
assertHttpCode "variation/$variationId/addresses" 200 PUT '{"id":"'$variationId'"}'
