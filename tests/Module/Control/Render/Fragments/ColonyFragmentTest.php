<?php

declare(strict_types=1);

namespace Stu\Module\Control\Render\Fragments;

use Doctrine\Common\Collections\Collection;
use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Twig\TwigPageInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\StuTestCase;

class ColonyFragmentTest extends StuTestCase
{
    private ColonyFragment $subject;

    #[Override]
    protected function setUp(): void
    {

        $this->subject = new ColonyFragment();
    }

    public function testRenderRendersSystemUserWithoutColonies(): void
    {
        $user = $this->mock(UserInterface::class);
        $twigPage = $this->mock(TwigPageInterface::class);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(UserEnum::USER_NOONE);

        $twigPage->shouldReceive('setVar')
            ->with('USER_COLONIES', [])
            ->once();

        $this->subject->render($user, $twigPage, $this->mock(GameControllerInterface::class));
    }

    public function testRenderRendersNormalUserWithColonies(): void
    {
        $user = $this->mock(UserInterface::class);
        $twigPage = $this->mock(TwigPageInterface::class);
        $colonies = $this->mock(Collection::class);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(666);
        $user->shouldReceive('getColonies')
            ->withNoArgs()
            ->once()
            ->andReturn($colonies);

        $twigPage->shouldReceive('setVar')
            ->with('USER_COLONIES', $colonies)
            ->once();

        $this->subject->render($user, $twigPage, $this->mock(GameControllerInterface::class));
    }
}
