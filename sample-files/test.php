<?php
$js|=5 | JS_UI;
$a = array( 1,2,3,5 );
foreach( [1,2,3,4] as $int) echo $int;
foreach( $a as $b=>$c) {
	if($b == $c) {

		echo $b." ".$c;
		if(1>2) {
		?> Hello <?
		}
	} else {
		echo "Hi";
	}
}

$b = [ 1=>"Testing" ];

/* This is a test */
echo $a[0];
echo $b->Test;

// Testing

$b = implode(str_replace("ing","", "Testing"),[1,2,3]);

?>

<?= MyClass::MY_CONSTANT ?>
<?= MyConstant ?>

<html> <head>
		<style>
			.a { color: red; } 

			div { 
				margin: 5px;
			}
		</style>
	</head>
	<body>
		<p>Hi <?=$firstName?> the <?=$lastName?>,</p>
		<p>This is some sample text.</p>

		
		<div id="foo" class="<?=$test?>" >Hello</div>
		<?= __js("TEsting") ?>
		<? if( !in_array(3, $a) && true || false ) { echo "Testing"; } ?>
		<?= testing() ?>
		<?= count($a); ?>
<?= he( $a->foo ) ?>
		<?= "Testing" ?>
	</body>
</html>

