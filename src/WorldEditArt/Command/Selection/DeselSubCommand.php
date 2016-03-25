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

use WorldEditArt\Command\SubCommand;
use WorldEditArt\InternalConstants\PermissionNames;
use WorldEditArt\InternalConstants\Terms;
use WorldEditArt\User\WorldEditArtUser;

class DeselSubCommand extends SubCommand{
	public function getName() : string{
		return "desel";
	}

	public function getDescription(WorldEditArtUser $user) : string{
		return Terms::COMMAND_DESEL_DESCRIPTION;
	}

	public function getUsage(WorldEditArtUser $user) : string{
		return Terms::COMMAND_DESEL_USAGE;
	}

	public function run(WorldEditArtUser $user, string ...$args){
		$name = $args[0] ?? "default";
		if($user->getSelection($name) !== null){
			$user->setSelection(null, $name);
			return $user->translate(Terms::COMMAND_DESEL_SUCCESS, ["NAME" => $name]);
		}
		return $user->translate(Terms::COMMAND_ERROR_NO_SEL, ["NAME" => $name]);
	}

	public function hasPermission(WorldEditArtUser $user){
		return $user->hasPermission(PermissionNames::COMMAND_DESEL);
	}
}
