<?php

namespace Permits\Data\Mapper;

use Common\Data\Mapper\MapperInterface;

/**
 * Fee mapper
 */
class IrhpFee implements MapperInterface
{
    use MapFromResultTrait;

    public const SUBMIT_APPLICATION_CAPTION = 'permits.button.submit-application';

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
