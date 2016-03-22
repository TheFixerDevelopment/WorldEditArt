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

use WorldEditArt\InternalConstants\Terms;
use WorldEditArt\User\WorldEditArtUser;

class HelpSubCommand extends SubCommand{
	public function getName() : string{
		return "help";
	}

	public function getAliases() : array{
		return [""];
	}

	public function getDescription(WorldEditArtUser $user) : string{
		return $this->getMain()->translate(Terms::COMMAND_HELP_DESCRIPTION, $user->getLangs());
	}

	public function getUsage(WorldEditArtUser $user) : string{
		return $this->getMain()->translate(Terms::COMMAND_HELP_USAGE, $user->getLangs());
	}

	public function run(WorldEditArtUser $user, string ...$args){
		if(isset($args[0]) and !is_numeric($args[0])){
			$cmd = $this->getMain()->getMainCommand()->getSubCommand($args[0]);
			if($cmd !== null){
				$this->getMain()->translate(Terms::COMMAND_HELP_INDIVIDUAL, $user->getLangs(), [
					"COMMAND_NAME" => $cmd->getName(),
					"COMMAND_DESCRIPTION" => $cmd->getDetailedDescription($user),
					"COMMAND_USAGE" => $cmd->getDetailedUsage($user),
					"COMMAND_ALIASES" => implode(", ", $cmd->getAliases()),
				]);
			}
		}

		$page = (int) array_shift($args) ?? 1;
		$pageSize = 5;
		$lines = explode("\n", $this->fetchOutput($user));
		$base = ($page - 1) * $pageSize;
		$user->sendMessage(Terms::COMMAND_HELP_PAGE_HEADER, [
			"PAGE_NUMBER" => $page,
			"MAX_PAGES" => ceil(count($lines) / $pageSize),
		]);
		for($i = 0; $i < $pageSize and isset($lines[$base + $i]); $i++){
			$user->sendRawMessage($lines[$base + $i]);
		}
		return null;
	}

	private function fetchOutput(WorldEditArtUser $user) : string{
		$lines = "";
		foreach($this->getMain()->getMainCommand()->getSubCommands() as $subCmd){
			if($subCmd->hasPermission($user)){
				$lines .= $user->translate(Terms::COMMAND_HELP_PAGE_ENTRY, [
					"COMMAND_NAME" => $subCmd->getName(),
					"COMMAND_DESCRIPTION" => $subCmd->getDescription($user),
				]);
			}
		}
		return $lines;
	}

	public function hasPermission(WorldEditArtUser $user){
		return true;
	}
}
