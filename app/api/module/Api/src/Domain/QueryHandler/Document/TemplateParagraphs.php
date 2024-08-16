<?php

/**
 * TemplateParagraphs
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Document;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * TemplateParagraphs
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TemplateParagraphs extends AbstractQueryHandler
{
    protected $repoServiceName = 'DocTemplate';

    public function handleQuery(QueryInterface $query)
    {
        $entity = $this->getRepo()->fetchUsingId($query);

        return $this->result(
            $entity,
            [
                'docTemplateBookmarks' => [
                    'docBookmark' => [
                        'docParagraphBookmarks' => [
                            'docParagraph' => []
                        ]
                    ]
                ]
            ]
        );
    }
}
