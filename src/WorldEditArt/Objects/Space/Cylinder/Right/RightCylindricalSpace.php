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

namespace WorldEditArt\Objects\Space\Cylinder\Right;

use pocketmine\level\Position;
use pocketmine\math\Vector3;
use WorldEditArt\Objects\BlockStream\BlockStream;
use WorldEditArt\Objects\Space\Space;

class RightCylindricalSpace extends Space{
	const DIRECTION_X = 0;
	const DIRECTION_Y = 1;
	const DIRECTION_Z = 2;

	/** @var Vector3 $center */
	private $center;
	/** @var float $radius */
	private $radius;
	/** @var float $height */
	private $height;
	/** @var int $direction */
	private $direction;

	public function __construct(Position $center, int $direction = self::DIRECTION_Y, float $radius = -1.0, float $height = -1.0){
		parent::__construct($center->getLevel());
		$this->center = $center->floor();
		$this->direction = $direction;
		if($radius !== -1.0){
			$this->radius = $radius;
		}
		if($height !== -1.0){
			$this->height = $height;
		}
	}

	public function getSolidBlockStream() : BlockStream{
		return new SolidRightCylinderBlockStream($this);
	}

	public function getHollowBlockStream(int $padding = 1, int $margin = 0) : BlockStream{
		return new HollowRightCylinderBlockStream($this, $padding, $margin);
	}

	public function getApproxBlockCount() : int{
		// TODO: Implement getApproxBlockCount() method.
	}

	protected function isInsideImpl(Vector3 $pos) : bool{
	}

	public function getRadius() : float{
		$this->throwValid();
		return $this->radius;
	}

	public function getCenter() : Vector3{
		return $this->center;
	}

	public function getDirection() : int{
		return $this->direction;
	}

	public function getHeight() : float{
		$this->throwValid();
		return $this->height;
	}

	public function isValid() : bool{
		return isset($this->radius, $this->height);
	}

	public function axis0() : string{
		if($this->direction === self::DIRECTION_X){
			return "x";
		}elseif($this->direction === self::DIRECTION_Y){
			return "y";
		}else{
			return "z";
		}
	}

	public function axis1() : string{
		if($this->direction === self::DIRECTION_X){
			return "y";
		}elseif($this->direction === self::DIRECTION_Y){
			return "z";
		}else{
			return "x";
		}
	}

	public function axis2() : string{
		if($this->direction === self::DIRECTION_X){
			return "z";
		}elseif($this->direction === self::DIRECTION_Y){
			return "x";
		}else{
			return "y";
		}
	}
}
