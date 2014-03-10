<?php
error_reporting(E_ALL ^ E_STRICT);
ini_set('display_errors', 1);

include 'vendor/autoload.php';
include 'include/guid.php';

if(isset($_GET['categoryid'])){
	$categoryid = $_GET['categoryid'];
	}
	else{
	$categoryid = "";
	}


print <<<qq
<link rel="stylesheet" href="css/apirix.css" />
qq;

// Create connection
$conn = odbc_connect("Driver={SQL Server Native Client 10.0};Server=localhost;Database=apirix;", "apirix", "goapirix14");

// Check connection
if (!$conn) {
    die('Something went wrong while connecting to MSSQL');
}
 

/*Name of the document file*/
$newfilename = $_GET['filename'];
$document = "incoming/".$_GET['filename'];

$ext = end(explode('.', $newfilename));
$guid = guid();
$guid = str_replace('{','',$guid);
$guid = str_replace('}','',$guid);
$guid = $guid . ".".$ext;
$maindir = rand(0,999);
$subdir = rand(0,999);
$maindir = str_pad($maindir, 4, "0", STR_PAD_LEFT);
$subdir = str_pad($subdir, 4, "0", STR_PAD_LEFT);

rename($document,"uploadstorage/$maindir/$subdir/".$guid);

echo "original file name is " . $newfilename . "<br>";
echo "new file name is $maindir/$subdir/" . $guid . "<br>";

$document =  "uploadstorage/$maindir/$subdir/".$guid;

$sql = "insert into userResumes (user_id, resume_name, apirix_name, category_id) 
		values (1,'".$newfilename."','uploadstorage/$maindir/$subdir/".$guid."',".$categoryid.")";
$rs=odbc_exec($conn,$sql);
if (!$rs)
  {exit("Unable to insert resume into userresume table");}

  
 
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
	$mytexttxt = extractdoctext($document);
	$mytexttxt = str_replace("HYPERLINK","",$mytexttxt);
	
	/* So far we found this to be the standard footer in Word documents */
	$footer = strpos(str_replace(" ","",$mytexttxt),"YdXiJxITS");
	
	if ($footer > 0){
		$mytexttxt = substr($mytexttxt,0,$footer);
		}
	$mytext = explode(' ',$mytexttxt);
	$merge = array_search('MERGEFORMAT',$mytext);
	 if ($merge > 0){
		array_splice($mytext, $merge);
	}
}
if($ext == "docx"){ 
	echo "Parsing DOCX!<br>";
	$mytext = explode(' ',extractdocxtext($document));
}


$max = sizeof($mytext);
$theresume = '';
for ($t=0; $t < $max ; $t++){
	// Build all words into a single variable; let's try this
	$theresume .= $mytext[$t];
	#echo $mytext[$t] . "<br>";
}

$theresume = preg_replace("/[\s\,\-\n\r\t@\/\_\(\)\x20]/","",$theresume);
$theresume = preg_replace('/[^a-z0-9#.]/i', '', $theresume); 

#print "<p>$theresume</p><br><br>";

print "<p id='updateuser' style='display:block'></p>";


/* HARD SKILLS SECTION */
$skills = array();
$sql = "
		select skillalias.skillalias_search_value, skill.skill_display_value
		from skill with(nolock)
			join lkupskillalias with(nolock) on lkupskillalias.fk_skill_skill_id = skill.skill_id
			join skillalias with(nolock) on skillalias.skillalias_id = lkupskillalias.fk_skillalias_skillalias_id
			join lkupSkillCategory with(nolock) on lkupSkillCategory.fk_skill_skill_id = skill.skill_id
		where lkupSkillCategory.fk_skillcategory_skillcategory_id = $categoryid
		and skill_type_id = 0

	";
$rs=odbc_exec($conn,$sql);
if (!$rs)
  {exit("Error in SQL");}
  
$skillcount = 0;
$rowcount = 0;
$barmax = odbc_num_rows($rs);
while (odbc_fetch_row($rs)){
	$rowcount++;
	$skillvalue=odbc_result($rs,"skillalias_search_value");
	$realskill=odbc_result($rs,"skill_display_value");
	if (stripos($theresume,$skillvalue) > 0){
		print "
			<script>
				//document.getElementById('updateuser').innerHTML = 'Found $skillvalue';
			</script>";
		$skillcount++;
		$skills[$skillcount] = $realskill;
	}else{
		print "
			<script>
				//document.getElementById('updateuser').innerHTML = 'Did not find $skillvalue';
			</script>";
	}
}
print "
	<script>
		document.getElementById('updateuser').style.display='hidden';
	</script>";
odbc_close($conn);
$hardskills= array_keys(array_count_values($skills));

$max = sizeof($hardskills);
print "<br><br>You appear to have these HARD skills:<br><br>";
for ($t=0; $t < $max ; $t++){
	print $hardskills[$t] . "<br>";
}

/* SOFT SKILLS SECTION */
$skills = array();

$sql = "
		select skillalias.skillalias_search_value, skill.skill_display_value
		from skill with(nolock)
			join lkupskillalias with(nolock) on lkupskillalias.fk_skill_skill_id = skill.skill_id
			join skillalias with(nolock) on skillalias.skillalias_id = lkupskillalias.fk_skillalias_skillalias_id
			join lkupSkillCategory with(nolock) on lkupSkillCategory.fk_skill_skill_id = skill.skill_id
		where lkupSkillCategory.fk_skillcategory_skillcategory_id = 2
		and skill_type_id = 1

	";
$rs=odbc_exec($conn,$sql);
if (!$rs)
  {exit("Error in SQL");}
  
$skillcount = 0;
$rowcount = 0;
$barmax = odbc_num_rows($rs);
while (odbc_fetch_row($rs)){
	$rowcount++;
	$skillvalue=odbc_result($rs,"skillalias_search_value");
	$realskill=odbc_result($rs,"skill_display_value");
	if (stripos($theresume,$skillvalue) > 0){
	print "
			<script>
				//document.getElementById('updateuser').innerHTML = 'Found $skillvalue';
			</script>";
		$skillcount++;
		$skills[$skillcount] = $realskill;
	}else{
	print "
			<script>
				//document.getElementById('updateuser').innerHTML = 'Did not find $skillvalue';
			</script>";
	}
}
print "
	<script>
		document.getElementById('updateuser').style.display='hidden';
	</script>";
odbc_close($conn);
$softskills= array_keys(array_count_values($skills));

$max = sizeof($softskills);
print "<br><br>You appear to have these SOFT skills:<br><br>";
for ($t=0; $t < $max ; $t++){
	print $softskills[$t] . "<br>";
}




?>