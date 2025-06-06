<?php

namespace ipad54\composter\block;

use pocketmine\block\Block;
use pocketmine\block\BlockTypeInfo;
use pocketmine\block\Opaque;
use pocketmine\block\utils\BlockDataSerializer;
use pocketmine\block\utils\HorizontalFacingTrait;
use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\SpawnParticleEffectPacket;
use pocketmine\player\Player;
use ipad54\composter\sound\ComposteEmptySound;
use ipad54\composter\sound\ComposteFillSound;
use ipad54\composter\sound\ComposteFillSuccessSound;
use ipad54\composter\sound\ComposteReadySound;

class Composter extends Opaque
{
    use HorizontalFacingTrait;

    protected int $fill = 0;
    
    protected array $ingredients = [
        ItemTypeIds::NETHER_WART => 30,
        ItemTypeIds::GRASS => 30,
        ItemTypeIds::KELP => 30,
        ItemTypeIds::LEAVES => 30,
        ItemTypeIds::DRIED_KELP => 30,
        ItemTypeIds::BEETROOT_SEEDS => 30,
        ItemTypeIds::MELON_SEEDS => 30,
        ItemTypeIds::WHEAT_SEEDS => 30,
        ItemTypeIds::PUMPKIN_SEEDS => 30,
        ItemTypeIds::TALL_GRASS => 30,
        ItemTypeIds::SEAGRASS => 30,

        ItemTypeIds::DRIED_KELP_BLOCK => 50,
        ItemTypeIds::CACTUS => 50,
        ItemTypeIds::MELON => 50,
        ItemTypeIds::SUGARCANE => 50,

        ItemTypeIds::MELON_BLOCK => 65,
        ItemTypeIds::MUSHROOM_STEW => 65,
        ItemTypeIds::POTATO => 65,
        ItemTypeIds::WATER_LILY => 65,
        ItemTypeIds::CARROT => 65,
        ItemTypeIds::SEA_PICKLE => 65,
        ItemTypeIds::BROWN_MUSHROOM_BLOCK => 65,
        ItemTypeIds::RED_MUSHROOM_BLOCK => 65,
        ItemTypeIds::WHEAT => 65,
        ItemTypeIds::BEETROOT => 65,
        ItemTypeIds::PUMPKIN => 65,
        ItemTypeIds::CARVED_PUMPKIN => 65,
        ItemTypeIds::RED_FLOWER => 65,
        ItemTypeIds::YELLOW_FLOWER => 65,
        ItemTypeIds::APPLE => 65,

        ItemTypeIds::COOKIE => 85,
        ItemTypeIds::BAKED_POTATO => 85,
        ItemTypeIds::HAY_BALE => 85,
        ItemTypeIds::BREAD => 85,

        ItemTypeIds::CAKE => 100,
        ItemTypeIds::PUMPKIN_PIE => 100
    ];

    public function __construct(BlockTypeInfo $typeInfo)
    {
        parent::__construct($typeInfo);
    }

    protected function writeStateToMeta(): int
    {
        return $this->fill;
    }

    public function readStateFromData(int $id, int $stateMeta): void
    {
        $this->fill = BlockDataSerializer::readBoundedInt("fill", $stateMeta, 0, 8);
    }

    public function getStateBitmask(): int
    {
        return 0b1111;
    }

    private function spawnParticleEffect(Vector3 $position): void
    {
        $packet = new SpawnParticleEffectPacket();
        $packet->position = $position;
        $packet->particleName = "minecraft:crop_growth_emitter";
        $this->position->getWorld()->broadcastPacketToViewers($this->position, $packet);
    }

    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []): bool
    {
        if ($this->fill >= 8) {
            $this->fill = 0;
            $this->position->getWorld()->setBlock($this->position, $this);
            $this->position->getWorld()->addSound($this->position, new ComposteEmptySound());
            $this->position->getWorld()->dropItem($this->position->add(0.5, 1.1, 0.5), VanillaItems::BONE_MEAL());
            return true;
        }
        
        if (isset($this->ingredients[$item->getTypeId()]) && $this->fill < 7) {
            $item->pop();
            $this->spawnParticleEffect($this->position->add(0.5, 0.5, 0.5));
            
            if ($this->fill == 0) {
                $this->incrementFill(true);
                return true;
            }
            
            $chance = $this->ingredients[$item->getTypeId()];
            if (mt_rand(0, 100) <= $chance) {
                $this->incrementFill(true);
                return true;
            }
            
            $this->position->getWorld()->addSound($this->position, new ComposteFillSound());
        }
        return true;
    }

    public function incrementFill(bool $playsound = false): bool
    {
        if ($this->fill >= 7) {
            return false;
        }
        
        $this->fill++;
        if ($this->fill >= 7) {
            $this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, 25);
        } else {
            $this->position->getWorld()->setBlock($this->position, $this);
        }
        
        if ($playsound) {
            $this->position->getWorld()->addSound($this->position, new ComposteFillSuccessSound());
        }
        return true;
    }

    public function onScheduledUpdate(): void
    {
        if ($this->fill == 7) {
            $this->fill++;
            $this->position->getWorld()->setBlock($this->position, $this);
            $this->position->getWorld()->addSound($this->position, new ComposteReadySound());
        }
    }
}
