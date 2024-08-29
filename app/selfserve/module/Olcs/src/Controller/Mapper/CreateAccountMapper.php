<?php
namespace Olcs\Controller\Mapper;

class CreateAccountMapper
{
    /**
     *
     * @param array $data Posted form data
     * @return array Transformed data
     */
    public function formatSaveData(array $data)
    {
        $output = [];
        $output['loginId'] = $data['fields']['loginId'];
        $output['translateToWelsh'] = $data['fields']['translateToWelsh'];
        $output['contactDetails']['emailAddress'] = $data['fields']['emailAddress'];
        $output['contactDetails']['person']['familyName'] = $data['fields']['familyName'];
        $output['contactDetails']['person']['forename']   = $data['fields']['forename'];

        if ('Y' === $data['fields']['isLicenceHolder']) {
            $output['licenceNumber'] = $data['fields']['licenceNumber'];
        } else {
            $output['organisationName'] = $data['fields']['organisationName'];
            $output['businessType'] = $data['fields']['businessType'];
        }

        return $output;
    }

    /**
     * A radio button is used and validated only if a checkbox is selected.
     * As browsers by default do not post the value or default value of a radio
     * button.  We specify an empty input for this field.
     *
     * @param array $postData Data from posted form
     *
     * @return array
     */
    public function formatPostData(array $postData)
    {
        if (empty($postData['fields']['businessType'])) {
            $postData['fields']['businessType'] = null;
        }

        return $postData;
    }
}
