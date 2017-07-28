<?php

/*
 *
 * WorldEditArt-Epsilon
 *
 * Copyright (C) 2017 SOFe
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 */

declare(strict_types=1);

namespace LegendsOfMCPE\WorldEditArt\Epsilon;

use pocketmine\command\CommandSender;
use pocketmine\level\Location;
use pocketmine\utils\TextFormat;

abstract class BuilderSession{
	const MSG_CLASS_LOADING = 1;
	const MSG_CLASS_UPDATE = 2;
	const MSG_CLASS_INFO = 3;
	const MSG_CLASS_SUCCESS = 4;
	const MSG_CLASS_WARN = 5;
	const MSG_CLASS_ERROR = 6;

	const MSG_CLASS_COLOR_MAP = [
		BuilderSession::MSG_CLASS_LOADING => TextFormat::DARK_GRAY,
		BuilderSession::MSG_CLASS_UPDATE => TextFormat::GRAY,
		BuilderSession::MSG_CLASS_INFO => TextFormat::WHITE,
		BuilderSession::MSG_CLASS_SUCCESS => TextFormat::GREEN,
		BuilderSession::MSG_CLASS_WARN => TextFormat::YELLOW,
		BuilderSession::MSG_CLASS_ERROR => TextFormat::RED,
	];

	/** @var WorldEditArt */
	private $plugin;

	public function __construct(WorldEditArt $plugin){
		$this->plugin = $plugin;
	}

	public abstract function getOwner() : CommandSender;

	public abstract function getUniqueId() : string;

	public abstract function getLocation() : Location;

	public function getPlugin() : WorldEditArt{
		return $this->plugin;
	}

	public function msg(string $message, int $class = BuilderSession::MSG_CLASS_INFO, string $title = null){
		if(isset($title)){
			$this->getOwner()->sendMessage(TextFormat::BOLD . BuilderSession::MSG_CLASS_COLOR_MAP[$class] . $title);
		}
		foreach(explode("\n", $message) as $line){
			$this->getOwner()->sendMessage(BuilderSession::MSG_CLASS_COLOR_MAP[$class] . $line);
		}
	}

	public function hasPermission(string $permission) : bool{
		return $this->getOwner()->hasPermission($permission);
	}

	public function isAvailable() : bool{
		return true;
	}

	public function close(){
		// TODO save data
		foreach($this->plugin->getConstructionZones() as $zone){
			if($zone->getLockingSession() === $this){
				$zone->unlock();
			}
		}
	}
}
