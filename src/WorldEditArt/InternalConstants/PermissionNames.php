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

namespace WorldEditArt\InternalConstants;

interface PermissionNames{
	const COMMAND_MAIN = "worldeditart.builder.command";
	const COMMAND_DESEL = "worldeditart.builder.desel";
	const COMMAND_SET = "worldeditart.builder.set";
	const COMMAND_SPHERE = "worldeditart.builder.select.sphere";
	const COMMAND_POS_1 = "worldeditart.builder.select.pos1";
	const COMMAND_POS_2 = "worldeditart.builder.select.pos2";

	const BYPASS_UNDER_CONSTRUCTION = "worldeditart.admin.zones.bypass.constr";
	const CREATE_UNDER_CONSTRUCTION_ZONE = "worldeditart.admin.zones.create.constr";
	const REMOVE_UNDER_CONSTRUCTION_ZONE = "worldeditart.admin.zones.remove.constr";
}
