<?php

declare(strict_types=1);

namespace Stu\Component\Anomaly\Type;

use Stu\Component\Anomaly\AnomalyCreationInterface;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Control\StuRandom;
use Stu\Module\Message\Lib\DistributedMessageSenderInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Ship\Lib\Damage\ApplyDamageInterface;
use Stu\Module\Ship\Lib\Message\Message;
use Stu\Module\Ship\Lib\Message\MessageCollection;
use Stu\Module\Ship\Lib\Message\MessageCollectionInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Orm\Entity\AnomalyInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;

//TODO unit tests
final class SubspaceEllipseHandler implements AnomalyHandlerInterface
{
    public const MASS_CALCULATION_THRESHOLD = 33_333_333;

    private MapRepositoryInterface $mapRepository;

    private StarSystemMapRepositoryInterface $starSystemMapRepository;

    private AnomalyCreationInterface $anomalyCreation;

    private ShipRepositoryInterface $shipRepository;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    private ApplyDamageInterface $applyDamage;

    private PrivateMessageSenderInterface $privateMessageSender;

    private DistributedMessageSenderInterface $distributedMessageSender;

    private StuRandom $stuRandom;

    public function __construct(
        MapRepositoryInterface $mapRepository,
        StarSystemMapRepositoryInterface $starSystemMapRepository,
        AnomalyCreationInterface $anomalyCreation,
        ShipRepositoryInterface $shipRepository,
        ShipWrapperFactoryInterface $shipWrapperFactory,
        ApplyDamageInterface $applyDamage,
        PrivateMessageSenderInterface $privateMessageSender,
        DistributedMessageSenderInterface $distributedMessageSender,
        StuRandom $stuRandom
    ) {
        $this->mapRepository = $mapRepository;
        $this->starSystemMapRepository = $starSystemMapRepository;
        $this->anomalyCreation = $anomalyCreation;
        $this->shipRepository = $shipRepository;
        $this->shipWrapperFactory = $shipWrapperFactory;
        $this->applyDamage = $applyDamage;
        $this->privateMessageSender = $privateMessageSender;
        $this->distributedMessageSender = $distributedMessageSender;
        $this->stuRandom = $stuRandom;
    }

    public function checkForCreation(): void
    {
        $subspaceEllipses = [];

        foreach ($this->mapRepository->getForSubspaceEllipseCreation() as $map) {
            $subspaceEllipses[] = $this->anomalyCreation->create(
                AnomalyTypeEnum::SUBSPACE_ELLIPSE,
                $map
            );
        }
        foreach ($this->starSystemMapRepository->getForSubspaceEllipseCreation() as $starsystemMap) {
            $subspaceEllipses[] = $this->anomalyCreation->create(
                AnomalyTypeEnum::SUBSPACE_ELLIPSE,
                $starsystemMap
            );
        }

        foreach ($subspaceEllipses as $anomaly) {
            $this->informSpacecraftOwnersAboutCreation($anomaly);
        }
    }

    public function handleShipTick(AnomalyInterface $anomaly): void
    {
        $location = $anomaly->getLocation();
        $spacecrafts = $location->getShips();

        $messagesForShips = new MessageCollection();
        $messagesForBases = new MessageCollection();

        $intro = new Message(
            UserEnum::USER_NOONE,
            null
        );

        $messagesForShips->add($intro);
        $messagesForBases->add($intro);

        foreach ($spacecrafts as $spacecraft) {

            if ($spacecraft->getUser()->isVacationRequestOldEnough()) {
                continue;
            }

            if (!$spacecraft->hasShipSystem(ShipSystemTypeEnum::SYSTEM_SHIELDS)) {
                continue;
            }

            $shieldSystem = $spacecraft->getShipSystem(ShipSystemTypeEnum::SYSTEM_SHIELDS);

            if (
                $spacecraft->getShield() === 0
                && $shieldSystem->getStatus() === 0
            ) {
                continue;
            }

            $message = new Message(UserEnum::USER_NOONE, $spacecraft->getUser()->getId());
            $message->add($spacecraft->getName());

            if ($shieldSystem->getMode() > ShipSystemModeEnum::MODE_OFF) {
                $shieldSystem->setMode(ShipSystemModeEnum::MODE_OFF);
            }

            if ($spacecraft->getShield() > 0) {
                $spacecraft->setShield(0);
                $message->add('- die Schilde wurden entladen');
            }

            if ($shieldSystem->getStatus() > 0) {
                $wrapper = $this->shipWrapperFactory->wrapShip($spacecraft);

                $informations = new InformationWrapper();
                $this->applyDamage->damageShipSystem(
                    $wrapper,
                    $shieldSystem,
                    $this->stuRandom->rand(1, 50, true),
                    $informations
                );
                $message->addMessageMerge($informations->getInformations());
            }

            if ($spacecraft->isBase()) {
                $messagesForBases->add($message);
            } else {
                $messagesForShips->add($message);
            }

            $this->shipRepository->save($spacecraft);
        }

        $this->informSpacecraftOwnersAboutConsequences(
            $anomaly->getLocation()->getSectorString(),
            $messagesForShips,
            $messagesForBases
        );
    }

    public function letAnomalyDisappear(AnomalyInterface $anomaly): void
    {
        //TODO
    }

    private function informSpacecraftOwnersAboutCreation(AnomalyInterface $anomaly): void
    {
        $usersToInform = [];

        $location = $anomaly->getLocation();
        $spacecrafts = $location->getShips();

        foreach ($spacecrafts as $spacecraft) {
            $usersToInform[$spacecraft->getUser()->getId()] = $spacecraft->getUser();
        }

        foreach ($usersToInform as $user) {
            $this->privateMessageSender->send(
                UserEnum::USER_NOONE,
                $user->getId(),
                sprintf('[b][color=yellow]In Sektor %s ist aufgrund des immensen Energiepotentials eine Subraumellipse entstanden![/color][/b]', $location->getSectorString())
            );
        }
    }

    private function informSpacecraftOwnersAboutConsequences(
        string $sectorString,
        MessageCollectionInterface $messageCollectionForShips,
        MessageCollectionInterface $messageCollectionForBases
    ): void {

        $header = sprintf(
            "[b][color=red]Subraumellipse in Sektor %s[/color][/b]",
            $sectorString
        );

        $this->distributedMessageSender->distributeMessageCollection(
            $messageCollectionForShips,
            UserEnum::USER_NOONE,
            PrivateMessageFolderTypeEnum::SPECIAL_SHIP,
            $header
        );

        $this->distributedMessageSender->distributeMessageCollection(
            $messageCollectionForBases,
            UserEnum::USER_NOONE,
            PrivateMessageFolderTypeEnum::SPECIAL_STATION,
            $header
        );
    }
}
