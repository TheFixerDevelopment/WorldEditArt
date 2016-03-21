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

namespace WorldEditArt\Objects\Space\Cylinder\Right\Cone;

use pocketmine\math\Vector3;
use WorldEditArt\Objects\Space\Cylinder\Right\RightCylindricalSpace;

class RightConeSpace extends RightCylindricalSpace{
	public function getApproxBlockCount() : int{
		return parent::getApproxBlockCount() / 3;
	}

	protected function isInsideImpl(Vector3 $pos) : bool{
		$delta = $pos->subtract($this->getCenter());
		$a0 = $this->axis0();
		$a1 = $this->axis1();
		$a2 = $this->axis2();

		// check axis 0
		if($delta->{$a0} < 0 or $delta->{$a0} > $this->height){
			return false;
		}

		// normalize axis 1 and axis 2
		$prop = $delta->{$a0} / $this->height;
		$d1 = $delta->{$a1} - $this->tilt1 * $prop;
		$d2 = $delta->{$a2} - $this->tilt2 * $prop;

		// calculate radius at this height ($delta->{$a0})
		$maxRadius = $this->radius * ($this->height - $delta->{$a0}) / $this->height;

		// check axis 0 with Pyth. thm
		return sqrt(($d1 ** 2) + ($d2 ** 2)) <= $maxRadius;
	}
}
