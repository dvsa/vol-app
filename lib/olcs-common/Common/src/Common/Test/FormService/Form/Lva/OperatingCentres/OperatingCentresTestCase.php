<?php

declare(strict_types=1);

namespace Common\Test\FormService\Form\Lva\OperatingCentres;

use Common\RefData;
use Common\Service\Table\TableFactory;
use LmcRbacMvc\Service\AuthorizationService;
use Laminas\Filter\FilterPluginManager;
use Common\Service\Helper\FormHelperService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\TranslationHelperService;
use Laminas\Form\Form;

abstract class OperatingCentresTestCase extends MockeryTestCase
{
    protected const LGV_FIELDSET_NAME = 'totAuthLgvVehiclesFieldset';

    protected const HGV_FIELDSET_LABEL_WITH_VEHICLE_CLASSIFICATIONS_DISABLED = 'application_operating-centres_authorisation.data.totAuthHgvVehiclesFieldset.vehicles-label';

    protected const HGV_FIELDSET_LABEL_WITH_VEHICLE_CLASSIFICATIONS_ENABLED = 'application_operating-centres_authorisation.data.totAuthHgvVehiclesFieldset.hgvs-label';

    protected const HGV_FIELD_LABEL_WITH_VEHICLE_CLASSIFICATIONS_DISABLED = 'application_operating-centres_authorisation.data.totAuthHgvVehicles.vehicles-label';

    protected const HGV_FIELD_LABEL_WITH_VEHICLE_CLASSIFICATIONS_ENABLED = 'application_operating-centres_authorisation.data.totAuthHgvVehicles.hgvs-label';

    protected const HGV_FIELD_NAME = 'totAuthHgvVehicles';

    protected const HGV_FIELDSET_NAME = 'totAuthHgvVehiclesFieldset';

    /** @var  \Common\Service\Helper\AddressHelperService | m\MockInterface */
    protected $mockHlpAddr;

    /** @var  \Common\Service\Helper\DateHelperService | m\MockInterface */
    protected $mockHlpDate;

    /** @var  \Common\Service\Data\AddressDataService| m\MockInterface */
    protected $mockDataAddress;

    protected $formServiceLocator;

    protected $translator;

    protected $urlHelper;

    protected $tableBuilder;

    protected $formHelper;

    protected $authService;

    protected $filterManager;


    /**
     * @return void
     */
    protected function setUpDefaultServices()
    {
        $this->formServiceLocator = m::mock(FormServiceManager::class);
        $this->translator = m::mock(TranslationHelperService::class);
        $this->tableBuilder = m::mock(TableFactory::class);
        $this->authService = m::mock(AuthorizationService::class);
        $this->filterManager = m::mock(FilterPluginManager::class);
        $this->formHelper = m::mock(FormHelperService::class);
    }



    protected function paramsForLicence(): array
    {
        return $this->paramsForHgvLicence();
    }

    protected function paramsForHgvLicence(): array
    {
        return [
            'operatingCentres' => [],
            'canHaveSchedule41' => false,
            'canHaveCommunityLicences' => false,
            'isPsv' => false,
            'vehicleType' => ['id' => RefData::APP_VEHICLE_TYPE_HGV],
        ];
    }

    protected function paramsForMixedLicenceWithoutLgv(): array
    {
        return array_merge(
            $this->paramsForLicence(),
            [
                'vehicleType' => ['id' => RefData::APP_VEHICLE_TYPE_MIXED],
                'totAuthLgvVehicles' => null,
            ]
        );
    }

    protected function paramsForMixedLicenceWithLgv(): array
    {
        return array_merge(
            $this->paramsForMixedLicenceWithoutLgv(),
            [
                'totAuthLgvVehicles' => 0,
            ]
        );
    }

    /**
     * @return array
     */
    protected function paramsForLicenceThatAreEligibleForCommunityLicences()
    {
        return array_merge($this->paramsForLicence(), ['canHaveCommunityLicences' => true]);
    }

    /**
     * @return array
     */
    protected function paramsForLicenceThatAreNotEligibleForCommunityLicences()
    {
        return array_merge($this->paramsForLicence(), ['canHaveCommunityLicences' => false]);
    }

    protected function paramsForGoodsLicence(): array
    {
        return $this->paramsForHgvLicence();
    }

    protected function paramsForPsvLicence(): array
    {
        return array_merge($this->paramsForLicence(), ['isPsv' => true]);
    }

    protected function paramsForPsvLicenceThatAreEligibleForCommunityLicences(): array
    {
        return array_merge($this->paramsForPsvLicence(), ['canHaveCommunityLicences' => true]);
    }

    protected function assertVehicleClassificationsAreDisabledForForm(Form $form): void
    {
        $dataFieldset = $form->get('data');

        $this->assertFalse($dataFieldset->has(static::LGV_FIELDSET_NAME), 'Expected LGV fieldset to have been removed from the form');

        $hgvFieldset = $dataFieldset->get(static::HGV_FIELDSET_NAME);
        $this->assertSame(static::HGV_FIELDSET_LABEL_WITH_VEHICLE_CLASSIFICATIONS_DISABLED, $hgvFieldset->getLabel());

        $hgvField = $hgvFieldset->get(static::HGV_FIELD_NAME);
        $this->assertSame(static::HGV_FIELD_LABEL_WITH_VEHICLE_CLASSIFICATIONS_DISABLED, $hgvField->getLabel());
    }

    protected function assertVehicleClassificationsAreEnabledForForm(Form $form): void
    {
        $dataFieldset = $form->get('data');

        $this->assertTrue($dataFieldset->has(static::LGV_FIELDSET_NAME), 'Expected LGV fieldset to exist in the form');

        $hgvFieldset = $dataFieldset->get(static::HGV_FIELDSET_NAME);
        $this->assertSame(static::HGV_FIELDSET_LABEL_WITH_VEHICLE_CLASSIFICATIONS_ENABLED, $hgvFieldset->getLabel());

        $hgvField = $hgvFieldset->get(static::HGV_FIELD_NAME);
        $this->assertSame(static::HGV_FIELD_LABEL_WITH_VEHICLE_CLASSIFICATIONS_ENABLED, $hgvField->getLabel());
    }
}
