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

namespace LegendsOfMCPE\WorldEditArt\Epsilon\UserInterface\Commands;

use LegendsOfMCPE\WorldEditArt\Epsilon\WorldEditArt;
use pocketmine\command\Command;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\plugin\Plugin;
use stdClass;

abstract class WorldEditArtCommand extends Command implements PluginIdentifiableCommand{
	/** @var WorldEditArt */
	private $plugin;

	public function __construct(WorldEditArt $plugin, string $name, string $description = "", string $usageMessage = null, array $aliases = [], string $permission = null, array $formats = ["default" => []]){
		assert($name{0} === "/");
		parent::__construct($name, $description, $usageMessage, $aliases);
		$this->plugin = $plugin;
		$this->setPermission($permission);
		if(count($formats) > 0){
			$arr = new stdClass();
			foreach($formats as $name => $format){
				$arr->{$name} = [
					"input" => ["parameters" => $format],
					"output" => new stdClass()
				];
			}
			$this->commandData["overloads"] = $arr;
		}
	}

	/**
	 * @return Plugin|WorldEditArt
	 */
	public function getPlugin() : Plugin{
		return $this->plugin;
	}

	public static function registerAll(WorldEditArt $plugin){
		$plugin->getServer()->getCommandMap()->registerAll("wea", [
			new StatusCommand($plugin),
			new ManageSessionsCommand($plugin),

			new ConstructionZoneCommand($plugin),
		]);
	}
}
