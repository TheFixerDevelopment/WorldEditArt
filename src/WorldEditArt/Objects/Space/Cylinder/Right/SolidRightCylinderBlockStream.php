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

use pocketmine\level\Level;
use pocketmine\math\Vector3;
use WorldEditArt\Objects\BlockStream\BlockStream;

class SolidRightCylinderBlockStream implements BlockStream{
	/** @var Level $level */
	protected $level;
	/** @var string $ax0 */
	protected $ax0;
	/** @var string $ax1 */
	protected $ax1;
	/** @var string $ax2 */
	protected $ax2;
	/** @var int $v0 */
	protected $v0;
	/** @var int $max0 */
	protected $max0;
	/** @var Vector3[] $circle */
	protected $circle = [];
	/** @var int $circlePointer */
	protected $circlePointer = 0;

	public function __construct(RightCylindricalSpace $cyl){
		$this->level = $cyl->getLevel();
		$this->ax0 = $cyl->axis0();
		$this->ax1 = $cyl->axis1();
		$this->ax2 = $cyl->axis2();
		$two = [$cyl->getCenter()->{$this->ax0}, $cyl->getCenter()->{$cyl->getHeight()}];
		$this->v0 = (int) min($two);
		$this->max0 = (int) ceil(max($two));
		$this->initCircle($cyl);
	}

	protected function initCircle(RightCylindricalSpace $cyl){
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

	public function next(){
		if(!isset($this->circle[$this->circlePointer])){
			$this->circlePointer = 0;
			$this->v0++;
			if($this->v0 > $this->max0){
				return null;
			}
		}
		$vector = $this->circle[$this->circlePointer++];
		$vector->{$this->ax0} = $this->v0;
		return $this->level->getBlock($vector);
	}
}
