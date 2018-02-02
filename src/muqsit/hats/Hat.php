<?php
namespace muqsit\hats;

use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\{AddEntityPacket, RemoveEntityPacket};
use pocketmine\network\mcpe\protocol\types\EntityLink;
use pocketmine\Player;

class Hat {

    /** @var Player */
    private $player;

    /** @var Block */
    private $block;

    /** @var int */
    private $hatId;

    /** @var AddEntityPacket */
    private $spawnPacket;

    public function __construct(Player $player)
    {
        $this->player = $player;
    }

    public function setBlock(Block $block, bool $render = true) : void
    {
        $this->block = $block;
        if ($render) {
            $this->render();
        }
    }

    public function getBlock() : Block
    {
        return $this->block;
    }

    public function render() : void
    {
        if ($this->block === null) {
            return;
        }

        if ($this->spawnPacket === null) {
            $pk = new AddEntityPacket();
            $pk->entityRuntimeId = $this->hatId = Entity::$entityCount++;
            $pk->type = Entity::FALLING_BLOCK;
            $pk->position = new Vector3();

            //these are needed for combat - you cannot hit the hat!
            $pk->metadata[Entity::DATA_BOUNDING_BOX_WIDTH] = [Entity::DATA_TYPE_FLOAT, 0];
            $pk->metadata[Entity::DATA_BOUNDING_BOX_HEIGHT] = [Entity::DATA_TYPE_FLOAT, 0];

            $pk->metadata[Entity::DATA_RIDER_SEAT_POSITION] = [Entity::DATA_TYPE_VECTOR3F, new Vector3(0, 0.4, 0)];
            $this->spawnPacket = $pk;
        }

        $this->spawnPacket->metadata[Entity::DATA_VARIANT] = [Entity::DATA_TYPE_INT, $this->block->getId() | ($this->block->getDamage() << 8)];

        if (count($this->spawnPacket->links) === 0) {
            $link = new EntityLink();
            $link = new EntityLink();
            $link->fromEntityUniqueId = $this->player->getId();
            $link->toEntityUniqueId = $this->hatId;
            $link->type = 2;
            $link->bool1 = false;
            $this->spawnPacket->links[] = $link;
        }
    }

    public function send(Player ...$players) : void
    {
        if ($this->spawnPacket !== null) {
            $pk = clone $this->spawnPacket;
            foreach ($players as $player) {
                $player->dataPacket($pk);
            }
        }
    }

    public function unsend(Player $player) : void
    {
        if ($this->spawnPacket !== null) {
            $pk = new RemoveEntityPacket();
            $pk->entityUniqueId = $this->hatId;
            $player->dataPacket($pk);
        }
    }
}