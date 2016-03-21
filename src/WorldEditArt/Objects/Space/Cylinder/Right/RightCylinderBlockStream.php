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

class RightCylinderBlockStream implements BlockStream{
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
	/** @var bool $isHollow */
	protected $isHollow = false;

	public function __construct(RightCylindricalSpace $cyl, bool $isHollow = false){
		$this->level = $cyl->getLevel();
		$this->ax0 = $cyl->axis0();
		$this->ax1 = $cyl->axis1();
		$this->ax2 = $cyl->axis2();
		$two = [$cyl->getCenter()->{$this->ax0}, $cyl->getCenter()->{$cyl->getHeight()}];
		$this->v0 = (int) min($two);
		$this->max0 = (int) ceil(max($two));
		$this->isHollow = $isHollow;
		$this->initCircle($cyl);
	}

	protected function initCircle(RightCylindricalSpace $cyl){
		$radius = $cyl->getRadius();
		$radiusSquared = $radius ** 2;
		$center = $cyl->getCenter();
//		for($v1 = $cyl->{$this->ax1} - $cyl->getRadius(); $v1 <= $cyl->{$this->ax1} + 1; $v1++){
//			for($v2 = $cyl->{$this->ax2} - $cyl->getRadius(); $v2 <= $cyl->{$this->ax2} + 1; $v2++){
//				$vector = new Vector3;
//				$vector->{$this->ax1} = $v1;
//				$vector->{$this->ax2} = $v2;
//				if($vector->distanceSquared($base) <= $radiusSquared){
//					$this->circle[] = $vector;
//				}
//			}
//		}
		for($v1 = $center->{$this->ax1} - $radius; $v1 <= $center->{$this->ax1} + $radius; $v1++){
			$dRoot = sqrt($radiusSquared - (($v1 - $center->{$this->ax1}) ** 2));
			$start = $center->{$this->ax2} - $dRoot;
			$end = $center->{$this->ax2} + $dRoot;
			$vector = new Vector3;
			$vector->{$this->ax1} = $v1;
			if($this->isHollow){
				$vector->{$this->ax2} = $start;
				$this->circle[] = clone $vector;
				$vector->{$this->ax2} = $end;
				$this->circle[] = $vector;
			}else{
				for($v2 = $start; $v2 <= $end; $v2++){
					$vector->{$this->ax2} = $v2;
					$this->circle[] = clone $vector;
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
