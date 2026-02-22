<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Traits;

/**
 * @see \Dvsa\Olcs\Api\Entity\Traits\TotAuthVehiclesTrait
 */
trait TotAuthVehiclesTraitTestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function updateTotAuthHgvVehiclesIsCallable(): void
    {
        // Assert
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'updateTotAuthHgvVehicles']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function updateTotAuthHgvVehiclesReturnsSelf(): void
    {
        // Assert
        $this->setUpSut();
        $aNumberOfVehicles = 2;

        // Execute
        $result = $this->sut->updateTotAuthHgvVehicles($aNumberOfVehicles);

        // Assert
        $this->assertSame($this->sut, $result);
    }

    public static function validTotAuthHgvVehiclesCountsDataProvider(): array
    {
        return [
            'zero' => [0],
            'positive integer' => [1],
            'null' => [null],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('validTotAuthHgvVehiclesCountsDataProvider')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function updateTotAuthHgvVehiclesSetsTotAuthHgvVehicles(mixed $count): void
    {
        // Assert
        $this->setUpSut();

        // Execute
        $this->sut->updateTotAuthHgvVehicles($count);

        // Assert
        $this->assertSame($count, $this->sut->getTotAuthHgvVehicles());
    }

    public static function invalidTotAuthHgvVehiclesCountsDataProvider(): array
    {
        return [
            'zero string' => ['0'],
            'positive integer string' => ['1'],
            'empty string' => [''],
            'empty array' => [[]],
        ];
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function updateTotAuthHgvVehiclesSetsTotAuthVehiclesToTheTotalOfLgvsAndHgvs(): void
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
    public function updateTotAuthLgvVehiclesIsCallable(): void
    {
        // Assert
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'updateTotAuthLgvVehicles']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function updateTotAuthLgvVehiclesReturnsSelf(): void
    {
        // Assert
        $this->setUpSut();
        $aNumberOfVehicles = 2;

        // Execute
        $result = $this->sut->updateTotAuthLgvVehicles($aNumberOfVehicles);

        // Assert
        $this->assertSame($this->sut, $result);
    }

    public static function validTotAuthLgvVehiclesCountsDataProvider(): array
    {
        return [
            'zero' => [0],
            'positive integer' => [1],
            'null' => [null],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('validTotAuthLgvVehiclesCountsDataProvider')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function updateTotAuthLgvVehiclesSetsTotAuthHgvVehicles(mixed $count): void
    {
        // Assert
        $this->setUpSut();

        // Execute
        $this->sut->updateTotAuthLgvVehicles($count);

        // Assert
        $this->assertSame($count, $this->sut->getTotAuthLgvVehicles());
    }

    public static function invalidTotAuthLgvVehiclesCountsDataProvider(): array
    {
        return [
            'zero string' => ['0'],
            'positive integer string' => ['1'],
            'empty string' => [''],
            'empty array' => [[]],
        ];
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function updateTotAuthLgvVehiclesSetsTotAuthVehiclesToTheTotalOfLgvsAndHgvs(): void
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
