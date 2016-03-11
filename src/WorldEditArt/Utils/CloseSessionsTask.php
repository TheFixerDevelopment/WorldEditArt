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

namespace WorldEditArt\Utils;

class CloseSessionsTask extends WorldEditArtTask{
	public function onRun($t){
		foreach($this->getMain()->playerUsers as $k => $user){
			if($user->getCloseTime() >= (int) ($this->getMain()->getConfig()->getNested("Session.Linger", 10) * 60)){
				unset($this->getMain()->playerUsers[$k]);
			}
		}
	}
}
