<?php

/**
 * Letter Generation Document
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Data\Mapper;

/**
 * Letter Generation Document
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LetterGenerationDocument implements MapperInterface
{
    /**
     * @return (array|mixed)[]
     *
     * @psalm-return array{data: array, details: array{category: mixed, documentSubCategory: mixed, documentTemplate: mixed, bookmarks: mixed}, bookmarks: mixed}
     */
    #[\Override]
    public static function mapFromResult(array $data)
    {
        $meta = json_decode($data['metadata'], true);

        return [
            'data' => $data,
            'details' => [
                // These bits are here for backwards compatibility
                'category' => $data['category']['id'],
                'documentSubCategory' => $data['subCategory']['id'],
                'documentTemplate' => $meta['details']['documentTemplate'],
                'bookmarks' => $meta['bookmarks']
            ],
            'bookmarks' => $meta['bookmarks']
        ];
    }
}
