<?php

print "this code should not be ran unless you really know what you are doing";
exit ();
for($outer=748; $outer < 999; $outer++){
	$outerloop = str_pad($outer, 4, "0", STR_PAD_LEFT);	
	mkdir("/apirix/uploadstorage/$outerloop",0700);
	for($inner=0; $inner < 999; $inner++){
	print "Making $outer / $inner <br>";
	$innerloop = str_pad($inner, 4, "0", STR_PAD_LEFT);
	mkdir("/apirix/uploadstorage/$outerloop/$innerloop",0700);
	}
}
?>