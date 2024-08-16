<?php

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ContinuationReview\Section;

use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail;
use Dvsa\Olcs\Api\Entity\Organisation\OrganisationUser;
use Dvsa\Olcs\Api\Entity\User\Role;
use Dvsa\Olcs\Api\Entity\User\User;

class UsersReviewService extends AbstractReviewService
{
    /**
     * Format the readonly config from the given data
     *
     * @param ContinuationDetail $continuationDetail continuation detail
     *
     * @return array
     */
    public function getConfigFromData(ContinuationDetail $continuationDetail)
    {
        $organisationUsers = $continuationDetail->getLicence()->getOrganisation()->getOrganisationUsers();

        $header[] = [
            'value' => 'continuations.users-section.table.name',
            'header' => true
        ];
        $header[] = [
            'value' => 'continuations.users-section.table.email',
            'header' => true
        ];
        $header[] = [
            'value' => 'continuations.users-section.table.permission',
            'header' => true
        ];

        $config = [];
        /** @var OrganisationUser $organisationUser */
        foreach ($organisationUsers as $organisationUser) {
            /** @var User $user */
            $user = $organisationUser->getUser();
            $row = [];
            $row[] = ['value' => $user->getContactDetails()->getPerson()->getFullName()];
            $row[] = ['value' => $user->getContactDetails()->getEmailAddress()];
            $row[] = ['value' => implode(
                ',',
                array_map(
                    fn(Role $role) => $this->translate('role.' . $role->getRole()),
                    $user->getRoles()->toArray()
                )
            )];

            $config[] = $row;
        }

        usort(
            $config,
            fn($a, $b) => strcmp($a[0]['value'], $b[0]['value'])
        );

        return (count($config) === 0)
            ? ['emptyTableMessage' => $this->translate('continuations.users-section-empty-table-message')]
            : array_merge([$header], $config);
    }
}
