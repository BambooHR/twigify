<?

if ($a==1) {
	echo "Hi";
} elseif ($a==2) {
	echo "Hello";
}

echo $this->getVar("foo_var_2");

foreach($myDict as $key=>$value) {
	echo "$key=$value<br>\n";
}

foreach($myArray as $value) {
	echo $value."<br>\n";
}

?>


