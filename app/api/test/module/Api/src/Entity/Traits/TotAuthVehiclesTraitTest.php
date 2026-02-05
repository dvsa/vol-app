<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Traits;

/**
 * @see \Dvsa\Olcs\Api\Entity\Traits\TotAuthVehiclesTrait
 */
trait TotAuthVehiclesTraitTest
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function updateTotAuthHgvVehiclesIsCallable()
    {
        // Assert
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'updateTotAuthHgvVehicles']);
    }

    /**
     * @depends updateTotAuthHgvVehiclesIsCallable
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function updateTotAuthHgvVehiclesReturnsSelf()
    {
        // Assert
        $this->setUpSut();
        $aNumberOfVehicles = 2;

        // Execute
        $result = $this->sut->updateTotAuthHgvVehicles($aNumberOfVehicles);

        // Assert
        $this->assertSame($this->sut, $result);
    }

    /**
     * @return array
     */
    public function validTotAuthHgvVehiclesCountsDataProvider(): array
    {
        return [
            'zero' => [0],
            'positive integer' => [1],
            'null' => [null],
        ];
    }

    /**
     * @depends updateTotAuthHgvVehiclesIsCallable
     * @dataProvider validTotAuthHgvVehiclesCountsDataProvider
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function updateTotAuthHgvVehiclesSetsTotAuthHgvVehicles(mixed $count)
    {
        // Assert
        $this->setUpSut();

        // Execute
        $this->sut->updateTotAuthHgvVehicles($count);

        // Assert
        $this->assertSame($count, $this->sut->getTotAuthHgvVehicles());
    }

    /**
     * @return array
     */
    public function invalidTotAuthHgvVehiclesCountsDataProvider(): array
    {
        return [
            'zero string' => ['0'],
            'positive integer string' => ['1'],
            'empty string' => [''],
            'empty array' => [[]],
        ];
    }

    /**
     * @depends updateTotAuthHgvVehiclesIsCallable
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function updateTotAuthHgvVehiclesSetsTotAuthVehiclesToTheTotalOfLgvsAndHgvs()
    {
        // Assert
        $this->setUpSut();
        $aNumberOfVehicles = 2;
        $expectedNumber = $aNumberOfVehicles + $aNumberOfVehicles;

        // Execute
        $this->sut->setTotAuthLgvVehicles($aNumberOfVehicles);
        $this->sut->updateTotAuthHgvVehicles($aNumberOfVehicles);

        // Assert
        $this->assertSame($expectedNumber, $this->sut->getTotAuthVehicles());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function updateTotAuthLgvVehiclesIsCallable()
    {
        // Assert
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'updateTotAuthLgvVehicles']);
    }

    /**
     * @depends updateTotAuthLgvVehiclesIsCallable
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function updateTotAuthLgvVehiclesReturnsSelf()
    {
        // Assert
        $this->setUpSut();
        $aNumberOfVehicles = 2;

        // Execute
        $result = $this->sut->updateTotAuthLgvVehicles($aNumberOfVehicles);

        // Assert
        $this->assertSame($this->sut, $result);
    }

    /**
     * @return array
     */
    public function validTotAuthLgvVehiclesCountsDataProvider(): array
    {
        return [
            'zero' => [0],
            'positive integer' => [1],
            'null' => [null],
        ];
    }

    /**
     * @depends updateTotAuthLgvVehiclesIsCallable
     * @dataProvider validTotAuthLgvVehiclesCountsDataProvider
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function updateTotAuthLgvVehiclesSetsTotAuthHgvVehicles(mixed $count)
    {
        // Assert
        $this->setUpSut();

        // Execute
        $this->sut->updateTotAuthLgvVehicles($count);

        // Assert
        $this->assertSame($count, $this->sut->getTotAuthLgvVehicles());
    }

    /**
     * @return array
     */
    public function invalidTotAuthLgvVehiclesCountsDataProvider(): array
    {
        return [
            'zero string' => ['0'],
            'positive integer string' => ['1'],
            'empty string' => [''],
            'empty array' => [[]],
        ];
    }

    /**
     * @depends updateTotAuthLgvVehiclesIsCallable
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function updateTotAuthLgvVehiclesSetsTotAuthVehiclesToTheTotalOfLgvsAndHgvs()
    {
        // Assert
        $this->setUpSut();
        $aNumberOfVehicles = 2;
        $expectedNumber = $aNumberOfVehicles + $aNumberOfVehicles;

        // Execute
        $this->sut->setTotAuthHgvVehicles($aNumberOfVehicles);
        $this->sut->updateTotAuthLgvVehicles($aNumberOfVehicles);

        // Assert
        $this->assertSame($expectedNumber, $this->sut->getTotAuthVehicles());
    }
}
