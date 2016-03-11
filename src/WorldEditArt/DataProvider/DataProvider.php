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

namespace WorldEditArt\DataProvider;

use WorldEditArt\DataProvider\Model\UserData;
use WorldEditArt\DataProvider\Model\Zone;

interface DataProvider{
	public function getUserData(string $type, string $name) : UserData;

	public function saveUserData(UserData $data);

	/**
	 * @return Zone[]
	 */
	public function getZones() : array;

	public function isZoneExistent(string $name) : bool;

	public function addZone(Zone $zone) : bool;

	public function removeZone(string $zoneName) : bool;

	/**
	 * @param string $zoneName
	 *
	 * @return Zone|null
	 */
	public function getZone(string $zoneName);
}
