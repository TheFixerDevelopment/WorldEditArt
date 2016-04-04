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

use pocketmine\block\Block;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use WorldEditArt\Exception\SelectionOutOfRangeException;
use WorldEditArt\InternalConstants\Terms;
use WorldEditArt\Objects\BlockStream\BlockStream;
use WorldEditArt\Objects\Space\Space;
use WorldEditArt\User\WorldEditArtUser;
use WorldEditArt\WorldEditArt;

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
			$this->setRadius($radius);
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
		if($center->y + $this->radius > WorldEditArt::MAX_Y){
			throw new SelectionOutOfRangeException(SelectionOutOfRangeException::TOO_HIGH);
		}
		if($center->y - $this->radius < WorldEditArt::MIN_Y){
			throw new SelectionOutOfRangeException(SelectionOutOfRangeException::TOO_LOW);
		}
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
		$this->testOutOfRange();
	}

	public function isValid() : bool{
		return isset($this->center, $this->radius);
	}

	public function describe(WorldEditArtUser $user){
		$undefined = $user->translate(Terms::PHRASE_UNDEFINED);
		return $user->translate(Terms::SPACES_SPHERE, [
			"CENTER" => isset($this->center) ? $user->translateVector($this->center) : $undefined,
			"RADIUS" => $this->radius ?? $undefined,
			"LEVEL" => $this->getLevel()->getName(),
		]);
	}

	public function testOutOfRange(){
		if($this->center->y + $this->radius > WorldEditArt::MAX_Y){
			throw new SelectionOutOfRangeException(SelectionOutOfRangeException::TOO_HIGH);
		}
		if($this->center->y - $this->radius < WorldEditArt::MIN_Y){
			throw new SelectionOutOfRangeException(SelectionOutOfRangeException::TOO_LOW);
		}
	}

	/**
	 * @deprecated
	 *
	 * @param string                $name
	 * @param string                $value
	 * @param WorldEditArtUser|null $owner
	 */
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

	public function handlePosCommand(string $propertyName, Block $block) : bool{
		// TODO: Implement handlePosCommand() method.
	}
}
