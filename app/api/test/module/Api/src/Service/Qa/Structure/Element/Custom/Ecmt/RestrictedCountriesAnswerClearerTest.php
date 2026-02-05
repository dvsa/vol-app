<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Ecmt;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepository;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as IrhpApplicationEntity;
use Dvsa\Olcs\Api\Service\Qa\Common\ArrayCollectionFactory;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt\RestrictedCountriesAnswerClearer;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\GenericAnswerClearer;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * RestrictedCountriesAnswerClearerTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class RestrictedCountriesAnswerClearerTest extends MockeryTestCase
{
    public function testClear(): void
    {
        $emptyArrayCollection = m::mock(ArrayCollection::class);

        $irhpApplication = m::mock(IrhpApplicationEntity::class);
        $irhpApplication->shouldReceive('updateCountries')
            ->with($emptyArrayCollection)
            ->once()
            ->globally()
            ->ordered();

        $qaContext = m::mock(QaContext::class);
        $qaContext->shouldReceive('getQaEntity')
            ->withNoArgs()
            ->andReturn($irhpApplication);

        $genericAnswerClearer = m::mock(GenericAnswerClearer::class);
        $genericAnswerClearer->shouldReceive('clear')
            ->with($qaContext)
            ->once();

        $irhpApplicationRepo = m::mock(IrhpApplicationRepository::class);
        $irhpApplicationRepo->shouldReceive('save')
            ->with($irhpApplication)
            ->once()
            ->globally()
            ->ordered();

        $arrayCollectionFactory = m::mock(ArrayCollectionFactory::class);
        $arrayCollectionFactory->shouldReceive('create')
            ->withNoArgs()
            ->andReturn($emptyArrayCollection);

        $restrictedCountriesAnswerClearer = new RestrictedCountriesAnswerClearer(
            $genericAnswerClearer,
            $irhpApplicationRepo,
            $arrayCollectionFactory
        );

        $restrictedCountriesAnswerClearer->clear($qaContext);
    }
}
