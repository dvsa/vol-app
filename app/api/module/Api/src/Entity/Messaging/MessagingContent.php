<?php

namespace Dvsa\Olcs\Api\Entity\Messaging;

use Doctrine\ORM\Mapping as ORM;

/**
 * MessagingContent Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="messaging_content",
 *    indexes={
 *        @ORM\Index(name="fk_messaging_content_created_by_user_id", columns={"created_by"}),
 *        @ORM\Index(name="fk_messaging_content_last_modified_by_user_id",
     *     columns={"last_modified_by"})
 *    }
 * )
 */
class MessagingContent extends AbstractMessagingContent
{
}
