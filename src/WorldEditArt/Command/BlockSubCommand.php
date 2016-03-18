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

namespace WorldEditArt\Command;

use pocketmine\block\Block;
use WorldEditArt\InternalConstants\Terms;
use WorldEditArt\User\WorldEditArtUser;
use WorldEditArt\WorldEditArt;

abstract class BlockSubCommand extends SubCommand{
	public function run(WorldEditArtUser $user, string ...$args){
		$params = new CommandParser($args);
		$block = $user->getLocation();
		if(($anchorName = $params->optOpt("a", "default", null)) !== null){
			$block = $user->getAnchor($params);
		}
		if($block->getFloorY() > WorldEditArt::MAX_Y or $block->getFloorY() < WorldEditArt::MIN_Y){
			$user->sendMessage(Terms::COMMAND_ERROR_OUT_OF_RANGE);
			return;
		}

		$this->onRun($user, $block->getLevel()->getBlock($block->floor()), $params);
	}

	public abstract function onRun(WorldEditArtUser $user, Block $block, CommandParser $params);
}
