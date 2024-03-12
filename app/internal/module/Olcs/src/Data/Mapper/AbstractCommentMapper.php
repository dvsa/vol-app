<?php

namespace Olcs\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Laminas\Form\FormInterface;

/**
 * Class AbstractCommentMapper
 * @package Olcs\Data\Mapper
 */
class AbstractCommentMapper implements MapperInterface
{
    public const COMMENT_FIELD = ''; //needs to be constant as methods called statically

    /**
     * Should map data from a result array into an array suitable for a form
     *
     * @param array $data
     */
    public static function mapFromResult(array $data)
    {
        $formData['fields'] = [
            'id' => $data['id'],
            'version' => $data['version'],
            'comment' => $data[static::COMMENT_FIELD],
        ];

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
        $data['fields'][static::COMMENT_FIELD] = $data['fields']['comment'];

        return $data['fields'];
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
