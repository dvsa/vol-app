<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\UserBundle as Qry;

/**
 * Caseworker first name bookmark — [[CASEWORKER_FIRST_NAME]]
 *
 * CASEWORKER_NAME renders "forename familyName", which suits a signature block but reads
 * oddly mid-sentence. This lets letter content address the reader by first name without
 * having to restate the sign-off.
 */
class CaseworkerFirstName extends DynamicBookmark
{
    #[\Override]
    public function getQuery(array $data)
    {
        // No user in scope — a preview of a letter with no creating user, for instance.
        // Skip the query rather than run it with a null id; render() copes with no data.
        if (empty($data['user'])) {
            return null;
        }

        return Qry::create([
            'id' => $data['user'],
            'bundle' => [
                'contactDetails' => [
                    'person'
                ]
            ],
        ]);
    }

    #[\Override]
    public function render()
    {
        $forename = $this->data['contactDetails']['person']['forename'] ?? null;

        // Deliberately empty rather than a stand-in label: an empty grab is reported by
        // the letter diagnostics, whereas something like "Caseworker" would read as the
        // person's actual name mid-sentence and go unnoticed all the way to the operator.
        return is_string($forename) ? $forename : '';
    }
}
