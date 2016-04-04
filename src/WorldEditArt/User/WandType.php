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

namespace WorldEditArt\User;

use pocketmine\block\Block;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use WorldEditArt\Command\BlockSubCommand;
use WorldEditArt\Command\CommandParser;

class WandType{
	const ACTION_LEFT_CLICK_BLOCK = 1;
	const ACTION_RIGHT_CLICK_BLOCK = 2;
	const ACTION_CLICK_BOTH = self::ACTION_LEFT_CLICK_BLOCK | self::ACTION_RIGHT_CLICK_BLOCK;

	/** @var Item $item */
	private $item;
	/** @var bool $checkDamage */
	private $checkDamage;
	/** @var int $actionType */
	private $actionType;
	/** @var string $commandName */
	private $commandName;

	public function __construct(Item $item, string $commandName, int $actionType, bool $checkDamage){
		$this->item = $item;
		$this->commandName = $commandName;
		$this->actionType = $actionType;
		$this->checkDamage = $checkDamage;
	}

	public function getItem() : Item{
		return $this->item;
	}

	public function getCommandName() : string{
		return $this->commandName;
	}

	public function touch(WorldEditArtUser $user, Block $block, int $actionType, Item $item, CommandParser $commandParser){
		if($item->equals($this->item, $this->checkDamage) and
			($actionType === PlayerInteractEvent::LEFT_CLICK_BLOCK and ($this->actionType & self::ACTION_LEFT_CLICK_BLOCK) or
				$actionType === PlayerInteractEvent::RIGHT_CLICK_BLOCK and ($this->actionType & self::ACTION_RIGHT_CLICK_BLOCK))
		){
			$cmd = $user->getMain()->getMainCommand()->getSubCommand($this->commandName);
			if($cmd instanceof BlockSubCommand){
				$cmd->onRun($user, $block, $commandParser);
			}
		}
	}
}
