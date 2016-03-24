<?php

/*
 * WorldEditArt
 *
 * Copyright (C) 2016 PEMapModder
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PEMapModder
 */

namespace WorldEditArt\Objects\BlockStream;

use pocketmine\block\Block;
use pocketmine\utils\Random;
use WorldEditArt\WorldEditArt;

class WeightedListBlockReplacer implements BlockChanger{
	/** @var Random $random */
	private $random;
	/** @var float[] $source */
	private $source = [];
	/** @var WeightedBlockList $list */
	private $list;

	/**
	 * WeightedListBlockReplacer constructor.
	 *
	 * @param WeightedBlockType[] $source
	 * @param WeightedBlockList   $list
	 * @param int                 $seed default -1
	 *
	 * @throws \TypeError
	 */
	public function __construct(array $source, WeightedBlockList $list, int $seed = -1){
		foreach($source as $type){
			if(!($type instanceof WeightedBlockType)){
				throw new \TypeError("Expected WeightedBlockType[]");
			}
			if($type->getWeight() > 1.0 or $type->getWeight() < 0.0){
				throw new \OutOfRangeException("Type chance must be within 0 and 1 inclusive");
			}
			if($type->getWeight() !== 1.0){
				$random = true;
			}
			$this->source[WorldEditArt::itemTypeHash($type->getId(), $type->getDamage())] = $type->getWeight();
		}
		$this->list = $list;
		if(isset($random)){
			$this->random = new Random($seed);
		}
	}

	public function changeBlock(Block $block){
		$hash = WorldEditArt::itemTypeHash($block->getId(), $block->getDamage());
		if(isset($this->source[$hash])){
			if(!isset($this->random) or $this->random->nextInt() / 0x80000000 < $this->source[$hash]){
				return $this->list->nextRandom();
			}
		}
		return null;
	}
}
