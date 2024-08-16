<?php

namespace Dvsa\Olcs\Api\Entity\Messaging;

use Doctrine\ORM\Mapping as ORM;

/**
 * MessagingUserMessageRead Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="messaging_user_message_read",
 *    indexes={
 *        @ORM\Index(name="IDX_B9D49F7EA76ED395", columns={"user_id"}),
 *        @ORM\Index(name="fk_messaging_user_message_read_created_by_user_id", columns={"created_by"}),
 *        @ORM\Index(name="fk_messaging_user_message_read_last_modified_by_user_id",
     *     columns={"last_modified_by"}),
 *        @ORM\Index(name="fk_messaging_user_message_read_messaging_message_id",
     *     columns={"message_id"})
 *    }
 * )
 */
class MessagingUserMessageRead extends AbstractMessagingUserMessageRead
{
}
