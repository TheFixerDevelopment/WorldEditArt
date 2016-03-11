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

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;
use WorldEditArt\Event\WorldEditArtCommandInitEvent;
use WorldEditArt\InternalConstants\PermissionNames;
use WorldEditArt\InternalConstants\Terms;
use WorldEditArt\User\WorldEditArtUser;
use WorldEditArt\WorldEditArt;

class WorldEditArtCommand extends Command implements PluginIdentifiableCommand{
	/** @var WorldEditArt $plugin */
	private $plugin;
	/** @var SubCommand[] $subCmds */
	private $subCmds;

	public function __construct(WorldEditArt $plugin){
		$this->plugin = $plugin;
		$aliases = [];
		$plugin->getServer()->getPluginManager()->callEvent($event = new WorldEditArtCommandInitEvent($plugin,
			new HelpSubCommand($plugin)
		));
		$this->subCmds = $event->getSubCommands();
		foreach($this->subCmds as $alias => $subCmd){
			$aliases[] = "/" . $alias;
		}
		parent::__construct("/", "WorldEditArt command", "Execute // for details", $aliases);
		$this->setPermission(PermissionNames::COMMAND_MAIN);
		$plugin->getServer()->getCommandMap()->register("/", $this);
	}

	public function getMain() : WorldEditArt{
		return $this->plugin;
	}

	/**
	 * @return WorldEditArt
	 */
	public function getPlugin(){
		return $this->plugin;
	}

	public function execute(CommandSender $sender, $commandLabel, array $args){
		if(!$sender->hasPermission(PermissionNames::COMMAND_MAIN)){
			$sender->sendMessage($this->getMain()->translate(Terms::COMMAND_ERROR_NO_PERM));
			return false;
		}
		if($commandLabel{0} === "/"){
			$alias = substr($commandLabel, 1);
		}else{
			$alias = array_shift($args) ?? "";
		}
		if(!isset($this->subCmds[$alias])){
			$sender->sendMessage($this->getMain()->translate(Terms::COMMAND_ERROR_NOT_FOUND));
			return false;
		}
		if(!($sender instanceof Player)){
			$sender->sendMessage("Please run this command in-game, or use a command-controlled user.");
			return false;
		}
		$user = $this->getMain()->getPlayerUser($sender);
		if(!($user instanceof WorldEditArtUser)){
			$sender->sendMessage($this->getMain()->translate(Terms::COMMAND_ERROR_USER_LOADING));
			return false;
		}
		$result = $this->subCmds[$alias]->run($user, ...$args);
		if(is_string($result)){
			$sender->sendMessage($result);
		}
		return true;
	}

	/**
	 * @return SubCommand[]
	 */
	public function getSubCommands() : array{
		return $this->subCmds;
	}

	public function getSubCommand(string $name){
		return $this->subCmds[$name] ?? null;
	}
}
