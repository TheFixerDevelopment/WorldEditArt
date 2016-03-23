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

class WeightedBlockType{
	/** @var float $weight */
	private $weight;
	/** @var int $id */
	private $id;
	/** @var int $damage */
	private $damage;

	public function __construct(float $weight, int $id, int $damage){
		$this->weight = $weight;
		$this->id = $id;
		$this->damage = $damage;
	}

	public function getWeight() : float{
		return $this->weight;
	}

	public function getId() : int{
		return $this->id;
	}

	public function getDamage() : int{
		return $this->damage;
	}

	public function getBlock() : Block{
		return Block::get($this->id, $this->damage);
	}
}
