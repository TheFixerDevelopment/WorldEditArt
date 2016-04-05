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
 * @author LegendsOfMCPE Team
 */

namespace WorldEditArt\User;

use pocketmine\level\Location;
use pocketmine\permission\PermissionAttachment;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use WorldEditArt\DataProvider\Model\UserData;
use WorldEditArt\WorldEditArt;

class PlayerUser extends WorldEditArtUser{
	const TYPE_NAME = "WorldEditArt.PlayerUser";

	/** @var Player $player */
	private $player;

	public function __construct(WorldEditArt $main, Player $player, UserData $data){
		parent::__construct($main, $data);
		$this->player = $player;
	}

	public function getType() : string{
		return "WorldEditArt.User";
	}

	public function getName() : string{
		return strtolower($this->player->getName());
	}

	public function sendRawMessage(string $message){
		$this->player->sendMessage($message);
	}

	public function getLocation() : Location{
		return $this->player->getLocation();
	}

	public function isPermissionSet($name){
		return $this->player->isPermissionSet($name);
	}

	public function hasPermission($name){
//		if(WorldEditArt::isDebug()){
//			$pluginMgr = $this->getMain()->getServer()->getPluginManager();
//			assert($pluginMgr->getPermission($name) !== null);
//		}
		return $this->player->hasPermission($name);
	}

	public function addAttachment(Plugin $plugin, $name = null, $value = null){
		return $this->player->addAttachment($plugin, $name, $value);
	}

	public function removeAttachment(PermissionAttachment $attachment){
		$this->player->removeAttachment($attachment);
	}

	public function recalculatePermissions(){
		$this->player->recalculatePermissions();
	}

	public function getEffectivePermissions(){
		return $this->player->getEffectivePermissions();
	}

	public function isOp(){
		return $this->player->isOp();
	}

	public function setOp($value){
		$this->player->setOp($value);
	}
}
