<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Damage;

use Stu\Lib\DamageWrapper;
use Stu\Module\Ship\Lib\Damage\ApplyDamageInterface;
use Stu\Module\Ship\Lib\Destruction\ShipDestructionCauseEnum;
use Stu\Module\Ship\Lib\Destruction\ShipDestructionInterface;
use Stu\Module\Ship\Lib\Message\Message;
use Stu\Module\Ship\Lib\Message\MessageCollectionInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

final class ApplyFieldDamage implements ApplyFieldDamageInterface
{
    public function __construct(
        private ApplyDamageInterface $applyDamage,
        private ShipDestructionInterface $shipDestruction
    ) {
    }

    public function damage(
        ShipWrapperInterface $wrapper,
        int $damage,
        bool $isAbsolutDmg,
        string $cause,
        MessageCollectionInterface $messages
    ): void {

        //ship itself
        $this->damageShip(
            $wrapper,
            $damage,
            $isAbsolutDmg,
            $cause,
            $messages
        );

        //tractored ship
        $tractoredShipWrapper = $wrapper->getTractoredShipWrapper();
        if ($tractoredShipWrapper !== null) {
            $this->damageShip(
                $tractoredShipWrapper,
                $damage,
                $isAbsolutDmg,
                $cause,
                $messages
            );
        }
    }

    private function damageShip(
        ShipWrapperInterface $wrapper,
        int $damage,
        bool $isAbsolutDmg,
        string $cause,
        MessageCollectionInterface $messages
    ): void {
        $ship = $wrapper->get();

        $message = new Message(null, $ship->getUser()->getId());
        $messages->add($message);

        $shipName = $ship->getName();

        $dmg = $isAbsolutDmg ? $damage : $ship->getMaxHull() * $damage / 100;

        $message->add(sprintf(
            _('%s: Die %s wurde in Sektor %d|%d beschädigt'),
            $cause,
            $shipName,
            $ship->getPosX(),
            $ship->getPosY()
        ));
        $message->addMessageMerge($this->applyDamage->damage(
            new DamageWrapper((int) ceil($dmg)),
            $wrapper
        )->getInformations());

        if ($ship->isDestroyed()) {

            $this->shipDestruction->destroy(
                null,
                $wrapper,
                ShipDestructionCauseEnum::FIELD_DAMAGE,
                $message
            );
        }
    }
}
