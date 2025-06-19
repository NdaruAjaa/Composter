<?php

declare(strict_types=1);

namespace ipad54\composter\block;

use pocketmine\block\Block;
use pocketmine\block\Opaque;
use pocketmine\block\utils\BlockDataSerializer;
use pocketmine\block\utils\HorizontalFacingTrait;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\item\Item;
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

    protected int $fillLevel = 0;

    /** @var array<string, int> */
    protected array $ingredients = [
        // Converted to string IDs
        'minecraft:nether_wart' => 30,
        'minecraft:grass' => 30,
        'minecraft:kelp' => 30,
        'minecraft:leaves' => 30,
        'minecraft:dried_kelp' => 30,
        'minecraft:beetroot_seeds' => 30,
        'minecraft:melon_seeds' => 30,
        'minecraft:wheat_seeds' => 30,
        'minecraft:pumpkin_seeds' => 30,
        'minecraft:tallgrass' => 30,
        'minecraft:seagrass' => 30,

        'minecraft:dried_kelp_block' => 50,
        'minecraft:cactus' => 50,
        'minecraft:melon_slice' => 50,
        'minecraft:sugar_cane' => 50,

        'minecraft:melon_block' => 65,
        'minecraft:mushroom_stew' => 65,
        'minecraft:potato' => 65,
        'minecraft:waterlily' => 65,
        'minecraft:carrot' => 65,
        'minecraft:sea_pickle' => 65,
        'minecraft:brown_mushroom_block' => 65,
        'minecraft:red_mushroom_block' => 65,
        'minecraft:wheat' => 65,
        'minecraft:beetroot' => 65,
        'minecraft:pumpkin' => 65,
        'minecraft:carved_pumpkin' => 65,
        'minecraft:red_flower' => 65,
        'minecraft:yellow_flower' => 65,
        'minecraft:apple' => 65,

        'minecraft:cookie' => 85,
        'minecraft:baked_potato' => 85,
        'minecraft:hay_block' => 85,
        'minecraft:bread' => 85,

        'minecraft:cake' => 100,
        'minecraft:pumpkin_pie' => 100
    ];

    public function getFuelTime(): int
    {
        return 300;
    }

    protected function describeBlockOnlyState(RuntimeDataDescriber $w): void
    {
        $w->boundedInt(0, 8, $this->fillLevel);
        $this->describeHorizontalFacing($w);
    }

    public function getFillLevel(): int
    {
        return $this->fillLevel;
    }

    public function setFillLevel(int $fillLevel): self
    {
        if($fillLevel < 0 || $fillLevel > 8){
            throw new \InvalidArgumentException("Fill level must be in range 0-8");
        }
        $this->fillLevel = $fillLevel;
        return $this;
    }

    private function spawnParticleEffect(Vector3 $position): void
    {
        $packet = new SpawnParticleEffectPacket();
        $packet->position = $position;
        $packet->particleName = "minecraft:crop_growth_emitter";
        
        $world = $this->position->getWorld();
        foreach($world->getViewersForPosition($this->position) as $player){
            $player->getNetworkSession()->sendDataPacket($packet);
        }
    }

    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []): bool
    {
        if ($this->fillLevel >= 8) {
            $this->fillLevel = 0;
            $this->position->getWorld()->setBlock($this->position, $this);
            $this->position->getWorld()->addSound($this->position, new ComposteEmptySound());
            $this->position->getWorld()->dropItem($this->position->add(0.5, 1.1, 0.5), VanillaItems::BONE_MEAL());
            return true;
        }

        $itemId = $item->getTypeId();
        if (isset($this->ingredients[$itemId]) {
            $item->pop();
            $returnedItems[] = $item;
            
            $this->spawnParticleEffect($this->position->add(0.5, 0.5, 0.5));
            
            if ($this->fillLevel === 0) {
                $this->incrementFill(true);
                return true;
            }

            $chance = $this->ingredients[$itemId];
            if (mt_rand(0, 100) <= $chance) {
                $this->incrementFill(true);
                return true;
            }
            
            $this->position->getWorld()->addSound($this->position, new ComposteFillSound());
        }
        
        return true;
    }

    public function incrementFill(bool $playSound = false): bool
    {
        if ($this->fillLevel >= 7) {
            return false;
        }
        
        $this->fillLevel++;
        
        if ($this->fillLevel >= 7) {
            $this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, 25);
        } else {
            $this->position->getWorld()->setBlock($this->position, $this);
        }
        
        if ($playSound) {
            $this->position->getWorld()->addSound($this->position, new ComposteFillSuccessSound());
        }
        
        return true;
    }

    public function onScheduledUpdate(): void
    {
        if ($this->fillLevel === 7) {
            $this->fillLevel++;
            $this->position->getWorld()->setBlock($this->position, $this);
            $this->position->getWorld()->addSound($this->position, new ComposteReadySound());
        }
    }
}
