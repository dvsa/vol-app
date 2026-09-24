<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Integration\Schema;

use Dvsa\OlcsTest\Integration\IntegrationTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\Bus\BusReg;
use Dvsa\Olcs\Api\Entity\Bus\LocalAuthority;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea;

final class BusSubsidySchemaTest extends IntegrationTestCase
{
    public function testSubsidyAssociationsHaveSeparateAuditedStorage(): void
    {
        $schema = $this->em()->getConnection()->createSchemaManager();
        $this->assertTrue($schema->tablesExist([
            'bus_reg_subsidy_traffic_area',
            'bus_reg_subsidy_traffic_area_hist',
            'bus_reg_subsidy_local_auth',
            'bus_reg_subsidy_local_auth_hist',
        ]));
    }
    public function testClearingProvidersIsAudited(): void
    {
        $db = $this->em()->getConnection();
        // Existing seeded records avoid assumptions about unrelated mandatory columns.
        $busId = $db->fetchOne('SELECT id FROM bus_reg LIMIT 1');
        $authorityId = $db->fetchOne('SELECT id FROM local_authority LIMIT 1');
        $this->assertNotFalse($busId, 'A bus registration fixture is required');
        $this->assertNotFalse($authorityId, 'A local authority fixture is required');
        $db->executeStatement('DELETE FROM bus_reg_subsidy_local_auth WHERE bus_reg_id = ?', [$busId]);
        $before = (int) $db->fetchOne('SELECT COUNT(*) FROM bus_reg_subsidy_local_auth_hist');
        $db->insert('bus_reg_subsidy_local_auth', ['bus_reg_id' => $busId, 'local_authority_id' => $authorityId]);
        $db->delete('bus_reg_subsidy_local_auth', ['bus_reg_id' => $busId, 'local_authority_id' => $authorityId]);
        $this->assertSame($before + 2, (int) $db->fetchOne('SELECT COUNT(*) FROM bus_reg_subsidy_local_auth_hist'));
    }
    public function testSelectionsRoundTripAndClearWithoutChangingLegacyFields(): void
    {
        $db = $this->em()->getConnection();
        $before = $db->fetchAssociative('SELECT id, subsidised, subsidy_detail FROM bus_reg LIMIT 1');
        $authorityId = (int) $db->fetchOne('SELECT id FROM local_authority LIMIT 1');
        $areaId = $db->fetchOne('SELECT traffic_area_id FROM local_authority WHERE id = ?', [$authorityId]);
        $busReg = $this->em()->find(BusReg::class, $before['id']);
        $busReg->setSubsidyTrafficAreas(new ArrayCollection([$this->em()->find(TrafficArea::class, $areaId)]));
        $busReg->setSubsidyLocalAuthorities(new ArrayCollection([$this->em()->find(LocalAuthority::class, $authorityId)]));
        $this->em()->flush();
        $this->em()->clear();

        $reloaded = $this->em()->find(BusReg::class, $before['id']);
        $this->assertSame([$areaId], $reloaded->getSubsidyTrafficAreas()->map(static fn ($area) => $area->getId())->toArray());
        $this->assertSame([$authorityId], $reloaded->getSubsidyLocalAuthorities()->map(static fn ($la) => $la->getId())->toArray());
        $reloaded->setSubsidyTrafficAreas(new ArrayCollection());
        $reloaded->setSubsidyLocalAuthorities(new ArrayCollection());
        $this->em()->flush();
        $this->em()->clear();

        $cleared = $this->em()->find(BusReg::class, $before['id']);
        $this->assertCount(0, $cleared->getSubsidyTrafficAreas());
        $this->assertCount(0, $cleared->getSubsidyLocalAuthorities());
        $this->assertSame($before, $db->fetchAssociative(
            'SELECT id, subsidised, subsidy_detail FROM bus_reg WHERE id = ?',
            [$before['id']]
        ));
    }

    public function testLongSubsidyCommentsRoundTripInRegistrationAndHistory(): void
    {
        $db = $this->em()->getConnection();
        $comments = str_repeat('é', 499) . "\n" . str_repeat('b', 500);
        $busId = $db->fetchOne('SELECT id FROM bus_reg LIMIT 1');
        $this->assertNotFalse($busId, 'A bus registration fixture is required');

        foreach (['bus_reg', 'bus_reg_hist'] as $table) {
            $columns = $db->createSchemaManager()->listTableColumns($table);
            $this->assertSame(1000, $columns['subsidy_detail']->getLength());
        }

        $busReg = $this->em()->find(BusReg::class, $busId);
        $busReg->setSubsidyDetail($comments);
        $this->em()->flush();
        $this->em()->clear();
        $this->assertSame($comments, $this->em()->find(BusReg::class, $busId)->getSubsidyDetail());

        $db->insert('bus_reg_hist', ['id' => $busId, 'subsidy_detail' => $comments]);
        $this->assertSame($comments, $db->fetchOne(
            'SELECT subsidy_detail FROM bus_reg_hist WHERE hist_id = ?',
            [$db->lastInsertId()]
        ));
    }
}
