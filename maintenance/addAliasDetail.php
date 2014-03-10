<!DOCTYPE html>
<?php
if(isset($_GET['id'])){
	$skill_id = $_GET['id'];
	};
if(isset($_POST['id'])){
	$skill_id = $_POST['id'];
	};
if(isset($_POST['new_alias'])){
	$new_alias = rtrim($_REQUEST['new_alias']);
	}
	else{
	$new_alias = "";
	}
?>


<html>
<head>
</head>
<body>

<div class="demo-section">
	<strong>Add A New Alias</strong>	
	<?php
		
		
		// Create connection
		$conn = odbc_connect("Driver={SQL Server Native Client 10.0};Server=localhost;Database=apirix;", "apirix", "goapirix14");

		// Check connection
		if (!$conn) {
			die('Something went wrong while connecting to MSSQL');
		}		
		
		if ($new_alias != ""){
			# try to insert a new skill alias if it doesn't already exist
			$sql = "select count(*) as cnt 
				from skillalias with(nolock)
				where skillalias_display_value = '" . $new_alias . "'";
			$rs=odbc_exec($conn,$sql);
			print $sql;
			if (!$rs)
			  {exit("Error in SQL");}
			$exists = odbc_result($rs,"cnt");
			if ($exists > 0){
				print '
					<script>
						alert("Value already exists.");
					</script>';
			}else{
				$search = preg_replace("/[\s\,\-\n\r\t@\/\_\(\)\x20]/","",$new_alias);
				$search = preg_replace('/[^a-z0-9#.]/i', '', $search); 

				$sql = "insert into skillalias (skillalias_search_value, skillalias_display_value)
						values ('$search','$new_alias')";
				$rs=odbc_exec($conn,$sql);
				
				$sql = "select @@identity as ident";
				$rs=odbc_exec($conn,$sql);
				$newid = odbc_result($rs,"ident");
				
				$sql = "insert into lkupskillalias (fk_skill_skill_id, fk_skillalias_skillalias_id) values ($skill_id, $newid)";
				$rs=odbc_exec($conn,$sql);
				
			}
			print "
				<script language='javascript'>
					window.opener.location.reload();
					window.close();
				</script>
				";
			exit;
		} # end of inserting new alias

	
		$sql = "
				select skill.skill_display_value
				from skill with(nolock)
				where skill.skill_id = $skill_id
			";		
		$rs=odbc_exec($conn,$sql);
		if (!$rs)
		  {exit("Error in SQL");}
		 $skill_display_value = odbc_result($rs,"skill_display_value");
		  
		$rowcount = 0;
		$parent = "";
	
print "	
<form name='mainfrm' method='post'>
<input type='hidden' name='skill_id' value='$skill_id'><br>
Please enter a new alias for the skill: <b>$skill_display_value</b><br>
<input type='text' name='new_alias' length=50 maxlength=4000 autofocus='autofocus'><br>
<input type='submit'>
</form>

</body>
</html>
";
?>