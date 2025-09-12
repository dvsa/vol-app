<?php

declare(strict_types=1);

namespace Olcs\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Laminas\Form\FormInterface;

/**
 * PI SLA Exception Mapper
 */
class PiSlaException implements MapperInterface
{
    /**
     * Should map data from a result array into an array suitable for a form
     *
     * @param array $data API data
     * @return array
     */
    public static function mapFromResult(array $data): array
    {
        // For add form, we just need the PI ID and case ID for context
        return [
            'fields' => [
                'pi' => $data['id'] ?? null,
                'case' => $data['case']['id'] ?? null,
            ]
        ];
    }

    /**
     * Should map form data back into a command data structure
     *
     * @param array $data Form data
     * @return array
     */
    public static function mapFromForm(array $data): array
    {
        $fields = $data['fields'] ?? [];
        
        return [
            'pi' => $fields['pi'] ?? null,
            'slaException' => $fields['slaException'] ?? null,
        ];
    }

    /**
     * Should map errors onto the form, any global errors should be returned so they can be added
     * to the flash messenger
     *
     * @param FormInterface $form Form
     * @param array $errors API errors
     * @return array
     */
    public static function mapFromErrors(FormInterface $form, array $errors): array
    {
        // Handle specific validation errors if needed
        if (!empty($errors['messages'])) {
            foreach ($errors['messages'] as $key => $value) {
                if ($key === 'slaException') {
                    $form->get('fields')->get('slaException')->setMessages(
                        is_array($value) ? $value : [$value]
                    );
                    unset($errors['messages'][$key]);
                }
            }
        }

        return $errors;
    }
}
