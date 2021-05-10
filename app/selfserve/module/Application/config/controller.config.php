<?php

return [
    'plugins' => [
        'invokables' => [
            'Application' => \Dvsa\Olcs\Application\Controller\OverviewController::class,
            'Application/TypeOfLicence' => \Dvsa\Olcs\Application\Controller\TypeOfLicenceController::class,
            'Application/BusinessType' => \Dvsa\Olcs\Application\Controller\BusinessTypeController::class,
            'Application/BusinessDetails' => \Dvsa\Olcs\Application\Controller\BusinessDetailsController::class,
            'Application/Addresses' => \Dvsa\Olcs\Application\Controller\AddressesController::class,
            'Application/People' => \Dvsa\Olcs\Application\Controller\PeopleController::class,
            'Application/OperatingCentres' => \Dvsa\Olcs\Application\Controller\OperatingCentresController::class,
            'Application/FinancialEvidence' => \Dvsa\Olcs\Application\Controller\FinancialEvidenceController::class,
            'Application/TransportManagers' => \Dvsa\Olcs\Application\Controller\TransportManagersController::class,
            'Application/Vehicles' => \Dvsa\Olcs\Application\Controller\VehiclesController::class,
            'Application/VehiclesPsv' => \Dvsa\Olcs\Application\Controller\VehiclesPsvController::class,
            'Application/Safety' => \Dvsa\Olcs\Application\Controller\SafetyController::class,
            'Application/FinancialHistory' => \Dvsa\Olcs\Application\Controller\FinancialHistoryController::class,
            'Application/LicenceHistory' => \Dvsa\Olcs\Application\Controller\LicenceHistoryController::class,
            'Application/ConvictionsPenalties' => \Dvsa\Olcs\Application\Controller\ConvictionsPenaltiesController::class,
            'Application/Undertakings' => \Dvsa\Olcs\Application\Controller\UndertakingsController::class,
            'Application/TaxiPhv' => \Dvsa\Olcs\Application\Controller\TaxiPhvController::class,
            'Application/VehiclesDeclarations' => \Dvsa\Olcs\Application\Controller\VehiclesDeclarationsController::class,
            'Application/PaymentSubmission' => \Dvsa\Olcs\Application\Controller\PaymentSubmissionController::class,
            'Application/Summary' => \Dvsa\Olcs\Application\Controller\SummaryController::class,
            'Application/UploadEvidence' => \Dvsa\Olcs\Application\Controller\UploadEvidenceController::class,
            'Application/Review' => \Common\Controller\Lva\ReviewController::class,
        ],
        'factories' => [

        ],
        'delegators' => [
            'Application/BusinessType' => [
                // NOTE: we need an associative array when we need to override the
                // delegator elsewhere, such as in selfserve or internal
                'delegator' => \Common\Controller\Lva\Delegators\GenericBusinessTypeDelegator::class,
            ],
            'Application/FinancialEvidence' => [
                Common\Controller\Lva\Delegators\ApplicationFinancialEvidenceDelegator::class,
            ],
            'Application/People' => [
                \Common\Controller\Lva\Delegators\ApplicationPeopleDelegator::class,
            ],
            'Application/TransportManagers' => [
                Common\Controller\Lva\Delegators\ApplicationTransportManagerDelegator::class,
            ],
        ],
    ],
];
