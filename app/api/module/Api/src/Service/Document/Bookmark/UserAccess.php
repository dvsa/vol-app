<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\LicenceBundle as Qry;
use Dvsa\Olcs\Api\Domain\TranslatorAwareInterface;
use Dvsa\Olcs\Api\Domain\TranslatorAwareTrait;
use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;

class UserAccess extends DynamicBookmark implements TranslatorAwareInterface
{
    use TranslatorAwareTrait;

    public const USER_MESSAGE_SELF_SERVE = "You can log in and select 'Manage users' to amend the current users.";
    public const USER_MESSAGE_NON_SELF_SERVE = "You can register for a self-serve account to amend your licence details online.";

    public function getQuery(array $data)
    {
        $bundle = [
            'organisation' => [
                'organisationUsers' => [
                    'user' => [
                        'contactDetails' => [
                            'person'
                        ],
                        'roles'
                    ],
                ]
            ],
        ];
        return Qry::create(['id' => $data['licence'], 'bundle' => $bundle]);
    }

    /**
     * @return string
     */
    public function render()
    {
        if (!empty($this->data['organisation']['organisationUsers'])) {
            $userAccessSnippet = $this->getSnippet('UserAccess');

            return $this->getParser()->replace(
                $userAccessSnippet,
                [
                    'LICENCE_NUMBER' => $this->data['licNo'],
                    'SELF_SERVE_MESSAGE' => $this->getSelfServeMessage(),
                    'USERS_TABLE' => $this->generateUserTable()
                ]
            );
        }

        return '';
    }

    /**
     * @param $rows
     * @return mixed
     */
    protected function sortUsers($rows)
    {
        usort(
            $rows,
            function ($a, $b) {
                if ($a['BOOKMARK1'] == $b['BOOKMARK1']) {
                    return 0;
                } elseif ($a['BOOKMARK1'] < $b['BOOKMARK1']) {
                    return -1;
                } else {
                    return 1;
                }
            }
        );
        return $rows;
    }

    /**
     * @return bool
     */
    protected function isRegisteredForSelfServe(): bool
    {
        $organisationUsers = $this->data['organisation']['organisationUsers'];
        foreach ($organisationUsers as $organisationUser) {
            if ($organisationUser['isAdministrator'] == 'Y') {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string
     */
    protected function generateUserTable(): string
    {
        $header[] = [
            'BOOKMARK1' => 'Name',
            'BOOKMARK2' => 'Email address',
            'BOOKMARK3' => 'Permission'
        ];

        $rows = [];
        $organisationUsers = $this->data['organisation']['organisationUsers'];
        foreach ($organisationUsers as $organisationUser) {
            $user = $organisationUser['user'];
            $email = $user['contactDetails']['emailAddress'] ?? '';
            $forename = $user['contactDetails']['person']['forename'] ?? '';
            $familyName = $user['contactDetails']['person']['familyName'] ?? '';
            $name = trim($forename . ' ' . $familyName);
            $permission = implode(
                ',',
                array_map(
                    fn($role) => $this->getTranslator()->translate('role.' . $role['role']),
                    $user['roles']
                )
            );

            $rows[] = [
                'BOOKMARK1' => $name,
                'BOOKMARK2' => $email,
                'BOOKMARK3' => $permission,
            ];
        }
        $usersTableSnippet = $this->getSnippet('CHECKLIST_3CELL_TABLE');

        $sortUsers = $this->sortUsers($rows);

        $rows = array_pad($sortUsers, 15, ['BOOKMARK1' => '', 'BOOKMARK2' => '', 'BOOKMARK3' => '']);

        $allRows = array_merge($header, $rows);
        $parser = $this->getParser();

        $usersTable = '';
        foreach ($allRows as $tokens) {
            $usersTable .= $parser->replace($usersTableSnippet, $tokens);
        }

        return $usersTable;
    }

    /**
     * @return string
     */
    protected function getSelfServeMessage()
    {
        return $this->isRegisteredForSelfServe() ? self::USER_MESSAGE_SELF_SERVE : self::USER_MESSAGE_NON_SELF_SERVE;
    }
}
