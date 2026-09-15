<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\UserBundle as Qry;

/**
 * Caseworker direct dial bookmark — [[CASEWORKER_PHONE]]
 *
 * The number was previously only reachable through CASEWORKER_DETAILS, which brings the
 * name, office, traffic area, address and email along with it. Content that just wants to
 * say "call me on X" had no way to ask for the number on its own.
 *
 * Primary number, falling back to secondary — the same rule CASEWORKER_DETAILS applies,
 * so the two never disagree about which number is the caseworker's.
 */
class CaseworkerPhone extends DynamicBookmark
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
                    'phoneContacts' => [
                        'phoneContactType'
                    ]
                ]
            ],
        ]);
    }

    #[\Override]
    public function render()
    {
        $phoneContacts = $this->data['contactDetails']['phoneContacts'] ?? [];

        if (!is_array($phoneContacts) || $phoneContacts === []) {
            // A caseworker's number is optional, so this is an ordinary case rather than
            // a fault. Empty lets the letter diagnostics report the unresolved grab;
            // anything invented here would print a wrong number to an operator.
            return '';
        }

        return Formatter\ContactNumber::format($phoneContacts);
    }
}
