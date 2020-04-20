<?php

namespace Permits\Data\Mapper;

/**
 * Fee mapper
 */
class IrhpFee
{
    const SUBMIT_APPLICATION_CAPTION = 'permits.button.submit-application';

    /**
     * Map for form options
     *
     * @param array $data
     * @param mixed $form
     *
     * @return array
     */
    public function mapForFormOptions(array $data, $form): array
    {
        $applicationData = $data['application'];

        if ($applicationData['isBilateral'] && !$applicationData['hasOutstandingFees']) {
            $form->get('Submit')->get('SubmitButton')->setValue(self::SUBMIT_APPLICATION_CAPTION);
        }

        return $data;
    }
}
