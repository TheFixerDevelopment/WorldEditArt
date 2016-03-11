<?php

/*
 * WorldEditArt
 *
 * Copyright (C) 2016 LegendsOfMCPE
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author LegendsOfMCPE
 */

namespace WorldEditArt\DataProvider\Model;

use pocketmine\level\Position;
use pocketmine\math\Vector3;

class Zone{
	const TYPE_UNDER_CONSTRUCTION = 1;

	/** @var string $name */
	private $name;
	/** @var int $type */
	private $type;
	/** @var string $level */
	private $level;
	/** @var Vector3 $start */
	private $start;
	/** @var Vector3 $end */
	private $end;

	public function __construct(string $name, int $type, string $level, Vector3 $start, Vector3 $end){
		$this->name = $name;
		$this->type = $type;
		$this->level = $level;
		$this->start = $start;
		$this->end = $end;
	}

	public function getName() : string{
		return $this->name;
	}

	public function getType() : int{
		return $this->type;
	}

	public function getLevelName() : string{
		return $this->level;
	}

	public function getStart() : Vector3{
		return $this->start;
	}

	public function getEnd() : Vector3{
		return $this->end;
	}

	/**
	 * Returns whether the position is inside this zone
	 *
	 * @param Position $pos
	 *
	 * @return bool
	 */
	public function isInside(Position $pos) : bool{
		return (
			$pos->getLevel()->getName() === $this->level and
			($this->start->x <=> $pos->x) + ($this->end->x <=> $pos->x) === 0 and
			($this->start->y <=> $pos->y) + ($this->end->y <=> $pos->y) === 0 and
			($this->start->z <=> $pos->z) + ($this->end->z <=> $pos->z) === 0
		);
	}
}
