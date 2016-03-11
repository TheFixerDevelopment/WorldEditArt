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

use pocketmine\math\Vector3;

class HollowRightCylinderBlockStream extends SolidRightCylinderBlockStream{
	/** @var int $padding */
	private $padding;
	/** @var int $margin */
	private $margin;

	public function __construct(RightCylindricalSpace $cylinder, int $padding = 1, int $margin = 0){
		parent::__construct($cylinder);
		$this->padding = $padding;
		$this->margin = $margin;
		$this->v0--;
		$this->max0++;
	}

	public function initCircle(RightCylindricalSpace $cyl){
		$base = new Vector3;
		$radiusSquared = $cyl->getRadius() ** 2;
		for($v1 = $cyl->{$this->ax1} - $cyl->getRadius(); $v1 <= $cyl->{$this->ax1} + 1; $v1++){
			for($v2 = $cyl->{$this->ax2} - $cyl->getRadius(); $v2 <= $cyl->{$this->ax2} + 1; $v2++){
				$vector = new Vector3;
				$vector->{$this->ax1} = $v1;
				$vector->{$this->ax2} = $v2;
				if($vector->distanceSquared($base) <= $radiusSquared){
					$this->circle[] = $vector;
				}
			}
		}
	}
}
