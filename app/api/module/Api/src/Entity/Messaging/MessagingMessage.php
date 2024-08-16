<?php

namespace Dvsa\Olcs\Api\Entity\Messaging;

use Doctrine\ORM\Mapping as ORM;

/**
 * MessagingMessage Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="messaging_message",
 *    indexes={
 *        @ORM\Index(name="fk_messaging_message_created_by_user_id", columns={"created_by"}),
 *        @ORM\Index(name="fk_messaging_message_last_modified_by_user_id",
     *     columns={"last_modified_by"}),
 *        @ORM\Index(name="fk_messaging_message_messaging_content_id", columns={"message_content_id"}),
 *        @ORM\Index(name="fk_messaging_message_messaging_conversation_id",
     *     columns={"conversation_id"})
 *    }
 * )
 */
class MessagingMessage extends AbstractMessagingMessage
{
}
