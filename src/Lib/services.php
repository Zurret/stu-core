<?php

declare(strict_types=1);

namespace Stu\Lib;

use Psr\Container\ContainerInterface;
use Stu\Lib\Colony\PlanetFieldHostProvider;
use Stu\Lib\Colony\PlanetFieldHostProviderInterface;
use Stu\Lib\Information\InformationFactory;
use Stu\Lib\Information\InformationFactoryInterface;
use Stu\Lib\Mail\MailFactory;
use Stu\Lib\Mail\MailFactoryInterface;
use Stu\Lib\Map\DistanceCalculation;
use Stu\Lib\Map\DistanceCalculationInterface;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\BorderDataProvider;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\ColonyShieldDataProvider;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\MapDataProvider;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\Shipcount\ShipcountDataProviderFactory;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\Shipcount\ShipcountDataProviderFactoryInterface;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\Subspace\SubspaceDataProviderFactory;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\Subspace\SubspaceDataProviderFactoryInterface;
use Stu\Lib\Map\VisualPanel\Layer\PanelLayerCreation;
use Stu\Lib\Map\VisualPanel\Layer\PanelLayerCreationInterface;
use Stu\Lib\Map\VisualPanel\Layer\PanelLayerEnum;
use Stu\Lib\ModuleScreen\Addon\ModuleSelectorAddonFactory;
use Stu\Lib\ModuleScreen\Addon\ModuleSelectorAddonFactoryInterface;
use Stu\Lib\ModuleScreen\GradientColor;
use Stu\Lib\ModuleScreen\GradientColorInterface;
use Stu\Lib\Pirate\Behaviour\AttackShipBehaviour;
use Stu\Lib\Pirate\Behaviour\CallForSupportBehaviour;
use Stu\Lib\Pirate\Behaviour\ChangeAlertStateToRed;
use Stu\Lib\Pirate\Behaviour\DeactivateShieldsBehaviour;
use Stu\Lib\Pirate\Behaviour\FlyBehaviour;
use Stu\Lib\Pirate\Behaviour\HideBehaviour;
use Stu\Lib\Pirate\Behaviour\PirateBehaviourInterface;
use Stu\Lib\Pirate\Behaviour\RageBehaviour;
use Stu\Lib\Pirate\Behaviour\RubColonyBehaviour;
use Stu\Lib\Pirate\Behaviour\SearchFriendBehaviour;
use Stu\Lib\Pirate\Component\MoveOnLayer;
use Stu\Lib\Pirate\Component\MoveOnLayerInterface;
use Stu\Lib\Pirate\Component\PirateAttack;
use Stu\Lib\Pirate\Component\PirateAttackInterface;
use Stu\Lib\Pirate\Component\PirateFlight;
use Stu\Lib\Pirate\Component\PirateFlightInterface;
use Stu\Lib\Pirate\Component\PirateNavigation;
use Stu\Lib\Pirate\Component\PirateNavigationInterface;
use Stu\Lib\Pirate\Component\PirateWrathManager;
use Stu\Lib\Pirate\Component\PirateWrathManagerInterface;
use Stu\Lib\Pirate\Component\ReloadMinimalEps;
use Stu\Lib\Pirate\Component\ReloadMinimalEpsInterface;
use Stu\Lib\Pirate\Component\SafeFlightRoute;
use Stu\Lib\Pirate\Component\SafeFlightRouteInterface;
use Stu\Lib\Pirate\Component\TrapDetection;
use Stu\Lib\Pirate\Component\TrapDetectionInterface;
use Stu\Lib\Pirate\PirateBehaviourEnum;
use Stu\Lib\Pirate\PirateCreation;
use Stu\Lib\Pirate\PirateCreationInterface;
use Stu\Lib\Pirate\PirateReaction;
use Stu\Lib\Pirate\PirateReactionInterface;
use Stu\Lib\Session\SessionStringFactory;
use Stu\Lib\Session\SessionStringFactoryInterface;
use Stu\Lib\ShipManagement\HandleManagers;
use Stu\Lib\ShipManagement\HandleManagersInterface;
use Stu\Lib\ShipManagement\Manager\ManageBattery;
use Stu\Lib\ShipManagement\Manager\ManageCrew;
use Stu\Lib\ShipManagement\Manager\ManageReactor;
use Stu\Lib\ShipManagement\Manager\ManageTorpedo;
use Stu\Lib\ShipManagement\Provider\ManagerProviderFactory;
use Stu\Lib\ShipManagement\Provider\ManagerProviderFactoryInterface;
use Stu\Lib\Transfer\BeamUtil;
use Stu\Lib\Transfer\BeamUtilInterface;
use Stu\Lib\Transfer\Strategy\CommodityTransferStrategy;
use Stu\Lib\Transfer\Strategy\TorpedoTransferStrategy;
use Stu\Lib\Transfer\Strategy\TransferStrategyInterface;
use Stu\Lib\Transfer\Strategy\TroopTransferStrategy;
use Stu\Lib\Transfer\TransferTargetLoader;
use Stu\Lib\Transfer\TransferTargetLoaderInterface;
use Stu\Lib\Transfer\TransferTypeEnum;
use Stu\Module\Config\StuConfigInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;

use function DI\autowire;
use function DI\create;
use function DI\get;

return [
    SessionStringFactoryInterface::class => autowire(SessionStringFactory::class),
    InformationFactoryInterface::class => autowire(InformationFactory::class),
    UuidGeneratorInterface::class => autowire(UuidGenerator::class),
    ManagerProviderFactoryInterface::class => autowire(ManagerProviderFactory::class),
    ModuleSelectorAddonFactoryInterface::class => autowire(ModuleSelectorAddonFactory::class),
    GradientColorInterface::class => autowire(GradientColor::class),
    DistanceCalculationInterface::class => autowire(DistanceCalculation::class),
    BeamUtilInterface::class => autowire(BeamUtil::class),
    MailerInterface::class => function (ContainerInterface $c): MailerInterface {
        $stuConfig = $c->get(StuConfigInterface::class);
        $transportDsn = $stuConfig->getGameSettings()->getEmailSettings()->getTransportDsn();

        return new Mailer(Transport::fromDsn($transportDsn));
    },
    MailFactoryInterface::class => autowire(MailFactory::class),
    PlanetFieldHostProviderInterface::class => autowire(PlanetFieldHostProvider::class),
    TransferTargetLoaderInterface::class => autowire(TransferTargetLoader::class),
    HandleManagersInterface::class => create(HandleManagers::class)->constructor(
        [
            autowire(ManageBattery::class),
            autowire(ManageCrew::class),
            autowire(ManageReactor::class),
            autowire(ManageTorpedo::class),
        ]
    ),
    SubspaceDataProviderFactoryInterface::class => autowire(SubspaceDataProviderFactory::class),
    ShipcountDataProviderFactoryInterface::class => autowire(ShipcountDataProviderFactory::class),
    PanelLayerCreationInterface::class => autowire(PanelLayerCreation::class)->constructorParameter(
        'dataProviders',
        [
            PanelLayerEnum::SYSTEM->value => autowire(MapDataProvider::class),
            PanelLayerEnum::MAP->value => autowire(MapDataProvider::class),
            PanelLayerEnum::COLONY_SHIELD->value => autowire(ColonyShieldDataProvider::class),
            PanelLayerEnum::BORDER->value => autowire(BorderDataProvider::class)
        ]
    ),
    TransferStrategyInterface::class => [
        TransferTypeEnum::COMMODITIES->value => autowire(CommodityTransferStrategy::class),
        TransferTypeEnum::CREW->value => autowire(TroopTransferStrategy::class),
        TransferTypeEnum::TORPEDOS->value => autowire(TorpedoTransferStrategy::class)
    ],
    PirateBehaviourInterface::class => [
        PirateBehaviourEnum::FLY->value => autowire(FlyBehaviour::class),
        PirateBehaviourEnum::RUB_COLONY->value => autowire(RubColonyBehaviour::class),
        PirateBehaviourEnum::ATTACK_SHIP->value => autowire(AttackShipBehaviour::class),
        PirateBehaviourEnum::HIDE->value => autowire(HideBehaviour::class),
        PirateBehaviourEnum::RAGE->value => autowire(RageBehaviour::class),
        PirateBehaviourEnum::GO_ALERT_RED->value => autowire(ChangeAlertStateToRed::class),
        PirateBehaviourEnum::CALL_FOR_SUPPORT->value => autowire(CallForSupportBehaviour::class),
        PirateBehaviourEnum::SEARCH_FRIEND->value => autowire(SearchFriendBehaviour::class),
        PirateBehaviourEnum::DEACTIVATE_SHIELDS->value => autowire(DeactivateShieldsBehaviour::class)
    ],
    PirateCreationInterface::class => autowire(PirateCreation::class),
    PirateReactionInterface::class => autowire(PirateReaction::class)->constructorParameter(
        'behaviours',
        get(PirateBehaviourInterface::class)
    ),
    PirateFlightInterface::class => autowire(PirateFlight::class),
    SafeFlightRouteInterface::class => autowire(SafeFlightRoute::class),
    MoveOnLayerInterface::class => autowire(MoveOnLayer::class),
    PirateNavigationInterface::class => autowire(PirateNavigation::class),
    ReloadMinimalEpsInterface::class => autowire(ReloadMinimalEps::class),
    PirateWrathManagerInterface::class => autowire(PirateWrathManager::class),
    PirateAttackInterface::class => autowire(PirateAttack::class),
    TrapDetectionInterface::class => autowire(TrapDetection::class)
];
