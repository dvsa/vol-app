<?php

namespace Olcs\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Zend\Form\FormInterface;

/**
 * Class PiHearing
 * @package Olcs\Data\Mapper
 */
class PiHearing implements MapperInterface
{
    /**
     * Should map data from a result array into an array suitable for a form
     *
     * @param array $data
     * @return array
     */
    public static function mapFromResult(array $data)
    {
        $formData['fields'] = $data;

        if (!isset($formData['fields']['witnesses'])) {
            $formData['fields']['witnesses'] = 0;
        }

        if (!empty($formData['fields']['piVenueOther'])) {
            $formData['fields']['piVenue'] = 'other';
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
     * @param array $data
     * @return array
     */
    public static function mapFromForm(array $data)
    {
        if ($data['fields']['piVenue'] === 'other') {
            $data['fields']['piVenue'] = null;
        } else {
            $data['fields']['piVenueOther'] = null;
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
     * @param FormInterface $form
     * @param array $errors
     * @return array
     */
    public static function mapFromErrors(FormInterface $form, array $errors)
    {
        return $errors;
    }
}
