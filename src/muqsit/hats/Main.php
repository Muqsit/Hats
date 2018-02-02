<?php
namespace muqsit\hats;

use pocketmine\block\{Block, Pumpkin, Skull};
use pocketmine\command\{CommandSender, Command};
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;

class Main extends PluginBase implements Listener {

    /** @var FallingSand[] */
    private $hats = [];

    public function onEnable() : void
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onPlayerCreation(PlayerCreationEvent $event) : void
    {
        $event->setPlayerClass(HatPlayer::class);
    }

    public function onCommand(CommandSender $issuer, Command $cmd, string $label, array $args) : bool
    {
        if (!($issuer instanceof Player)) {
            $issuer->sendMessage(TF::RED."You aren't a player.");
            return false;
        }

        $item = $issuer->getInventory()->getItemInHand();

        if (!$this->setHatBlock($issuer, $item, $error)) {
            $issuer->sendMessage(TF::RED.$error);
            return false;
        }

        $issuer->sendMessage(TF::GREEN.'You are now wearing '.$item->getName().TF::RESET.TF::GREEN.'!');
        return true;
    }

    public function setHatBlock(Player $issuer, Item $item, &$error = null) : bool
    {
        if ($item->isNull()) {
            $error = "You aren't holding a block.";
            return false;
        }

        $block = $item->getBlock();
        if ($block->getId() === Block::AIR) {
            $error = $item->getName().' is an invalid block!';
            return false;
        }

        if (!$this->isRenderable($item)) {
            $error = 'You cannot wear '.$item->getName().'.';
            return false;
        }

        $inv = $issuer->getArmorInventory();

        $currentHelmet = $inv->getHelmet();
        if (!$currentHelmet->isNull() && $issuer->isSurvival()) {
            foreach ($issuer->getInventory()->addItem($currentHelmet) as $helmet) {
                $issuer->getLevel()->dropItem($helmet);
            }
        }

        $inv->setHelmet($item);
        $this->renderBlockOntoPlayer($issuer, $block);

        return true;
    }

    public function isRenderable(Item $item) : bool
    {
        if ($item instanceof Armor) {
            return false;
        }

        $block = $item->getBlock();
        if ($block->getId() === Block::AIR) {
            return false;
        }

        return !($block instanceof Pumpkin) && !($block instanceof Skull);
    }

    public function renderBlockOntoPlayer(Player $player, Block $block) : void
    {
        ($this->hats[$k = $player->getId()] ?? $this->hats[$k] = new Hat($player))->setBlock($block);
        $player->setHat($this->hats[$k]);
    }
}