<?php

namespace ipad54\composter\block;

use ipad54\composter\sound\ComposteEmptySound;
use ipad54\composter\sound\ComposteFillSound;
use ipad54\composter\sound\ComposteFillSuccessSound;
use ipad54\composter\sound\ComposteReadySound;
use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockToolType;
use pocketmine\block\Opaque;
use pocketmine\data\bedrock\block\BlockStateWriter;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\SpawnParticleEffectPacket;
use pocketmine\player\Player;
use pocketmine\world\World;

class Composter extends Opaque
{
    protected int $fill = 0;

    protected array $ingredients = [
        VanillaItems::NETHER_WART()->getTypeId() => 30,
        VanillaItems::GRASS()->getTypeId() => 30,
        VanillaItems::KELP()->getTypeId() => 30,
        VanillaItems::LEAVES()->getTypeId() => 30,
        VanillaItems::DRIED_KELP()->getTypeId() => 30,
        VanillaItems::BEETROOT_SEEDS()->getTypeId() => 30,
        VanillaItems::MELON_SEEDS()->getTypeId() => 30,
        VanillaItems::WHEAT_SEEDS()->getTypeId() => 30,
        VanillaItems::PUMPKIN_SEEDS()->getTypeId() => 30,
        VanillaItems::SEAGRASS()->getTypeId() => 30,
        VanillaItems::SUGAR_CANE()->getTypeId() => 50,
        VanillaItems::MELON_SLICE()->getTypeId() => 50,
        VanillaItems::CACTUS()->getTypeId() => 50,
        VanillaItems::BAKED_POTATO()->getTypeId() => 85,
        VanillaItems::BREAD()->getTypeId() => 85,
        VanillaItems::PUMPKIN_PIE()->getTypeId() => 100
    ];

    public function __construct()
    {
        parent::__construct("Composter", new BlockBreakInfo(0.75, BlockToolType::AXE));
    }

    public function getFuelTime(): int
    {
        return 300;
    }

    protected function writeStateToCompoundTag(CompoundTag $tag): void
    {
        $tag->setInt("fill", $this->fill);
    }

    public function readStateFromCompoundTag(CompoundTag $tag): void
    {
        $this->fill = $tag->getInt("fill", 0);
    }

    private function spawnParticleEffect(Vector3 $position): void
    {
        $packet = SpawnParticleEffectPacket::create(
            $position,
            "minecraft:crop_growth_emitter"
        );

        foreach ($this->position->getWorld()->getNearbyPlayers($this->position, 16) as $player) {
            $player->getNetworkSession()->sendDataPacket($packet);
        }
    }

    public function onInteract(Player $player, Vector3 $clickPos): bool
    {
        $item = $player->getInventory()->getItemInHand();
        $world = $this->position->getWorld();

        if ($this->fill >= 8) {
            $this->fill = 0;
            $world->setBlock($this->position, $this);
            $world->addSound($this->position, new ComposteEmptySound());
            $world->dropItem($this->position->add(0.5, 1.1, 0.5), VanillaItems::BONE_MEAL());
            return true;
        }

        if (isset($this->ingredients[$item->getTypeId()]) && $this->fill < 7) {
            $item->pop();
            $player->getInventory()->setItemInHand($item);
            $this->spawnParticleEffect($this->position->add(0.5, 0.5, 0.5));

            $chance = $this->ingredients[$item->getTypeId()];
            if (mt_rand(0, 100) <= $chance) {
                $this->incrementFill(true);
                return true;
            }

            $world->addSound($this->position, new ComposteFillSound());
        }

        return true;
    }

    public function incrementFill(bool $playsound = false): bool
    {
        if ($this->fill >= 7) {
            return false;
        }

        if (++$this->fill >= 7) {
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
            ++$this->fill;
            $this->position->getWorld()->setBlock($this->position, $this);
            $this->position->getWorld()->addSound($this->position, new ComposteReadySound());
        }
    }
}
