<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Traits;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\TransportManagerApplication\Snapshot;

trait TransportManagerSnapshot
{
    /**
     * Create snapshot
     *
     * @param int $tmaId tma id
     * @param int $user  transport manager id
     *
     * @return Result
     */
    protected function createSnapshot($tmaId, $user): Result
    {
        $data = [
            'id' => $tmaId,
            'user' => $user
        ];

        return $this->handleSideEffect(Snapshot::create($data));
    }
}
