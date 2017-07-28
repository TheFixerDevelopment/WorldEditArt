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

class Consts{
	const PLUGIN_NAME = "WorldEditArt-Epsilon";

	const CONFIG_SESSION_IMPLICIT = "implicit builder session";
	const CONFIG_SESSION_GLOBAL_PASSPHRASE = "builder session global passphrase";

	const CONFIG_VERSION = "DO NOT EDIT THIS LINE";
	const CONFIG_VERSION_VALUE = 1;

	const PERM_STATUS = "worldeditart.status";
	const PERM_SESSION_START = "worldeditart.builder.session.start";
	const PERM_SESSION_CLOSE = "worldeditart.builder.session.close";
	const PERM_CZONE_BUILDER_VIEW = "worldeditart.builder.czone.view";
	const PERM_CZONE_BUILDER_LOCK_EDIT = "worldeditart.builder.czone.lock.edit";
	const PERM_CZONE_BUILDER_LOCK_BLOCKS = "worldeditart.builder.czone.lock.blocks";
	const PERM_CZONE_BUILDER_LOCK_ENTRY = "worldeditart.builder.czone.lock.entry";
	const PERM_CZONE_BUILDER_UNLOCK_SELF = "worldeditart.builder.czone.unlockself";
	const PERM_CZONE_BUILDER_UNLOCK_OTHER = "worldeditart.builder.czone.unlockother";
	const PERM_CZONE_BUILDER_MANAGE_ANY = [
		Consts::PERM_CZONE_BUILDER_VIEW,
		Consts::PERM_CZONE_BUILDER_LOCK_EDIT,
		Consts::PERM_CZONE_BUILDER_LOCK_BLOCKS,
		Consts::PERM_CZONE_BUILDER_LOCK_EDIT,
		Consts::PERM_CZONE_BUILDER_UNLOCK_SELF,
		Consts::PERM_CZONE_BUILDER_UNLOCK_OTHER
	];
	const PERM_CZONE_BUILDER_BLOCKS = "worldeditart.builder.czone.blocks";
	const PERM_CZONE_BUILDER_ENTRY = "worldeditart.builder.czone.entry";
}
