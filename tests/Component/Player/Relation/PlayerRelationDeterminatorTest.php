<?php

declare(strict_types=1);

namespace Stu\Component\Player\Relation;

use Mockery\MockInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\StuTestCase;

class PlayerRelationDeterminatorTest extends StuTestCase
{
    /** @var MockInterface&FriendDeterminator */
    private $friendDeterminator;

    /** @var MockInterface&EnemyDeterminator */
    private $enemyDeterminator;

    private PlayerRelationDeterminator $subject;

    /** @var MockInterface&UserInterface */
    private MockInterface $user;

    /** @var MockInterface&UserInterface */
    private MockInterface $opponent;

    protected function setUp(): void
    {
        $this->friendDeterminator = $this->mock(FriendDeterminator::class);
        $this->enemyDeterminator = $this->mock(EnemyDeterminator::class);

        $this->subject = new PlayerRelationDeterminator(
            $this->friendDeterminator,
            $this->enemyDeterminator
        );

        $this->user = $this->mock(UserInterface::class);
        $this->opponent = $this->mock(UserInterface::class);
    }

    public static function provideIsFriendData()
    {
        return [
            [PlayerRelationTypeEnum::NONE, PlayerRelationTypeEnum::NONE, false],
            [PlayerRelationTypeEnum::NONE, PlayerRelationTypeEnum::USER, false],
            [PlayerRelationTypeEnum::NONE, PlayerRelationTypeEnum::ALLY, false],
            [PlayerRelationTypeEnum::USER, PlayerRelationTypeEnum::NONE, true],
            [PlayerRelationTypeEnum::USER, PlayerRelationTypeEnum::ALLY, true],
            [PlayerRelationTypeEnum::ALLY, PlayerRelationTypeEnum::NONE, true],
            [PlayerRelationTypeEnum::ALLY, PlayerRelationTypeEnum::USER, false]
        ];
    }

    /**
     * @dataProvider provideIsFriendData
     */
    public function testIsFriend(
        PlayerRelationTypeEnum $friendRelation,
        PlayerRelationTypeEnum $enemyRelation,
        bool $expectedResult
    ): void {
        $this->friendDeterminator->shouldReceive('isFriend')
            ->with($this->user, $this->opponent)
            ->zeroOrMoreTimes()
            ->andReturn($friendRelation);
        $this->enemyDeterminator->shouldReceive('isEnemy')
            ->with($this->user, $this->opponent)
            ->zeroOrMoreTimes()
            ->andReturn($enemyRelation);

        $this->assertEquals(
            $expectedResult,
            $this->subject->isFriend($this->user, $this->opponent)
        );
    }

    public static function provideIsEnemyData()
    {
        return [
            [PlayerRelationTypeEnum::NONE, PlayerRelationTypeEnum::NONE, false],
            [PlayerRelationTypeEnum::NONE, PlayerRelationTypeEnum::USER, true],
            [PlayerRelationTypeEnum::NONE, PlayerRelationTypeEnum::ALLY, true],
            [PlayerRelationTypeEnum::USER, PlayerRelationTypeEnum::NONE, false],
            [PlayerRelationTypeEnum::USER, PlayerRelationTypeEnum::ALLY, false],
            [PlayerRelationTypeEnum::ALLY, PlayerRelationTypeEnum::NONE, false],
            [PlayerRelationTypeEnum::ALLY, PlayerRelationTypeEnum::USER, true]
        ];
    }

    /**
     * @dataProvider provideIsEnemyData
     */
    public function testIsEnemy(
        PlayerRelationTypeEnum $friendRelation,
        PlayerRelationTypeEnum $enemyRelation,
        bool $expectedResult
    ): void {
        $this->friendDeterminator->shouldReceive('isFriend')
            ->with($this->user, $this->opponent)
            ->zeroOrMoreTimes()
            ->andReturn($friendRelation);
        $this->enemyDeterminator->shouldReceive('isEnemy')
            ->with($this->user, $this->opponent)
            ->zeroOrMoreTimes()
            ->andReturn($enemyRelation);

        $this->assertEquals(
            $expectedResult,
            $this->subject->isEnemy($this->user, $this->opponent)
        );
    }
}
