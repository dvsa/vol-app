<?php

namespace Dvsa\Olcs\Api\Service\Permits\Bilateral\Metadata;

use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitStock as IrhpPermitStockRepository;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use RuntimeException;

class PeriodGenerator
{
    /** @var array */
    private $fieldsGenerators = [];

    /**
     * Create service instance
     *
     *
     * @return PeriodGenerator
     */
    public function __construct(private readonly IrhpPermitStockRepository $irhpPermitStockRepo)
    {
    }

    /**
     * Generate the period part of the response
     *
     * @param int $stockId
     * @param string $behaviour
     * @param IrhpPermitApplication $irhpPermitApplication (optional)
     *
     * @return array
     */
    public function generate($stockId, $behaviour, ?IrhpPermitApplication $irhpPermitApplication)
    {
        if (!isset($this->fieldsGenerators[$behaviour])) {
            throw new RuntimeException('No fields generator found for behaviour name ' . $behaviour);
        }

        $fieldsGenerator = $this->fieldsGenerators[$behaviour];
        $irhpPermitStock = $this->irhpPermitStockRepo->fetchById($stockId);

        return [
            'id' => $stockId,
            'key' => $irhpPermitStock->getPeriodNameKey(),
            'fields' => $fieldsGenerator->generate($irhpPermitStock, $irhpPermitApplication)
        ];
    }

    /**
     * Associate an instance of FieldsGeneratorInterface with a country behaviour
     *
     * @param string $behaviour
     * @param FieldsGeneratorInterface $fieldsGeneratorInterface
     */
    public function registerFieldsGenerator($behaviour, FieldsGeneratorInterface $fieldsGenerator)
    {
        $this->fieldsGenerators[$behaviour] = $fieldsGenerator;
    }
}
