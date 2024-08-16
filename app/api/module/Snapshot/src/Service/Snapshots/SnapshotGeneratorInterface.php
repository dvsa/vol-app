<?php

namespace Dvsa\Olcs\Snapshot\Service\Snapshots;

/**
 * SnapshotGeneratorInterface
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
interface SnapshotGeneratorInterface
{
    public function generate();
    public function setData(array $data); //stuck with this for now in order to fit with existing patterns
}
