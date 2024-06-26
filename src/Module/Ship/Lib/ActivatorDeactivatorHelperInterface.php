<?php

namespace Stu\Module\Ship\Lib;

use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\Movement\Component\PreFlight\ConditionCheckResult;

interface ActivatorDeactivatorHelperInterface
{
    public function activate(
        ShipWrapperInterface|int $shipId,
        ShipSystemTypeEnum $type,
        ConditionCheckResult|InformationInterface $logger,
        bool $allowUplink = false,
        bool $isDryRun = false
    ): bool;

    public function activateFleet(
        int $shipId,
        ShipSystemTypeEnum $systemId,
        GameControllerInterface $game
    ): void;

    public function deactivate(
        ShipWrapperInterface|int $shipId,
        shipSystemTypeEnum $type,
        InformationInterface $informations,
        bool $allowUplink = false
    ): bool;

    public function deactivateFleet(
        ShipWrapperInterface|int $target,
        shipSystemTypeEnum $type,
        InformationInterface $informations
    ): bool;

    public function setLSSMode(
        int $shipId,
        int $lssMode,
        GameControllerInterface $game
    ): void;

    public function setAlertState(
        ShipWrapperInterface|int $shipId,
        ShipAlertStateEnum $alertState,
        GameControllerInterface $game
    ): void;

    public function setAlertStateFleet(
        int $shipId,
        ShipAlertStateEnum $alertState,
        GameControllerInterface $game
    ): void;
}
