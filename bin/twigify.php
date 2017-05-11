<?php
/**
 * Twigify.  Copyright (c) 2016-2017, Jonathan Gardiner and BambooHR.
 * Licensed under the Apache 2.0 license.
 */

// Deals with installation inside /vendor or out.
foreach ([__DIR__ . '/../../../autoload.php', __DIR__ . '/../vendor/autoload.php'] as $file) {
	if (file_exists($file)) {
		require $file;
		break;
	}
}

use BambooHR\Twigify\Converter;
use BambooHR\Twigify\JinjaConverter;
use BambooHR\Twigify\Exceptions\ConvertException;
use BambooHR\Twigify\SwitchToIfConverter;

$fileName = "";

$converter = new Converter();
for($i=1;$i<count($_SERVER['argv']); ++$i) {
	$arg = $_SERVER['argv'][$i];
	if($arg=='-j' || $arg=='jinja') {
		$converter = new JinjaConverter();
	} else {
		$fileName = $arg;
	}
}

if(count($_SERVER['argv'])<2 || !$fileName) {
	echo "Usage: php twiggify.php [-j|--jinja] [file]\n";
	exit(1);
}
 
$factory = new PhpParser\ParserFactory();
$parser = $factory->create(PhpParser\ParserFactory::PREFER_PHP5);
$statements = $parser->parse( file_get_contents( $fileName ));

$plugins = new \BambooHR\Twigify\PluginContainer();
$plugins->init();

$traverser = new \PhpParser\NodeTraverser();
$traverser->addVisitor( new SwitchToIfConverter() );
$plugins->register($traverser);

try {
	$statements = $traverser->traverse($statements);
	echo $converter->prettyPrint( $statements );
}  
catch(ConvertException $e) {
	echo $e->getMessage()."\n";
}


$errors = $converter->getErrors();
if (count($errors)>0) {
	echo "\n\nErrors:\n";
	foreach ($converter->getErrors() as $error) {
		echo " - $error\n";
	}
}

$plugins->beforeShutdown();

