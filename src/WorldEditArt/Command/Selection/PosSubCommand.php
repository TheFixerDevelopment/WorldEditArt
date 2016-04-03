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

namespace WorldEditArt\Command\Selection;

use pocketmine\block\Block;
use WorldEditArt\Command\BlockSubCommand;
use WorldEditArt\Command\CommandParser;
use WorldEditArt\User\WorldEditArtUser;
use WorldEditArt\WorldEditArt;

class PosSubCommand extends BlockSubCommand{
	public function __construct(WorldEditArt $main, string $spaceClass){
		parent::__construct($main);
	}

	public function onRun(WorldEditArtUser $user, Block $block, CommandParser $params){
		// TODO: Implement onRun() method.
	}

	public function getName() : string{
		// TODO: Implement getName() method.
	}

	public function getDescription(WorldEditArtUser $user) : string{
		// TODO: Implement getDescription() method.
	}

	public function getUsage(WorldEditArtUser $user) : string{
		// TODO: Implement getUsage() method.
	}

	public function hasPermission(WorldEditArtUser $user){
		// TODO: Implement hasPermission() method.
	}
}
