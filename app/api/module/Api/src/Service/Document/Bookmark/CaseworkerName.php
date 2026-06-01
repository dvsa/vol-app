<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\UserBundle as Qry;

/**
 * Caseworker name bookmark
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class CaseworkerName extends DynamicBookmark
{
    #[\Override]
    public function getQuery(array $data)
    {
        // Skip the query when no user id is in scope (e.g. preview of a letter with no
        // createdBy yet) — render() will fall back to a generic "Caseworker" label.
        if (empty($data['user'])) {
            return null;
        }

        $bundle = [
            'contactDetails' => [
                'person'
            ]
        ];
        return Qry::create(['id' => $data['user'], 'bundle' => $bundle]);
    }

    #[\Override]
    public function render()
    {
        $person = $this->data['contactDetails']['person'] ?? null;
        if (!is_array($person)) {
            // Mirrors the previous LetterPreviewService::buildCaseworkerName fallback
            // when the user has no contact details / person set, or no user at all.
            return 'Caseworker';
        }
        return Formatter\Name::format($person);
    }
}
