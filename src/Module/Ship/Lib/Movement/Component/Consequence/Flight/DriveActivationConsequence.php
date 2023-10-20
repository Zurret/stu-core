<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component\Consequence\Flight;

use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Lib\Message\Message;
use Stu\Module\Ship\Lib\Message\MessageCollectionInterface;
use Stu\Module\Ship\Lib\Message\MessageInterface;
use Stu\Module\Ship\Lib\Movement\Component\Consequence\AbstractFlightConsequence;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

class DriveActivationConsequence extends AbstractFlightConsequence
{
    private ShipSystemManagerInterface $shipSystemManager;

    public function __construct(ShipSystemManagerInterface $shipSystemManager)
    {
        $this->shipSystemManager = $shipSystemManager;
    }

    protected function triggerSpecific(
        ShipWrapperInterface $wrapper,
        FlightRouteInterface $flightRoute,
        MessageCollectionInterface $messages
    ): void {
        if ($wrapper->get()->isTractored()) {
            return;
        }

        $message = new Message();
        $messages->add($message);

        if ($flightRoute->isImpulseDriveNeeded()) {
            $this->activate(
                $wrapper,
                ShipSystemTypeEnum::SYSTEM_IMPULSEDRIVE,
                $message
            );
        }

        if ($flightRoute->isWarpDriveNeeded()) {
            $this->activate(
                $wrapper,
                ShipSystemTypeEnum::SYSTEM_WARPDRIVE,
                $message
            );
        }

        if ($flightRoute->isTranswarpCoilNeeded()) {
            $this->activate(
                $wrapper,
                ShipSystemTypeEnum::SYSTEM_TRANSWARP_COIL,
                $message
            );
        }
    }

    private function activate(
        ShipWrapperInterface $wrapper,
        ShipSystemTypeEnum $systemId,
        MessageInterface $message
    ): void {
        $ship = $wrapper->get();

        if (!$ship->getSystemState($systemId)) {
            $this->shipSystemManager->activate($wrapper, $systemId);

            $message->add(sprintf(
                _('Die %s aktiviert %s %s'),
                $ship->getName(),
                $systemId === ShipSystemTypeEnum::SYSTEM_TRANSWARP_COIL ? 'die' : 'den',
                ShipSystemTypeEnum::getDescription($systemId)
            ));
        }
    }
}
