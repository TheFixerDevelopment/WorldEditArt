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
use WorldEditArt\Exception\SelectionOutOfRangeException;
use WorldEditArt\InternalConstants\PermissionNames;
use WorldEditArt\InternalConstants\Terms;
use WorldEditArt\Objects\Space\Sphere\SphereSpace;
use WorldEditArt\User\WorldEditArtUser;

class SphereSubCommand extends BlockSubCommand{
	public function onRun(WorldEditArtUser $user, Block $block, CommandParser $params){
		$radius = (float) $params->nextPlain();
		if($radius <= 0){
			$user->sendUsage(Terms::COMMAND_SPHERE_USAGE);
			return null;
		}
		$selName = $params->opt("n", "default");
		$sel = $user->getSelection($selName);
		if($sel !== null){
			$user->sendMessage(Terms::COMMAND_GENERIC_SUBSTITUTING, ["SEL" => $sel->describe($user)]);
		}
		try{
			$new = new SphereSpace($block->getLevel(), $block, $radius);
		}catch(SelectionOutOfRangeException $ex){
			return $user->translate($ex->translate());
		}
		$user->setSelection($new, $selName);
		return $user->translate(Terms::COMMAND_SPHERE_SUCCESS, ["NEW" => $new->describe($user), "NAME" => $selName]);
	}

	public function getName() : string{
		return "sphere";
	}

	public function getAliases() : array{
		return ["sph"];
	}

	public function getDescription(WorldEditArtUser $user) : string{
		return $user->translate(Terms::COMMAND_SPHERE_DESCRIPTION);
	}

	public function getUsage(WorldEditArtUser $user) : string{
		return $user->translate(Terms::COMMAND_SPHERE_USAGE);
	}

	public function hasPermission(WorldEditArtUser $user){
		return $user->hasPermission(PermissionNames::COMMAND_SPHERE);
	}
}
