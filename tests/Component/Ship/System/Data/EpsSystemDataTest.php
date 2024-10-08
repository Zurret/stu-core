<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Data;

use Mockery\MockInterface;
use Override;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Template\StatusBarFactoryInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;
use Stu\StuTestCase;

class EpsSystemDataTest extends StuTestCase
{
    /** @var MockInterface|ShipSystemRepositoryInterface */
    private $shipSystemRepository;
    /** @var MockInterface|StatusBarFactoryInterface */
    private $statusBarFactory;

    /**
     * @var MockInterface|ShipInterface
     */
    private $ship;

    private EpsSystemData $subject;

    #[Override]
    public function setUp(): void
    {
        $this->shipSystemRepository = $this->mock(ShipSystemRepositoryInterface::class);
        $this->statusBarFactory = $this->mock(StatusBarFactoryInterface::class);
        $this->ship = $this->mock(ShipInterface::class);

        $this->subject = new EpsSystemData(
            $this->shipSystemRepository,
            $this->statusBarFactory
        );
    }

    public function testGetEpsPercentage(): void
    {
        $this->subject->setShip($this->ship);
        $this->subject->setEps(80);
        $this->subject->setMaxEps(100);

        $this->ship->shouldReceive('hasShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_EPS)
            ->andReturn(false);

        $result = $this->subject->getEpsPercentage();

        $this->assertEquals(80, $result);
    }
}
