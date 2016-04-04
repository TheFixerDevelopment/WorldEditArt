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

namespace WorldEditArt\Exception;

use WorldEditArt\InternalConstants\Terms;

class SelectionOutOfRangeException extends WorldEditArtExplainableException{
	const UNKNOWN = 0;
	const TOO_HIGH = 1;
	const TOO_LOW = 2;
	/** @var int $type */
	private $type;

	public function __construct(int $type){
		$this->type = $type;
		parent::__construct($this->translate());
	}

	public function getType() : int{
		return $this->type;
	}

	public function translate(){
		switch($this->type){
			case self::UNKNOWN:
				return Terms::COMMAND_ERROR_OUT_OF_RANGE;
			case self::TOO_HIGH:
				return Terms::COMMAND_ERROR_OUT_OF_RANGE_TOO_HIGH;
			case self::TOO_LOW:
				return Terms::COMMAND_ERROR_OUT_OF_RANGE_TOO_LOW;
		}
		return null;
	}
}
