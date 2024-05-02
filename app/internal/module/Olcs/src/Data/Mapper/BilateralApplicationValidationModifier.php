<?php

namespace Olcs\Data\Mapper;

use Olcs\Service\Permits\Bilateral\ApplicationFormPopulator;

/**
 * BilateralApplicationValidationModifier mapper
 */
class BilateralApplicationValidationModifier
{
    /** @var ApplicationFormPopulator */
    protected $applicationFormPopulator;

    /**
     * Create service instance
     *
     *
     * @return BilateralApplicationValidationModifier
     */
    public function __construct(ApplicationFormPopulator $applicationFormPopulator)
    {
        $this->applicationFormPopulator = $applicationFormPopulator;
    }

    /**
     * Selectively remove form validation so that it doesn't get applied on elements that are hidden at the time of
     * form submission
     *
     *
     * @return array
     */
    public function mapForFormOptions(array $data, mixed $form)
    {
        $this->applicationFormPopulator->populate($form, $data);

        if (isset($data['fields']['countries'])) {
            $countriesInputFilter = $form->getInputFilter()->get('fields')->get('countries');
            $countryInputs = $countriesInputFilter->getInputs();

            $selectedCountryIds = $data['selectedCountryIds'];
            $countries = $data['fields']['countries'];

            foreach ($countries as $countryId => $periodData) {
                if (in_array($countryId, $selectedCountryIds)) {
                    $periodsInputFilter = $countryInputs[$countryId]->get('periods');
                    $periodNameToKeep = 'period' . $periodData['selectedPeriodId'];

                    $periods = $periodData['periods'];
                    foreach ($periods as $periodName => $periodData) {
                        if ($periodName != $periodNameToKeep) {
                            $periodsInputFilter->remove($periodName);
                        }
                    }
                } else {
                    $countriesInputFilter->remove($countryId);
                }
            }
        }

        return $data;
    }
}
