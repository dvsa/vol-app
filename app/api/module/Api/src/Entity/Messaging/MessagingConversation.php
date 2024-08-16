<?php

namespace Dvsa\Olcs\Api\Entity\Messaging;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\LicenceProviderInterface;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\OrganisationProviderInterface;

/**
 * MessagingConversation Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="messaging_conversation",
 *    indexes={
 *        @ORM\Index(name="fk_messaging_conversation_created_by_user_id", columns={"created_by"}),
 *        @ORM\Index(name="fk_messaging_conversation_last_modified_by_user_id",
     *     columns={"last_modified_by"}),
 *        @ORM\Index(name="fk_messaging_conversation_task_id", columns={"task_id"})
 *    }
 * )
 */
class MessagingConversation extends AbstractMessagingConversation implements LicenceProviderInterface, OrganisationProviderInterface
{
    public function getRelatedLicence(): Licence
    {
        return $this->task->getLicence();
    }

    public function getRelatedOrganisation(): Organisation
    {
        return $this->getRelatedLicence()->getOrganisation();
    }
}
