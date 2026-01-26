<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\LicenceBundle as Qry;

/**
 * Operator Admin Email
 *
 * Returns comma-separated email addresses of all admin users for the operator.
 */
class OperatorAdminEmail extends DynamicBookmark
{
    public const PARAM_LICENCE = 'licence';

    protected $params = [self::PARAM_LICENCE];

    /**
     * Get the query for operator admin email data
     *
     * @param array $data The context data containing licence ID
     * @return Qry The query for fetching licence with organisation users
     */
    public function getQuery(array $data)
    {
        return Qry::create([
            'id' => $data[self::PARAM_LICENCE],
            'bundle' => [
                'organisation' => [
                    'organisationUsers' => [
                        'user' => [
                            'contactDetails' => []
                        ]
                    ]
                ]
            ]
        ]);
    }

    /**
     * Render the operator admin email addresses
     *
     * @return string Comma-separated list of admin email addresses, or empty string if none found
     */
    public function render()
    {
        if (empty($this->data['organisation']['organisationUsers'])) {
            return '';
        }

        $emails = [];

        foreach ($this->data['organisation']['organisationUsers'] as $orgUser) {
            // Only include admin users
            if (($orgUser['isAdministrator'] ?? 'N') !== 'Y') {
                continue;
            }

            // Get email from user's contact details
            $email = $orgUser['user']['contactDetails']['emailAddress'] ?? null;

            if (!empty($email)) {
                $emails[] = $email;
            }
        }

        return implode(', ', $emails);
    }
}
