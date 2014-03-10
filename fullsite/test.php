<!-- 2014.02.10 Don Barnwell -->
<!-- Requirements in an email from Steve @ PS|SHIP -->

<?php
require "simple_html_dom.php";

// Since my server has no Amazon login, I kept a local copy from my desktop
// substitute www.amazon.com for the real site
$html = file_get_html('http://www.amazon.com');

$title = array();
$oldprice = array();
$currentprice = array();
$link = array();
$savings = array();

//Scan each element in the DOM using Amazon's standardized naming convention
foreach($html->find('div[class=s9hl]') as $element){ 
	$i++;
	$ret = $element->find('a');
	$title[$i] = $ret[0]->title;

	$theprice = $element->find('span[class*=newListprice]');
	$oldprice[$i] = str_replace("$","",$theprice[0]->innertext);

	$newprice = $element->find('span[class*=s9Price]');
	$currentprice[$i] = str_replace("$","",$newprice[0]->innertext);

	// If oldprice is a number, we can do math, else set it to zero
	if(is_numeric($oldprice[$i])){
		$savings[$i] = $oldprice[$i] - $currentprice[$i];
	}else{
		$savings[$i] = 0;
	}

	// If image is a URL, use it, else use the SRC
	$image = $element->find('img');
	if($image[0]->url){
		$itemimage[$i] = $image[0]->url;
	}else{
		$itemimage[$i] = $image[0]->src;
	}
	
	// Get the anchor for the product and make it clickable later
	$thelink = $element->find('a');
	$link[$i] = 'http://www.amazon.com'.$thelink[0]->href;
}

// This is the sort function.  Sorts only ascending for now.
$sortby = $_POST["sortby"];
$counter = count($title);
for($x=1;$x<$counter;$x++){
	for($y=0;$y<$counter-$x;$y++){
		$yessort = 0;
		if($sortby == "title"){
			if($title[$y] > $title[$y+1] ){
				$yessort = 1;
			}else{
				$yessort = 0;
			}
		}
		if ($sortby == "original"){
			if($oldprice[$y] > $oldprice[$y+1]){
				$yessort = 1;
			}else{
				$yessort = 0;
			}					
		}
		if($sortby == "current" || $sortby == ""){
			if($currentprice[$y] > $currentprice[$y+1]){
				$yessort = 1;
			}else{
				$yessort = 0;
			}										
		}
		if($sortby == "savings"){
			if($savings[$y] > $savings[$y+1]){
				$yessort = 1;
			}else{
				$yessort = 0;
			}										
		}
			
		if($yessort > 0){
			//sort on title
			$temptitle=$title[$y];
			$title[$y]=$title[$y+1];
			$title[$y+1]=$temptitle;
			//sort oldprices too
			$tempoldprice=$oldprice[$y];
			$oldprice[$y]=$oldprice[$y+1];
			$oldprice[$y+1]=$tempoldprice;
			//sort newprices too
			$tempcurrentprice=$currentprice[$y];
			$currentprice[$y]=$currentprice[$y+1];
			$currentprice[$y+1]=$tempcurrentprice;
			//sort links too
			$templink=$link[$y];
			$link[$y]=$link[$y+1];
			$link[$y+1]=$templink;			
			//sort images too
			$tempimage=$itemimage[$y];
			$itemimage[$y]=$itemimage[$y+1];
			$itemimage[$y+1]=$tempimage;						
			//sort savings too
			$tempsavings=$savings[$y];
			$savings[$y]=$savings[$y+1];
			$savings[$y+1]=$tempsavings;									
		} # end of comparison
	} # end of y for
} # end of x for
?>

<html>
	<head>
	<style>
	body {
		font-size: 14px; color: #000000; font-family: verdana, arial,  helvetica,sans-serif
	}
	table {
		font-size: 11px; color: #000000; font-family: verdana, arial,  helvetica,sans-serif
	}
	</style>
	<script language='javascript'>
		function sortit(field){
			document.amazon.sortby.value = field;
			document.amazon.submit();
		}
	</script>
	</head>
	<body class='body'>
	<form name='amazon' id='amazon' method='post'>
	<input type='hidden' name='sortby' id='sortby'>
	Please select a column header to sort<br>
	<?php
		$sortby = $_POST["sortby"];
		if($sortby)
			print 'Sorting by ' .$sortby;
	?>		
	
	<table width='75%' class='table' border=1>
	<tr>
		<td><a href='javascript:sortit("title");'><b>Title</b></a></td>
		<td align='center'><a href='javascript:sortit("original");'><b>Original Price</b></a></td>
		<td align='center'><a href='javascript:sortit("current");'><b>Current Price</b></a></td>
		<td align='center'><a href='javascript:sortit("savings");'><b>Savings</b></a></td>
		<td align='center'>URL</td>
		<td align='center'>Image</td></tr>
		
	<?php
	for($counter = 1; $counter < count($title); $counter++){
		// If the URL is going to be over 20 characters, clean it up
		if(strlen($link[$counter]) > 20){
			$cleanlink = substr($link[$counter],0,20). "...";
		}else{			
			$cleanlink = $link[$counter];
		}
		print "<tr><td>".$title[$counter]."</td>";
		print "<td align='right'>".$oldprice[$counter]."</td>";
		print "<td align='right'>".$currentprice[$counter]."</td>";
		print "<td align='right'>".number_format($savings[$counter],2)."</td>";
		print "<td><a href='".$link[$counter]."' target='_new'>$cleanlink</a></td>";
		print "<td><img src='".$itemimage[$counter]."'></img></td>";
		print "</tr>";
	}
	?>
	</form>
	</body>
</html>