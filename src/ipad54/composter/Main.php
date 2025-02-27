<?php

declare(strict_types=1);

namespace ipad54\composter;

use ipad54\composter\block\Composter;
use pocketmine\block\BlockFactory;
use pocketmine\block\VanillaBlocks;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase
{
    protected function onLoad() : void
    {
        BlockFactory::getInstance()->registerCustomBlock(new Composter());
    }
}
