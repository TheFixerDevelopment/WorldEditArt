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

namespace WorldEditArt\Lang;

use WorldEditArt\WorldEditArt;
use const WorldEditArt\LANG_EXTENSION;
use const WorldEditArt\LANG_SUFFIX;
use const WorldEditArt\NO_XML;
use const WorldEditArt\XML_SUPPORTED;

if(WorldEditArt::isDebug()){
	function xml_parser_create(){
		if(!XML_SUPPORTED){
			throw new \Exception("xml_parser_create() doesn't exist");
		}
		\xml_parser_create(...func_get_args());
	}
}

class LanguageManager{
	/** @var WorldEditArt $main */
	private $main;
	/** @var Translation[][] $translations */
	private $translations = [];
	/** @var LanguageFileParser[] $langs */
	private $langs = [];

	public function __construct(WorldEditArt $main){
		$this->main = $main;

		$dir = $main->getDataFolder() . "lang/";
		if(!is_dir($dir)){
			mkdir($dir);
			if(NO_XML){
				if(XML_SUPPORTED){
					$main->getLogger()->notice("Good news! Your server has the XML extension. XML language files are going to be used.");
				}else{
					$main->getLogger()->notice("Your server doesn't have the XML extension. JSON language files are going to be used.");
				}
			}
			$it = new \DirectoryIterator($main->getResourceFolder("lang/"));
			/** @var \SplFileInfo $file */
			foreach($it as $file){
				if($file->getExtension() === LANG_EXTENSION){
					copy($file->getPathname(), $dir . $file->getBasename());
				}
			}
		}

		if(XML_SUPPORTED){
			foreach(new \DirectoryIterator($dir) as $file){
				if($file->getExtension() === "xml"){
					$this->main->getLogger()->info("Loading {$file->getBasename()}...");
					$this->parseXML(file_get_contents($file->getPathname()));
				}
			}
		}else{
			foreach(new \DirectoryIterator($dir) as $file){
				if($file->getExtension() === "json"){
					$fileName = $file->getPathname();
					$langName = substr($fileName, 0, -9);
					$this->main->getLogger()->info("Loading $langName...");
					$this->parseJSON(file_get_contents($fileName));
				}
			}
		}

		if(!isset($this->langs["en"])){
			$fileName = XML_SUPPORTED ? "en.xml" : "en.xml.json";
			$this->main->getLogger()->alert("$fileName missing in lang folder! Default $fileName will be loaded as fallback language.");
			if(XML_SUPPORTED){
				$this->parseXML($this->main->getResourceContents("lang/$fileName"), "en");
			}else{
				$this->parseJSON($this->main->getResourceContents("lang/$fileName"), "en");
			}
		}
		if(XML_SUPPORTED){
			$this->parseXML($this->main->getResourceContents("lang/en.xml"), "/backup");
		}else{
			$this->parseJSON($this->main->getResourceContents("lang/en.xml.json"), "/backup");
		}
	}

	private function parseXML(string $xmlData, $forceName = null){
		$parser = new LanguageFileParser($xmlData, $this->main);
		$name = $forceName ?? $parser->getName();
		foreach($parser->getValues() as $id => $term){
			if(!isset($this->translations[$id][$name])){
				foreach($parser->getConstants() as $constant => $value){
					$term->define($constant, $value);
				}
				$this->translations[$id][$name] = $term;
			}
		}

		$parser->finalize();
		$this->langs[$name] = $parser;
	}

	private function parseJSON(string $jsonData, $forceName = null){
		$parser = new LanguageFileParser($jsonData, $this->main, false);
		$name = $forceName ?? $parser->getName();
		foreach($parser->getValues() as $id => $term){
			if(!isset($this->translations[$id][$name])){
				foreach($parser->getConstants() as $constant => $value){
					$term->define($constant, $value);
				}
				$this->translations[$id][$name] = $term;
			}
		}

		$parser->finalize();
		$this->langs[$name] = $parser;
	}

	public function getTerm(string $id, array $langs = [], array $vars = []) : string{
		if(isset($this->translations[$id])){
			$trans = $this->translations[$id];
			foreach($langs as $lang){
				if(isset($trans[$lang])){
					return $trans[$lang]->toString($vars);
				}
			}
			if(isset($trans["/backup"])){
				return $trans["/backup"]->toString($vars);
			}else{
				$this->main->getLogger()->error("Failed to find undefined string '$id' (from languages: " . implode(", ", $langs) . ", default en)");
				return $id;
			}
		}else{
			$this->main->getLogger()->error("Failed to find undefined string '$id'");
			return $id;
		}
	}
}
