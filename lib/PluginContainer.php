<?php

/**
 * Twigify.  Copyright (c) 2016-2017, Jonathan Gardiner and BambooHR.
 * Licensed under the Apache 2.0 license.
 */

namespace BambooHR\Twigify;

use PhpParser\NodeTraverser;

class PluginContainer
{

	private $plugins = [];

	function init() {
		$path=$_ENV['HOME'];
		if (!empty($path)) {
			$configPath = $path . DIRECTORY_SEPARATOR . ".twigify-plugins";
			echo "Plugin path: $configPath\n";
			if(is_dir( $configPath ) ) {
				foreach(glob( $configPath . DIRECTORY_SEPARATOR . "*.php") as $pluginFile) {
					$this->plugins[] = require $pluginFile;
				}
			}
		}
	}

	function register(NodeTraverser $nodeTraverser) {
		foreach($this->plugins as $plugin) {
			$nodeTraverser->addVisitor($plugin);
		}
	}

	function beforeShutdown() {
		$this->allPlugins("beforeShutdown");
	}

	private function allPlugins($methodName, ...$params) {
		foreach($this->plugins as $plugin) {
			if(method_exists($plugin, $methodName)) {
				call_user_func_array([$plugin, $methodName], $params);
			}
		}
	}
}