<?php

namespace Olcs\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Dvsa\Olcs\Utils\Helper\DateTimeHelper;
use Laminas\Form\FormInterface;
use Olcs\Module;

/**
 * Class Pi
 * @package Olcs\Data\Mapper
 */
class Pi implements MapperInterface
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
        $publish = 'N';

        if (isset($data['form-actions']['publish']) && $data['form-actions']['publish'] !== null) {
            $publish = 'Y';
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
                if ($key === 'DECISION_DATE_BEFORE_HEARING_DATE') {
                    /** @var DateTimeSelect $e */
                    $hearingDate = DateTimeHelper::format($value, Module::$dateFormat);
                    $form->get('fields')->get('decisionDate')->setMessages(
                        ['Decision date must be after or the same as the PI hearing date '. $hearingDate]
                    );
                    unset($errors['messages'][$key]);
                }
            }
        }

        return $errors;
    }
}
