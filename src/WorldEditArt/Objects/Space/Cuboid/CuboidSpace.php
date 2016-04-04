<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

namespace WorldEditArt\Objects\Space\Cuboid;

use pocketmine\block\Block;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use WorldEditArt\Exception\SelectionOutOfRangeException;
use WorldEditArt\InternalConstants\Terms;
use WorldEditArt\Objects\BlockStream\BatchBlockStream;
use WorldEditArt\Objects\BlockStream\BlockStream;
use WorldEditArt\Objects\Space\Space;
use WorldEditArt\User\WorldEditArtUser;
use WorldEditArt\WorldEditArt;

class CuboidSpace extends Space{
	const PROP_POS_1 = "pos1";
	const PROP_POS_2 = "pos2";

	/** @var Vector3|null $pos1 */
	private $pos1;
	/** @var Vector3|null $pos2 */
	private $pos2;

	public function __construct(Level $level, Vector3 $pos1 = null, Vector3 $pos2 = null){
		parent::__construct($level);
		if($pos1 !== null){
			$this->setPos1($pos1);
		}
		if($pos2 !== null){
			$this->setPos2($pos2);
		}
	}

	/**
	 * @return Vector3|null
	 */
	public function getPos1(){
		return $this->pos1;
	}

	/**
	 * @return Vector3|null
	 */
	public function getPos2(){
		return $this->pos2;
	}

	public function setPos1(Vector3 $pos){
		if($pos instanceof Position and $pos->getLevel() !== $this->getLevel()){
			throw new \InvalidArgumentException("Each space can only be in one level");
		}
		$pos = $pos->floor();
		if($pos->y > WorldEditArt::MAX_Y){
			throw new SelectionOutOfRangeException(SelectionOutOfRangeException::TOO_HIGH);
		}
		if($pos->y < WorldEditArt::MIN_Y){
			throw new SelectionOutOfRangeException(SelectionOutOfRangeException::TOO_LOW);
		}
		$this->pos1 = $pos;
	}

	public function setPos2(Vector3 $pos){
		if($pos instanceof Position and $pos->getLevel() !== $this->getLevel()){
			throw new \InvalidArgumentException("Each space can only be in one level");
		}
		if($pos->y > WorldEditArt::MAX_Y){
			throw new SelectionOutOfRangeException(SelectionOutOfRangeException::TOO_HIGH);
		}
		if($pos->y < WorldEditArt::MIN_Y){
			throw new SelectionOutOfRangeException(SelectionOutOfRangeException::TOO_LOW);
		}
		$this->pos2 = $pos->floor();
	}

	public function getSolidBlockStream() : BlockStream{
		return new SolidCuboidBlockStream($this);
	}

	public function getHollowBlockStream(int $padding = 1, int $margin = 0) : BlockStream{
		if($padding !== 1 or $margin !== 0){
			$streams = [];
			for($i = 1; $i <= $padding; $i++){
				$streams[] = new HollowCuboidBlockStream($this->grow(-$i, -$i, -$i, -$i, -$i, -$i));
			}
			for($i = 1; $i <= $margin; $i++){
				$streams[] = new HollowCuboidBlockStream($this->grow($i, $i, $i, $i, $i, $i));
			}
			return new BatchBlockStream($streams);
		}
		return new HollowCuboidBlockStream($this);
	}

	protected function isInsideImpl(Vector3 $pos) : bool{
		return (
			min($this->pos1->x, $this->pos2->x) <= $pos->x and
			max($this->pos1->x, $this->pos2->x) >= $pos->x and
			min($this->pos1->y, $this->pos2->y) <= $pos->y and
			max($this->pos1->y, $this->pos2->y) >= $pos->y and
			min($this->pos1->z, $this->pos2->z) <= $pos->z and
			max($this->pos1->z, $this->pos2->z) >= $pos->z
		);
	}

	public function getApproxBlockCount() : int{
		$this->throwValid();
		return abs(
			($this->pos1->x - $this->pos2->x + 1) *
			($this->pos1->y - $this->pos2->y + 1) *
			($this->pos1->z - $this->pos2->z + 1)
		);
	}

	private function grow(int $x1, int $y1, int $z1, int $x2, int $y2, int $z2){
		return new CuboidSpace($this->getLevel(), new Vector3(
			min($this->pos1->x, $this->pos2->x) - $x1,
			min($this->pos1->y, $this->pos2->y) - $y1,
			min($this->pos1->z, $this->pos2->z) - $z1
		), new Vector3(
			max($this->pos1->x, $this->pos2->x) + $x2,
			max($this->pos1->y, $this->pos2->y) + $y2,
			max($this->pos1->z, $this->pos2->z) + $z2
		));
	}

	public function isValid() : bool{
		return $this->pos1 !== null and $this->pos2 !== null;
	}

	public function describe(WorldEditArtUser $user){
		return $user->translate(Terms::SPACES_CUBOID, [
			"POS_1" => isset($this->pos1) ? $user->translateVector($this->pos1) : $user->translate(Terms::PHRASE_UNDEFINED),
			"POS_2" => isset($this->pos2) ? $user->translateVector($this->pos2) : $user->translate(Terms::PHRASE_UNDEFINED),
			"LEVEL" => $this->getLevel()->getName(),
		]);
	}

	public function handlePosCommand(string $propertyName, Block $block) : bool{
		switch($propertyName){
			case self::PROP_POS_1:
				$this->setPos1($block->floor());
				return true;
			case self::PROP_POS_2:
				$this->setPos2($block->floor());
				return true;
		}
		return false;
	}

	/**
	 * @deprecated
	 *
	 * @param WorldEditArtUser $owner
	 * @param string           $name
	 * @param string           $value
	 */
	protected function handleCreationArg(WorldEditArtUser $owner, string $name, string $value){
		if($name === "1"){
			$posField = "pos1";
		}elseif($name === "2"){
			$posField = "pos2";
		}
		if(isset($posField)){
			$explosion = explode(",", $value);
			if(count($explosion) === 3){
				$this->$posField = new Vector3(...array_map("intval", $explosion));
			}
		}
	}
}
