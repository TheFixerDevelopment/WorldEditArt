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

namespace WorldEditArt\Objects\Space;

use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use WorldEditArt\Command\CommandParser;
use WorldEditArt\Objects\BlockStream\BlockStream;
use WorldEditArt\User\WorldEditArtUser;

abstract class Space{
	/** @var Level $level */
	private $level;

	protected function __construct(Level $level){
		$this->level = $level; // TODO handle LevelUnloadEvent, garbage spaces and streams using that level
	}

	public function getLevel() : Level{
		return $this->level;
	}

	public abstract function getSolidBlockStream() : BlockStream;

	/**
	 * @param int $padding the thickness inside, default 1
	 * @param int $margin  the thickness outside, default 0
	 *
	 * @return BlockStream
	 */
	public abstract function getHollowBlockStream(int $padding = 1, int $margin = 0) : BlockStream;

	public abstract function getApproxBlockCount() : int;

	public function isInside(Vector3 $pos) : bool{
		if($pos instanceof Position and $pos->level !== $this->level){
			return false;
		}
		return $this->isInsideImpl($pos);
	}

	protected abstract function isInsideImpl(Vector3 $pos) : bool;

	public function isValid() : bool{
		return true;
	}

	public function throwValid(){
		if(!$this->isValid()){
			throw new \InvalidStateException("Attempt to call method on an invalid Space object");
		}
	}

	public abstract function handlePosCommand();

	public static function create(Level $level, CommandParser $args = null, WorldEditArtUser $owner = null){
		$instance = new static($level);
		if($args !== null){
			foreach($args->getOpts() as $opt => $value){
				$instance->handleCreationArg($opt, $value, $owner);
			}
		}
	}

	protected function handleCreationArg(string $name, string $value, WorldEditArtUser $owner = null){
	}
}
