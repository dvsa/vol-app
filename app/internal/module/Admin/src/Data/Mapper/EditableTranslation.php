<?php

namespace Admin\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Laminas\Form\FormInterface;

/**
 * Editable Translation Mapper
 *
 * @package Admin\Data\Mapper
 */
class EditableTranslation implements MapperInterface
{
    /**
     * Should map data from a result array into an array suitable for a form
     *
     * @param array $data Data from command
     *
     * @return array
     */
    #[\Override]
    public static function mapFromResult(array $data): array
    {
        return $data;
    }

    /**
     * Should map form data back into a command data structure
     *
     * @param array $data Data from form
     *
     * @return array
     */
    public static function mapFromForm(array $data): array
    {
        $data['fields']['translationsArray'] ??= [];
        foreach ($data['fields']['translationsArray'] as $isoCode => $translation) {
            if (empty($translation)) {
                unset($data['fields']['translationsArray'][$isoCode]);
            } else {
                if (($data['fields']['format'] ?? 'text') === 'editorjs') {
                    $content = json_decode((string) $translation, true);
                    if (!is_array($content) || !isset($content['blocks']) || !is_array($content['blocks'])) {
                        throw new \InvalidArgumentException('Rich translation content must be EditorJS JSON');
                    }
                }
                $data['fields']['translationsArray'][$isoCode] = base64_encode((string) $data['fields']['translationsArray'][$isoCode]);
            }
        }

        return $data['fields'];
    }

    /**
     * Should map errors onto the form, any global errors should be returned so they can be added
     * to the flash messenger
     *
     * @param FormInterface $form   Form interface
     * @param array         $errors array response from errors
     *
     * @return array
     */
    public static function mapFromErrors(FormInterface $form, array $errors): array
    {
        return $errors;
    }
}
