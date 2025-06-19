<?php

declare(strict_types=1);

namespace ipad54\composter;

use ipad54\composter\block\Composter;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\data\bedrock\block\BlockStateNames;
use pocketmine\data\bedrock\block\BlockStateStringValues;
use pocketmine\data\bedrock\block\BlockTypeNames;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\world\format\io\GlobalBlockStateHandlers;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase
{
    protected function onLoad(): void
    {
        // 1. Register the block in VanillaBlocks (if contributing to core)
        // For a plugin, we'll need to manually register it
        
        // 2. Register block state serializer/deserializer
        $blockFactory = RuntimeBlockStateRegistry::getInstance();
        $serializer = GlobalBlockStateHandlers::getSerializer();
        $deserializer = GlobalBlockStateHandlers::getDeserializer();
        
        $composter = new Composter();
        
        // 3. Register all possible states
        $blockFactory->register($composter);
        
        // 4. Register serializer
        $serializer->map($composter, function(Composter $block, RuntimeDataDescriber $w) {
            $w->enum($block->getComposterLevel(), [
                0 => BlockStateStringValues::COMPOSTER_LEVEL_0,
                1 => BlockStateStringValues::COMPOSTER_LEVEL_1,
                2 => BlockStateStringValues::COMPOSTER_LEVEL_2,
                3 => BlockStateStringValues::COMPOSTER_LEVEL_3,
                4 => BlockStateStringValues::COMPOSTER_LEVEL_4,
                5 => BlockStateStringValues::COMPOSTER_LEVEL_5,
                6 => BlockStateStringValues::COMPOSTER_LEVEL_6,
                7 => BlockStateStringValues::COMPOSTER_LEVEL_7,
                8 => BlockStateStringValues::COMPOSTER_LEVEL_8
            ]);
        });
        
        // 5. Register deserializer
        $deserializer->map(BlockTypeNames::COMPOSTER, function(RuntimeDataDescriber $w) : Composter {
            $composter = new Composter();
            $w->enum($composter->getComposterLevel(), [
                BlockStateStringValues::COMPOSTER_LEVEL_0 => 0,
                BlockStateStringValues::COMPOSTER_LEVEL_1 => 1,
                BlockStateStringValues::COMPOSTER_LEVEL_2 => 2,
                BlockStateStringValues::COMPOSTER_LEVEL_3 => 3,
                BlockStateStringValues::COMPOSTER_LEVEL_4 => 4,
                BlockStateStringValues::COMPOSTER_LEVEL_5 => 5,
                BlockStateStringValues::COMPOSTER_LEVEL_6 => 6,
                BlockStateStringValues::COMPOSTER_LEVEL_7 => 7,
                BlockStateStringValues::COMPOSTER_LEVEL_8 => 8
            ]);
            return $composter;
        });
    }
}
