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
use WorldEditArt\InternalConstants\Terms;
use WorldEditArt\Objects\BlockStream\BlockStream;
use WorldEditArt\Objects\Space\Space;
use WorldEditArt\User\WorldEditArtUser;
use WorldEditArt\WorldEditArt;

class RightCylindricalSpace extends Space{
	const DIRECTION_UNCHANGED = -1;
	const DIRECTION_X = 0;
	const DIRECTION_Y = 1;
	const DIRECTION_Z = 2;

	/** @var int $direction */
	private $direction;
	/** @var Vector3 $center */
	private $center;
	/** @var float $radius */
	protected $radius;
	/** @var float $height */
	protected $height;
	/** @var int $tilt1 */
	protected $tilt1;
	/** @var int $tilt2 */
	protected $tilt2;

	public function __construct(Level $level, int $direction = self::DIRECTION_Y, Vector3 $center = null, Vector3 $top = null, float $radius = -1.0){
		parent::__construct($level);
		$this->direction = $direction;
		$this->center = $center === null ? null : $center->floor();
		if($radius !== -1.0){
			$this->radius = $radius;
		}
		if($center !== null and $top !== null){
			$a0 = $this->axis0();
			$a1 = $this->axis1();
			$a2 = $this->axis2();
			$this->height = $top->{$a0} - $center->{$a0};
			$this->tilt1 = $top->{$a1} - $center->{$a1};
			$this->tilt2 = $top->{$a2} - $center->{$a2};
		}
	}

	public function getSolidBlockStream() : BlockStream{
		return new RightCylinderBlockStream($this);
	}

	public function getHollowBlockStream(int $padding = 1, int $margin = 0) : BlockStream{
		if($padding === 1 and $margin === 0){
			return new RightCylinderBlockStream($this, true);
		}
		return new HollowRightCylinderBlockStream($this, $padding, $margin);
//		$streams = [];
//		for($radius = $this->radius - $padding; $radius <= $this->radius + $margin; $radius++){
//			$streams[] = new RightCylinderBlockStream($this, true);
//		}
//		return new BatchBlockStream($streams);
	}

	public function getApproxBlockCount() : int{
		return ($this->radius ** 2) * M_PI * $this->height;
	}

	protected function isInsideImpl(Vector3 $pos) : bool{
		$delta = $pos->subtract($this->center);
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

		// check axis 0 with Pyth. thm
		return sqrt(($d1 ** 2) + ($d2 ** 2)) <= $this->radius;
	}

	public function getRadius() : float{
		$this->throwValid();
		return $this->radius;
	}

	public function setRadius(float $radius){
		$this->radius = $radius;
	}

	public function getCenter() : Vector3{
		return clone $this->center;
	}

	public function setCenter(Vector3 $center, int $direction = self::DIRECTION_UNCHANGED, bool $moveTop = true){
		$top = $this->getTop();
		$this->center = new Vector3($center->x, $center->y, $center->z);
		if($direction !== self::DIRECTION_UNCHANGED){
			$this->direction = $direction;
		}
		if(!$moveTop){
			$this->setTop($top);
		}
	}

	public function getDirection() : int{
		return $this->direction;
	}

	public function getHeight() : float{
		$this->throwValid();
		return $this->height;
	}

	public function setHeight(float $height, bool $keepTilt = true){
		if($keepTilt){
			$ratio = $height / $this->height;
			$this->tilt1 *= $ratio;
			$this->tilt2 *= $ratio;
		}
		$this->height = $height;
	}

	public function getTop() : Vector3{
		$this->throwValid();
		$top = $this->getCenter();
		$top->{$this->axis0()} += $this->height;
		$top->{$this->axis1()} += $this->tilt1;
		$top->{$this->axis2()} += $this->tilt2;
		return $top;
	}

	public function setTop(Vector3 $top){
		$a0 = $this->axis0();
		$a1 = $this->axis1();
		$a2 = $this->axis2();
		$this->height = $top->{$a0} - $this->center->{$a0};
		$this->tilt1 = $top->{$a1} - $this->center->{$a1};
		$this->tilt2 = $top->{$a2} - $this->center->{$a2};
	}

	public function isValid() : bool{
		return isset($this->center, $this->radius, $this->height, $this->tilt1, $this->tilt2);
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

	public function describe(WorldEditArtUser $user){
		return $user->translate(Terms::SPACES_CYLINDER, [
			"CENTER" => $user->translateVector($this->center),
			"TOP" => $user->translateVector($this->getTop()),
			"RADIUS" => round($this->radius, 1),
			"AXIS" => strtoupper($this->axis0()),
			"LEVEL" => $this->getLevel()->getName(),
		]);
	}
}
