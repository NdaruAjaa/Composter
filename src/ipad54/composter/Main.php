<?php

declare(strict_types=1);

namespace ipad54\composter;

use ipad54\composter\block\Composter;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\item\ItemTypeIds;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase
{
    protected function onLoad(): void
    {
        RuntimeBlockStateRegistry::getInstance()->register(Composter::class);
        
        // If you need to map the block to a specific type ID (optional)
        RuntimeBlockStateRegistry::getInstance()->map(
            BlockTypeIds::COMPOSTER,
            fn() => new Composter(BlockTypeIds::COMPOSTER, 0, ItemTypeIds::COMPOSTER)
        );
    }
}
