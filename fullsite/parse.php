<?php
 include 'vendor/autoload.php';

 
/*Name of the document file*/
$document = "incoming/".$_GET['filename'];
$newfilename = $_GET['filename'];

preg_replace('/^[\w]$/', '', $newfilename);
rename($document,'uploadstorage/'.$newfilename);
echo "new file name is " . $newfilename;

$document =  'uploadstorage/'.$newfilename;
 
 function extractpdftext($filename){
// Parse pdf file and build necessary objects.
	$parser  = new \Smalot\PdfParser\Parser();
	$pdf     = $parser->parseFile($filename);
	$text = $pdf->getText();

	/* Extract email addresses: I don't know why, was just fun to write */
	// $pattern = "/([A-Za-z0-9\.\-\_\!\#\$\%\&\'\*\+\/\=\?\^\`\{\|\}]+)\@([A-Za-z0-9.-_]+)(\.[A-Za-z]{2,5})/";
	// preg_match_all($pattern,$text,$emails);
	// $new_emails = array_unique($emails[0]);

	/* Replace all emails with nothing */
	// print "Emails:<br>";
	// foreach ($new_emails as $key => $val){
		// $text = str_replace($val,'',$text);
		// echo "$val\n";
	// }

	/* replace all punctuation with space for word separation */
	$text = str_replace('.',' ',$text);
	$text = str_replace(',',' ',$text);
	$text = str_replace(';',' ',$text);
	$text = str_replace(':',' ',$text);
	$text = str_replace('-',' ',$text);
	$text = str_replace('HYPERLINK','',$text);

	/* split the word list into an array */
	$words = explode(' ',$text);
	return $words;
	}
 
 function extracttxttext($filename){
	$outtext = file_get_contents($filename, FILE_IGNORE_NEW_LINES);
	$outtext = explode(' ',$outtext);
	return $outtext;
	}
 
 function extractdoctext($filename) {
        $fileHandle = fopen($filename, "r");
        $line = @fread($fileHandle, filesize($filename));   
        $lines = explode(chr(0x0D),$line);
        $outtext = "";
        foreach($lines as $thisline)
          {
            $pos = strpos($thisline, chr(0x00));
            if (($pos !== FALSE)||(strlen($thisline)==0))
              {
              } else {
                $outtext .= $thisline." ";
              }
          }
	     $outtext = preg_replace("/[\s\,\.\-\n\r\t@\/\_\(\)]/"," ",$outtext);
         $outtext = preg_replace("/[^a-zA-Z0-9\s\,\.\-\n\r\t@\/\_\(\)]/","",$outtext);
        return $outtext;
    }
 
/**Function to extract text*/
function extractdocxtext($filename) {
    //Check for extension
    $ext = end(explode('.', $filename));
 
    //if its docx file
    if($ext == 'docx')
    $dataFile = "word/document.xml";
    //else it must be odt file
    if($ext == 'odt')
    $dataFile = "content.xml";     
       
    //Create a new ZIP archive object
    $zip = new ZipArchive;
 
    // Open the archive file
    if (true === $zip->open($filename)) {
        // If successful, search for the data file in the archive
        if (($index = $zip->locateName($dataFile)) !== false) {
            // Index found! Now read it to a string
            $text = $zip->getFromIndex($index);
            // Load XML from a string
            // Ignore errors and warnings
            $xml = DOMDocument::loadXML($text, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
            // Remove XML formatting tags and return the text
            return strip_tags($xml->saveXML());
        }
        //Close the archive file
        $zip->close();
    }
 
    // In case of failure return a message
    return "File not found";
}


$ext = end(explode('.', $document));

if($ext == "pdf"){
	echo "Parsing PDF!<br>";
	$mytext = extractpdftext($document);
}

if($ext == "txt"){
	echo "Parsing TXT!<br>";
	$mytext = extracttxttext($document);
 
}
if($ext == "doc"){ 
	echo "Parsing DOC!<br>";
	$mytext = explode(' ',extractdoctext($document));
	$merge = array_search('MERGEFORMAT',$mytext);
	 if ($merge > 0){
		array_splice($mytext, $merge);
	}
}
if($ext == "docx"){ 
	echo "Parsing DOCX!<br>";
	$mytext = explode(' ',extractdocxtext($document));
}

// Create connection
$con=mysqli_connect("db517212687.db.1and1.com","dbo517212687","goapirix14","db517212687");
//$con=mssql_connect("apirix.casbp3lerv6t.us-west-2.rds.amazonaws.com","apirix","goapirix14");
$db = new COM("ADODB.Connection"); 
$dsn = "DRIVER={SQL Server}; SERVER=apirix.casbp3lerv6t.us-west-2.rds.amazonaws.com;UID=apirix;PWD=goapirix14; DATABASE=apirix"; 
$db->Open($dsn); 
$rs = $db->Execute("SELECT * FROM tag");


// Check connection
if (mysqli_connect_errno())
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }

$sql = "truncate table tag";
mysqli_query($con,$sql);

$max = sizeof($mytext);
for ($t=0; $t < $max ; $t++){
	//$mytext[$t] = preg_replace("/HYPERLINK/","",$mytext[$t]);
	$mytext[$t] = preg_replace("/[\s\,\.\-\n\r\t@\/\_\(\)\x20]/"," ",$mytext[$t]);
	$mytext[$t] = preg_replace('/[^a-z0-9]/i', '', $mytext[$t]); // remove all special characters
	echo $mytext[$t] . "<br>";
	
	$sql="INSERT INTO tag (tag_value)	VALUES ('$mytext[$t]')";
	if (!mysqli_query($con,$sql))
	  {
		die('Error: ' . mysqli_error($con));
	  }
}
mysqli_close($con);	


?>