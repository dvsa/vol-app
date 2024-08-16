<?php

namespace Dvsa\Olcs\Api\Entity\Note;

use Doctrine\ORM\Mapping as ORM;

/**
 * Note Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="note",
 *    indexes={
 *        @ORM\Index(name="ix_note_application_id", columns={"application_id"}),
 *        @ORM\Index(name="ix_note_licence_id", columns={"licence_id"}),
 *        @ORM\Index(name="ix_note_case_id", columns={"case_id"}),
 *        @ORM\Index(name="ix_note_organisation_id", columns={"organisation_id"}),
 *        @ORM\Index(name="ix_note_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_note_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_note_note_type", columns={"note_type"}),
 *        @ORM\Index(name="ix_note_bus_reg_id", columns={"bus_reg_id"}),
 *        @ORM\Index(name="fk_note_transport_manager1_idx", columns={"transport_manager_id"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_note_olbs_key_olbs_type", columns={"olbs_key","olbs_type"})
 *    }
 * )
 */
class Note extends AbstractNote
{
    public const NOTE_TYPE_APPLICATION = 'note_t_app';
    public const NOTE_TYPE_BUS = 'note_t_bus';
    public const NOTE_TYPE_CASE = 'note_t_case';
    public const NOTE_TYPE_LICENCE = 'note_t_lic';
    public const NOTE_TYPE_ORGANISATION = 'note_t_org';
    public const NOTE_TYPE_PERMIT = 'note_t_permit';
    public const NOTE_TYPE_PERSON = 'note_t_person';
    public const NOTE_TYPE_TRANSPORT_MANAGER = 'note_t_tm';
}
