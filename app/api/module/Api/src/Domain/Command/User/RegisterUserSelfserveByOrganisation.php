<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Command\User;

use Dvsa\Olcs\Transfer\Command\User\RegisterUserSelfserve;
use Dvsa\Olcs\Transfer\FieldType\Traits\Organisation;

/**
 * Register a self-serve operator-admin user and link them to an EXISTING organisation by id.
 *
 * This is an INTERNAL command: it has no transfer route and must never be given one. It is only
 * dispatched as a trusted server-side side effect by
 * {@see \Dvsa\Olcs\Api\Domain\CommandHandler\User\RegisterConsultantAndOperator}, where the
 * organisation id originates from an organisation that command has just created — never from
 * client input.
 *
 * The raw organisation-id binding path was deliberately removed from the public, anonymous
 * {@see RegisterUserSelfserve} command (VOL-7370) because it allowed an unauthenticated caller to
 * self-register as OPERATOR_ADMIN of any existing organisation by enumerable primary key. Keeping
 * that capability on this routeless internal command preserves the consultant journey without
 * re-exposing it to anonymous HTTP callers.
 *
 * It extends {@see RegisterUserSelfserve} so it reuses the same command handler unchanged
 * (the handler type-hints and asserts the parent command type). The organisation is always
 * supplied by the dispatching handler, so it uses the non-optional Organisation field-type trait.
 */
class RegisterUserSelfserveByOrganisation extends RegisterUserSelfserve
{
    use Organisation;
}
