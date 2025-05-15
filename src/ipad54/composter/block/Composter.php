<?php

declare(strict_types=1);

namespace ipad54\composter\block;

use pocketmine\network\mcpe\protocol\SpawnParticleEffectPacket;
use ipad54\composter\sound\ComposteEmptySound;
use ipad54\composter\sound\ComposteFillSound;
use ipad54\composter\sound\ComposteFillSuccessSound;
use ipad54\composter\sound\ComposteReadySound;
use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockManager;
use pocketmine\block\BlockToolType;
use pocketmine\block\Opaque;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

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
        VanillaItems::PUMPKIN_SEEDS()->getTypeId() => 30,
        VanillaItems::SEAGRASS()->getTypeId() => 30,
        VanillaItems::MELON()->getTypeId() => 50,
        VanillaItems::CACTUS()->getTypeId() => 50,
    ];

    public function __construct()
    {
        parent::__construct("Composter", new BlockBreakInfo(0.75, BlockToolType::AXE));
    }

    private function spawnParticleEffect(Vector3 $position): void
    {
        $packet = new SpawnParticleEffectPacket();
        $packet->position = $position;
        $packet->particleName = "minecraft:crop_growth_emitter";
        foreach ($this->position->getWorld()->getViewersForPosition($this->position) as $player) {
            $player->getNetworkSession()->sendDataPacket($packet);
        }
    }

    public function onInteract(Player $player, VanillaItems $item): bool
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

            if (mt_rand(0, 100) <= $this->ingredients[$item->getTypeId()]) {
                $this->incrementFill(true);
                return true;
            }

            $this->position->getWorld()->addSound($this->position, new ComposteFillSound());
        }
        return true;
    }

    private function incrementFill(bool $playsound = false): bool
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
}
