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
use WorldEditArt\Objects\Space\Space;
use WorldEditArt\User\WorldEditArtUser;
use WorldEditArt\WorldEditArt;

class RightCylindricalSpace extends Space{
	const DIRECTION_X = 0;
	const DIRECTION_Y = 1;
	const DIRECTION_Z = 2;

	/** @var int $direction */
	private $direction;
	/** @var Vector3 $center */
	private $center;
	/** @var float $radius */
	private $radius;
	/** @var float $height */
	private $height;

	public function __construct(Level $level, int $direction = self::DIRECTION_Y, Vector3 $center = null, float $radius = -1.0, float $height = -1.0){
		parent::__construct($level);
		$this->direction = $direction;
		$this->center = $center === null ? null : $center->floor();
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
		return isset($this->center, $this->radius, $this->height);
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

	protected function handleCreationArg(string $name, string $value, WorldEditArtUser $owner = null){
		if($owner !== null){
			$dir = WorldEditArt::getDirection($loc = $owner->getLocation());
			if($loc->pitch >= 45){
				$vert = Vector3::SIDE_DOWN;
			}elseif($loc->pitch <= -45){
				$vert = Vector3::SIDE_UP;
			}else{
				$vert = $dir;
			}
		}
		switch($name){
			case "d":
			case "dir":
			case "direction":
				switch(strtolower($value)){
					case "w":
					case "west":
						$abs = Vector3::SIDE_WEST;
						break;
					case "n":
					case "north":
						$abs = Vector3::SIDE_NORTH;
						break;
					case "e":
					case "east":
						$abs = Vector3::SIDE_EAST;
						break;
					case "s":
					case "south":
						$abs = Vector3::SIDE_SOUTH;
						break;
					case "d":
					case "down":
						$abs = Vector3::SIDE_DOWN;
						break;
					case "u":
					case "up":
						$abs = Vector3::SIDE_UP;
						break;
					case
					"l":
					case "left":
						if(isset($dir)){
							$abs = WorldEditArt::rotateAntiClockwise($dir);
							break;
						}
					case "r":
					case "right":
						if(isset($dir)){
							$abs = WorldEditArt::rotateClockwise($dir);
							break;
						}
					case "b":
					case "behind":
						if(isset($vert)){
							$abs = Vector3::getOppositeSide($vert);
							break;
						}
				}
				if(!isset($abs)){
					$abs = Vector3::SIDE_UP;
				}
				$this->direction = $abs;
				break;
			case "r":
			case "rad":
			case "radius":
				$this->radius = (float) $value;
				break;
			case "h":
			case "height":
				$this->height = (float) $value;
				break;
			case "c":
			case "center":
			case "centre":
				$explosion = explode(",", $value);
				if(count($explosion) === 3){
					$this->center = new Vector3(...array_map("intval", $explosion));
				}
				break;
		}
	}

	public function handlePosCommand(){
		// TODO: Implement handlePosCommand() method.
	}
}
