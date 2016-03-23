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

use pocketmine\utils\Random;

class WeightedBlockList{
	/** @var WeightedBlockType[] $list */
	private $list;
	/** @var float $weightSum */
	private $weightSum = 0.0;
	/** @var Random */
	private $random;

	public function __construct(int $seed = -1){
		$this->random = new Random();
	}

	public function add(WeightedBlockType $type){
		$this->list[] = $type;
		$this->weightSum += $type->getWeight();
	}

	public function nextRandom() : WeightedBlockType{
		$max = $this->random->nextFloat() * $this->weightSum;
		foreach($this->list as $type){
			$max -= $type->getWeight();
			if($max <= 0){
				return $type;
			}
		}
		assert(count($this->list) === 0);
		throw new \InvalidArgumentException("Empty block list");
	}
}
