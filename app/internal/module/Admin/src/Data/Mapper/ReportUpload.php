<?php

namespace Admin\Data\Mapper;

use Common\Data\Mapper\MapperInterface;

/**
 * Report Upload Mapper
 *
 * @package Admin\Data\Mapper
 */
class ReportUpload implements MapperInterface
{
    /**
     * Should map data from a result array into an array suitable for a form
     *
     * @param array $data Data from command
     *
     * @return array
     */
    public static function mapFromResult(array $data): array
    {
        return $data;
    }

    /**
     * @param array $data
     * @return array
     */
    public static function mapLetterTemplateOptions(array $data)
    {
        $valueOptions = [];
        foreach ($data['results'] as $template) {
            $valueOptions[] = [
                'value' => $template['templateSlug'],
                'label' => $template['templateSlug'],
            ];
        }
        return $valueOptions;
    }

    /**
     * @param $data
     * @return array
     */
    public static function mapEmailTemplateOptions(array $data)
    {
        $names = [];
        $valueOptions = [];
        foreach ($data['results'] as $template) {
            if (!in_array($template['name'], $names)) {
                $valueOptions[] = [
                    'value' => $template['name'],
                    'label' => $template['name'],
                ];
                $names[] = $template['name'];
            }
        }
        return $valueOptions;
    }
}
