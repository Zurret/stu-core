<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\MoveShip;

use Mockery\MockInterface;
use Override;
use request;
use Stu\ActionControllerTestCase;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Action\ActivateShields\ActivateShields;
use Stu\Module\Ship\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

class ActivateShieldsTest extends ActionControllerTestCase
{
    /** @var MockInterface&ActivatorDeactivatorHelperInterface */
    private MockInterface $helper;

    private ActivateShields $subject;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->helper = $this->mock(ActivatorDeactivatorHelperInterface::class);

        $this->subject = new ActivateShields($this->helper);
    }

    public function testHandle(): void
    {
        request::setMockVars(['id' => 42]);

        $this->game->shouldReceive('setView')
            ->with(ShowShip::VIEW_IDENTIFIER)
            ->once();

        $this->helper->shouldReceive('activate')
            ->with(42, ShipSystemTypeEnum::SYSTEM_SHIELDS, $this->game)
            ->once();

        $this->subject->handle($this->game);
    }
}
