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

namespace WorldEditArt\Command;

use WorldEditArt\User\WorldEditArtUser;
use WorldEditArt\WorldEditArt;

abstract class SubCommand{
	/** @var WorldEditArt $main */
	private $main;

	public function __construct(WorldEditArt $main){
		$this->main = $main;
	}

	public function getMain() : WorldEditArt{
		return $this->main;
	}

	public abstract function getName() : string;

	public function getAliases() : array{
		return [];
	}

	public abstract function getDescription(WorldEditArtUser $user) : string;

	public function getDetailedDescription(WorldEditArtUser $user) : string{
		return $this->getDescription($user);
	}

	public abstract function getUsage(WorldEditArtUser $user) : string;

	public function getDetailedUsage(WorldEditArtUser $user) : string{
		return $this->getUsage($user);
	}

	public abstract function run(WorldEditArtUser $user, string ...$args);

	/**
	 * @param WorldEditArtUser $user
	 *
	 * @return bool
	 */
	public abstract function hasPermission(WorldEditArtUser $user);
}
