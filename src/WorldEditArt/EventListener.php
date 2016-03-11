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

namespace WorldEditArt;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use WorldEditArt\User\PlayerUser;

class EventListener implements Listener{
	/** @var WorldEditArt $main */
	private $main;

	public function __construct(WorldEditArt $main){
		$this->main = $main;
		$main->getServer()->getPluginManager()->registerEvents($this, $main);
	}

	public function e_join(PlayerJoinEvent $event){
		$data = $this->main->getDataProvider()->getUserData(PlayerUser::TYPE_NAME, strtolower($event->getPlayer()->getName()));
		$user = new PlayerUser($this->main, $event->getPlayer(), $data);
		$this->main->addPlayerUser($user);
	}

	public function e_quit(PlayerQuitEvent $event){
		$user = $this->main->getPlayerUser($event->getPlayer());
		if($user !== null){
			$user->close();
		}
	}

	/**
	 * @param PlayerMoveEvent $event
	 *
	 * @priority        HIGH
	 * @ignoreCancelled true
	 */
	public function e_move(PlayerMoveEvent $event){
		$user = $this->main->getPlayerUser($event->getPlayer());
		if($user === null){
			return;
		}
		$this->main->compareZones($event->getFrom(), $event->getTo(), $entered, $left);
		// TODO: Send messages
	}
}
