<?php

/**
 * Letter
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Document;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Entity;

/**
 * Letter
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Letter extends AbstractQueryHandler
{
    protected $repoServiceName = 'Document';

    protected $extraRepos = ['DocTemplate'];

    public function handleQuery(QueryInterface $query)
    {
        /** @var Entity\Doc\Document $doc */
        $doc = $this->getRepo()->fetchUsingId($query);

        $meta = json_decode($doc->getMetadata(), true);

        /** @var Entity\Doc\DocTemplate $docTemplate */
        $docTemplate = $this->getRepo('DocTemplate')->fetchById($meta['details']['documentTemplate']);

        return $this->result(
            $doc,
            [
                'category',
                'subCategory'
            ],
            [
                'template' => $this->result($docTemplate)->serialize()
            ]
        );
    }
}
