<?php

namespace Olcs\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Common\Form\Elements\Custom\DateTimeSelect;
use Dvsa\Olcs\Utils\Helper\DateTimeHelper;
use Laminas\Form\FormInterface;
use Olcs\Module;

/**
 * Class PiHearing
 * @package Olcs\Data\Mapper
 */
class PiHearing implements MapperInterface
{
    /**
     * Should map data from a result array into an array suitable for a form
     *
     * @param array $data API data
     *
     * @return array
     */
    public static function mapFromResult(array $data)
    {
        $formData['fields'] = $data;

        if (!isset($formData['fields']['witnesses'])) {
            $formData['fields']['witnesses'] = 0;
        }

        if (!isset($formData['fields']['drivers'])) {
            $formData['fields']['drivers'] = 0;
        }

        if (!empty($formData['fields']['venueOther'])) {
            $formData['fields']['venue'] = 'other';
        }

        if (!isset($formData['fields']['isFullDay']) ||
            ($formData['fields']['isFullDay'] !== 'N' && $formData['fields']['isFullDay'] !== 'Y')
        ) {
            $formData['fields']['isFullDay'] = 'not-set';
        }

        foreach ($formData['fields'] as $key => $value) {
            if (isset($value['id'])) {
                $formData['fields'][$key] = $value['id'];
            }
        }

        return $formData;
    }

    /**
     * Should map form data back into a command data structure
     *
     * @param array $data Form data
     *
     * @return array
     */
    public static function mapFromForm(array $data)
    {
        if ($data['fields']['venue'] === 'other') {
            $data['fields']['venue'] = null;
        } else {
            $data['fields']['venueOther'] = null;
        }

        if ($data['fields']['isCancelled'] != 'Y') {
            $data['fields']['cancelledReason'] = null;
            $data['fields']['cancelledDate'] = null;
        }

        if ($data['fields']['isAdjourned'] != 'Y') {
            $data['fields']['adjournedReason'] = null;
            $data['fields']['adjournedDate'] = null;
        }

        $publish = 'N';

        if (isset($data['form-actions']['publish']) && $data['form-actions']['publish'] !== null) {
            $publish = 'Y';
            $data['fields']['text2'] = $data['fields']['details'];
        }

        $data = $data['fields'];
        $data['publish'] = $publish;

        return $data;
    }

    /**
     * Should map errors onto the form, any global errors should be returned so they can be added
     * to the flash messenger
     *
     * @param FormInterface $form   Form
     * @param array         $errors API errors
     *
     * @return array
     */
    public static function mapFromErrors(FormInterface $form, array $errors)
    {
        if (!empty($errors['messages'])) {
            foreach ($errors['messages'] as $key => $value) {
                if ($key === 'HEARING_DATE_BEFORE_PI') {
                    /** @var DateTimeSelect $e */
                    $piDate = DateTimeHelper::format($value, Module::$dateFormat);
                    $form->get('fields')->get('hearingDate')->setMessages(
                        ['Hearing date must be after or the same as the PI agreed date '. $piDate]
                    );
                    unset($errors['messages'][$key]);
                }
            }
        }

        return $errors;
    }
}
