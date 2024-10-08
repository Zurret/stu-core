<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Override;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemPriorities;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;

abstract class AbstractShipSystemType implements ShipSystemTypeInterface
{
    protected ShipInterface $ship;

    #[Override]
    public function setShip(ShipInterface $ship): void
    {
        $this->ship = $ship;
    }
    /**
     * updates the system metadata for this specific ship system
     */
    protected function updateSystemData(
        ShipSystemTypeEnum $systemType,
        $data,
        ShipSystemRepositoryInterface $shipSystemRepository
    ): void {
        $system = $this->ship->getShipSystem($systemType);
        $system->setData(json_encode($data, JSON_THROW_ON_ERROR));
        $shipSystemRepository->save($system);
    }

    abstract public function getSystemType(): ShipSystemTypeEnum;

    #[Override]
    public function activate(ShipWrapperInterface $wrapper, ShipSystemManagerInterface $manager): void
    {
        $wrapper->get()->getShipSystem($this->getSystemType())->setMode(ShipSystemModeEnum::MODE_ON);
    }

    #[Override]
    public function deactivate(ShipWrapperInterface $wrapper): void
    {
        $wrapper->get()->getShipSystem($this->getSystemType())->setMode(ShipSystemModeEnum::MODE_OFF);
    }

    #[Override]
    public function checkActivationConditions(ShipWrapperInterface $wrapper, string &$reason): bool
    {
        return true;
    }

    #[Override]
    public function checkDeactivationConditions(ShipWrapperInterface $wrapper, string &$reason): bool
    {
        return true;
    }

    #[Override]
    public function getEnergyUsageForActivation(): int
    {
        return 1;
    }

    #[Override]
    public function getPriority(): int
    {
        if (array_key_exists($this->getSystemType()->value, ShipSystemPriorities::PRIORITIES)) {
            return ShipSystemPriorities::PRIORITIES[$this->getSystemType()->value];
        }

        return ShipSystemPriorities::PRIORITY_STANDARD;
    }

    #[Override]
    public function getEnergyConsumption(): int
    {
        return 1;
    }

    #[Override]
    public function handleDestruction(ShipWrapperInterface $wrapper): void
    {
        //nothing to do here
    }

    #[Override]
    public function handleDamage(ShipWrapperInterface $wrapper): void
    {
        //nothing to do here
    }

    #[Override]
    public function getDefaultMode(): int
    {
        return ShipSystemModeEnum::MODE_OFF;
    }

    #[Override]
    public function getCooldownSeconds(): ?int
    {
        return null;
    }
}
