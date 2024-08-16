<?php

namespace Dvsa\Olcs\Api\Entity\User;

use Doctrine\ORM\Mapping as ORM;

/**
 * Permission Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="permission",
 *    indexes={
 *        @ORM\Index(name="ix_permission_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_permission_last_modified_by", columns={"last_modified_by"})
 *    }
 * )
 */
class Permission extends AbstractPermission
{
    public const SYSTEM_ADMIN = 'system-admin';
    public const INTERNAL_ADMIN = 'internal-admin';
    public const INTERNAL_USER = 'internal-user';
    public const INTERNAL_PERMITS = 'internal-permits';
    public const INTERNAL_PUBLICATIONS = 'internal-publications';
    public const INTERNAL_EDIT = 'internal-edit';
    public const LOCAL_AUTHORITY_ADMIN = 'local-authority-admin';
    public const LOCAL_AUTHORITY_USER = 'local-authority-user';
    public const OPERATOR_ADMIN = 'operator-admin';
    public const OPERATOR_USER = 'operator-user';
    public const PARTNER_ADMIN = 'partner-admin';
    public const PARTNER_USER = 'partner-user';
    public const SELFSERVE_USER = 'selfserve-user';
    public const CAN_UPDATE_LICENCE_LICENCE_TYPE = 'can-update-licence-licence-type';
    public const CAN_MANAGE_USER_INTERNAL = 'can-manage-user-internal';
    public const CAN_MANAGE_USER_SELFSERVE = 'can-manage-user-selfserve';
    public const SELFSERVE_EBSR_LIST = 'selfserve-ebsr-list';
    public const SELFSERVE_EBSR_UPLOAD = 'selfserve-ebsr-upload';
    public const SELFSERVE_EBSR_DOCUMENTS = 'selfserve-ebsr-documents';
    public const CAN_READ_USER_SELFSERVE = 'can-read-user-selfserve';
    public const TRANSPORT_MANAGER = 'selfserve-tm';

    public const CAN_LIST_CONVERSATIONS = 'can-list-conversations';
    public const CAN_LIST_MESSAGES = 'can-list-messages';
    public const CAN_REPLY_TO_CONVERSATIONS = 'can-reply-to-conversation';
    public const CAN_CREATE_CONVERSATION = 'can-create-conversation';
    public const CAN_DISABLE_MESSAGING = 'can-disable-messaging';
    public const CAN_ENABLE_MESSAGING = 'can-enable-messaging';
    public const CAN_CLOSE_CONVERSATION = 'can-close-conversation';
}
