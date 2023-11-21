<?php

declare(strict_types=1);

namespace ipad54\composter;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockLegacyIds as LegacyIds;
use pocketmine\item\ItemId;

class Main extends PluginBase
{

    protected function onLoad(): void
    {
        $composter = new Compostable(new BlockIdentifier(LegacyIds::COMPOSTER, 0, ItemId::COMPOSTER));
        BlockFactory::getInstance()->registerBlock($composter);
    }
}
