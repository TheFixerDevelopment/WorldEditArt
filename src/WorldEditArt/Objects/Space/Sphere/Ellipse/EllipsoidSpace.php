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

namespace WorldEditArt\Objects\Space\Sphere\Ellipse;

use pocketmine\level\Position;
use pocketmine\math\Vector3;
use WorldEditArt\Objects\BlockStream\BlockStream;
use WorldEditArt\Objects\Space\Space;
use WorldEditArt\User\WorldEditArtUser;

class EllipsoidSpace extends Space{
	/** @var Vector3 $center */
	private $center;
	/** @var float $xRadius */
	private $xRadius;
	/** @var float $yRadius */
	private $yRadius;
	/** @var float $zRadius */
	private $zRadius;

	public function __construct(Position $center, float $xRadius, float $yRadius, float $zRadius){
		parent::__construct($center->getLevel());
		$this->center = $center->floor();
		$this->xRadius = $xRadius;
		$this->yRadius = $yRadius;
		$this->zRadius = $zRadius;
	}

	public function getSolidBlockStream() : BlockStream{
		return new SolidEllipsoidBlockStream($this);
	}

	public function getHollowBlockStream(int $padding = 1, int $margin = 0) : BlockStream{
		return new HollowEllipsoidBlockStream($this);
	}

	public function getApproxBlockCount() : int{
		return 4 / 3 * M_PI * $this->xRadius * $this->yRadius * $this->zRadius;
	}

	protected function isInsideImpl(Vector3 $pos) : bool{
		$diff = $pos->subtract($this->center);
		$norm = ($diff->x ** 2 - $this->xRadius ** 2) + ($diff->y ** 2 - $this->yRadius ** 2) + ($diff->z ** 2 - $this->zRadius ** 2);
		return $norm <= 1;
	}

	public function getCenter() : Vector3{
		return $this->center;
	}

	public function getXRadius(){
		return $this->xRadius;
	}

	public function getYRadius(){
		return $this->yRadius;
	}

	public function getZRadius() : float{
		return $this->zRadius;
	}

	protected function handleCreationArg(string $name, string $value, WorldEditArtUser $owner){
		switch($name){
			case "xr":
			case "xrad":
			case "xradius":
			case "x-rad":
			case "x-radius":
				$rad = "xRadius";
				break;
			case "yr":
			case "yrad":
			case "yradius":
			case "y-rad":
			case "y-radius":
				$rad = "yRadius";
				break;
			case "zr":
			case "zrad":
			case "zradius":
			case "z-rad":
			case "z-radius":
				$rad = "zRadius";
				break;
		}
		if(isset($rad)){
			$this->$rad = (float) $value;
		}
	}

	public function handlePosCommand(){
		// TODO: Implement handlePosCommand() method.
	}
}
