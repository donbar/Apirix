<?php

 
// Include Composer autoloader if not already done.
include 'vendor/autoload.php';

// Filename
$filename = isset($argv[1])?$argv[1]:'resume.pdf';
 
// Parse pdf file and build necessary objects.
$parser  = new \Smalot\PdfParser\Parser();
$pdf     = $parser->parseFile($filename);

// Retrieve all details from the pdf file.
$details = $pdf->getDetails();

foreach ($details as $property => $value) {
    if (is_array($value)) {
        $value = implode(', ', $value);
    }

    //echo $property . ' => ' . $value . "\n";
}

$text = $pdf->getText();

$pattern = "/([A-Za-z0-9\.\-\_\!\#\$\%\&\'\*\+\/\=\?\^\`\{\|\}]+)\@([A-Za-z0-9.-_]+)(\.[A-Za-z]{2,5})/";
preg_match_all($pattern,$text,$emails);
$new_emails = array_unique($emails[0]);

/* Replace all emails with nothing */
print "Emails:<br>";
foreach ($new_emails as $key => $val){
	$text = str_replace($val,'',$text);
	echo "$val\n";
}
print <<<qq
<br>
qq;

/* replace all punctuation with space for word separation */
$text = str_replace('.',' ',$text);
$text = str_replace(',',' ',$text);
$text = str_replace(';',' ',$text);
$text = str_replace(':',' ',$text);
$text = str_replace('-',' ',$text);

/* split the word list into an array */
$words = explode(' ',$text);

for ($t=0; $t < 1000 ; $t++){
	$words[$t] = preg_replace('/[^a-z0-9]/i', '', $words[$t]); // remove all special characters
	echo $words[$t];
	echo "<br>";
}


