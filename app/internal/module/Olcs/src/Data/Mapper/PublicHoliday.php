<?php

namespace Olcs\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Laminas\Form\FormInterface;

/**
 * Public Holiday mapper
 *
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
class PublicHoliday implements MapperInterface
{
    public const FIELDS = 'fields';

    private static $areas_fields = ['isEngland', 'isWales', 'isScotland', 'isNi'];

    /**
     * Should map data from a result array into an array suitable for a form
     *
     * @param array $data
     */
    public static function mapFromResult(array $data)
    {
        if (empty($data)) {
            return [
                self::FIELDS => []
            ];
        }

        return [
            self::FIELDS => [
                'id' => $data['id'],
                'areas' => array_filter(
                    self::$areas_fields,
                    fn($fld) => isset($data[$fld]) && $data[$fld] === 'Y'
                ),
                'holidayDate' => $data['publicHolidayDate'],
            ],
        ];
    }

    /**
     * Should map form data back into a command data structure
     *
     * @param array $data
     *
     * @return array
     */
    public static function mapFromForm(array $data)
    {
        $data = $data[self::FIELDS];

        foreach ($data['areas'] as $fld) {
            $data[$fld] = 'Y';
        }

        unset($data['areas']);

        return $data;
    }

    /**
     * Should map errors onto the form, any global errors should be returned so they can be added
     * to the flash messenger
     *
     * @param FormInterface $form
     * @param array $errors
     *
     * @return array
     */
    public static function mapFromErrors(FormInterface $form, array $errors)
    {
        $form->setMessages([self::FIELDS => $errors['messages']]);

        return $errors;
    }
}
