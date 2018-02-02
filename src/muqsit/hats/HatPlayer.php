<?php
namespace muqsit\hats;

use pocketmine\Player;

class HatPlayer extends Player {

    /** @var Hat|null */
    private $hat;

    public function setHat(Hat $hat) : void
    {
        $this->hat = $hat;
        $hat->send($this, ...$this->getViewers());
    }

    protected function sendSpawnPacket(Player $player) : void
    {
        parent::sendSpawnPacket($player);
        if ($this->hat !== null) {
            $this->hat->send($player);
        }
    }

    public function despawnFrom(Player $player, bool $send = true) : void
    {
        parent::despawnFrom($player);
        if ($send && $this->hat !== null) {
            $this->hat->unsend($player);
        }
    }
}