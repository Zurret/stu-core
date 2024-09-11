<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowTransfer;

use Override;
use request;
use Stu\Lib\Transfer\InitializeShowTransferInterface;
use Stu\Lib\Transfer\TransferTargetLoaderInterface;
use Stu\Lib\Transfer\TransferTypeEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\Interaction\InteractionChecker;
use Stu\Module\Ship\Lib\ShipLoaderInterface;

final class ShowTransfer implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_TRANSFER';

    public function __construct(
        private ShipLoaderInterface $shipLoader,
        private TransferTargetLoaderInterface $transferTargetLoader,
        private InitializeShowTransferInterface $initializeShowTransfer
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        $shipId = request::getIntFatal('id');
        $targetId = request::getIntFatal('target');
        $isUnload = request::getIntFatal('is_unload') === 1;
        $isColonyTarget = request::getIntFatal('is_colony') === 1;
        $transferType = TransferTypeEnum::from(request::getIntFatal('transfer_type'));

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            $shipId,
            $user->getId(),
            true,
            false
        );

        $ship = $wrapper->get();

        $target = $this->transferTargetLoader->loadTarget($targetId, $isColonyTarget, false);

        $game->setMacroInAjaxWindow('html/entityNotAvailable.twig');

        if (!InteractionChecker::canInteractWith($ship, $target, $game, true)) {
            return;
        }

        $game->setMacroInAjaxWindow('html/transfer/ship/shipTransfer.twig');

        $game->setTemplateVar('SHIP', $ship);
        $this->initializeShowTransfer->init(
            $ship,
            $target,
            $isUnload,
            $transferType,
            $game
        );
    }
}
