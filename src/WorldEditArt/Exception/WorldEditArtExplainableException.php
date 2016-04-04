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

use WorldEditArt\User\WorldEditArtUser;

class WorldEditArtExplainableException extends \RuntimeException{
	/** @var string $id */
	private $id;
	/** @var string[] $params */
	private $params;

	public function __construct(string $id, array $params = []){
		$this->id = $id;
		$this->params = $params;
		parent::__construct($id);
	}

	public function explain(WorldEditArtUser $user){
		$user->sendMessage($this->id, $this->params);
	}
}
