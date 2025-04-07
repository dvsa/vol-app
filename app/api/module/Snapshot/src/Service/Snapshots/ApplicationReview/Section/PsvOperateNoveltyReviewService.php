<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Dvsa\Olcs\Api\Entity\Application\Application;

class PsvOperateNoveltyReviewService extends AbstractReviewService
{
    public function getConfigFromData(array $data = []): array
    {
        $multiItems['15f'][] = $this->addSection15f1($data);

        if ($data['psvLimousines'] === 'Y') {
            if ($data['psvWhichVehicleSizes']['id'] !== Application::PSV_VEHICLE_SIZE_SMALL) {
                $multiItems['15g'][] = $this->addSection15g();
            }
        } else {
            $multiItems['15f'][] = $this->addSection15f2();
        }

        return [
            'multiItems' => $multiItems,
        ];
    }

    protected function addSection15f1($data): array
    {
        return [
            'label' => 'application-review-vehicles-declarations-15f1',
            'value' => $this->formatYesNo($data['psvLimousines'])
        ];
    }

    protected function addSection15f2(): array
    {
        return [
            'label' => 'application-review-vehicles-declarations-15f2',
            'value' => $this->formatConfirmed('Y')
        ];
    }

    protected function addSection15g(): array
    {
        return [
            'label' => 'application-review-vehicles-declarations-15g',
            'value' => $this->formatConfirmed('Y')
        ];
    }
}
