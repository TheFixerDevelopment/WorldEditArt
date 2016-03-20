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

namespace WorldEditArt\Objects\Space\Sphere;

use pocketmine\level\Level;
use pocketmine\math\Vector3;
use WorldEditArt\Objects\BlockStream\BlockStream;
use WorldEditArt\Objects\Space\Space;
use WorldEditArt\User\WorldEditArtUser;

class SphereSpace extends Space{
	/** @var Vector3 $center */
	private $center;
	/** @var float $radius */
	private $radius;
	/** @var float $radiusSquared */
	private $radiusSquared;

	public function __construct(Level $level, Vector3 $center = null, float $radius = -1.0){
		parent::__construct($level);
		$this->center = $center;
		if($radius > 0){
			$this->radius = $radius;
			$this->radiusSquared = $radius ** 2;
		}
	}

	public function getSolidBlockStream() : BlockStream{
		return new SolidSphereBlockStream($this);
	}

	public function getHollowBlockStream(int $padding = 1, int $margin = 0) : BlockStream{
		return new HollowSphereBlockStream($this, $padding, $margin);
	}

	public function getApproxBlockCount() : int{
		return 4 / 3 * M_PI * ($this->radius ** 3);
	}

	protected function isInsideImpl(Vector3 $pos) : bool{
		$this->throwValid();
		return $this->center->distanceSquared($pos) <= $this->radiusSquared;
	}

	public function getCenter() : Vector3{
		return $this->center;
	}

	public function setCenter(Vector3 $center){
		$this->center = $center;
	}

	public function isRadiusSet() : bool{
		return isset($this->radius);
	}

	public function getRadius() : float{
		$this->throwValid();
		return $this->radius;
	}

	public function getRadiusSquared() : float{
		return $this->radiusSquared;
	}

	public function setRadius(float $radius){
		$this->radius = $radius;
		$this->radiusSquared = $radius ** 2;
	}

	public function isValid() : bool{
		return isset($this->center, $this->radius);
	}

	protected function handleCreationArg(string $name, string $value, WorldEditArtUser $owner = null){
		switch($name){
			case "c":
			case "center":
			case "centre":
				$explosion = explode(",", $value);
				if(count($explosion) === 3){
					$this->center = new Vector3(...array_map("intval", $explosion));
				}
				break;
			case "r":
			case"rad":
			case"radius":
				$this->setRadius((float) $value);
				break;
		}
	}

	public function handlePosCommand(){
		// TODO: Implement handlePosCommand() method.
	}
}
