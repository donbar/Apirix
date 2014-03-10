<!DOCTYPE html>
<?php
if(isset($_POST['categoryid'])){
	$categoryid = $_POST['categoryid'];
	}
	else{
	$categoryid = "";
	}
if(isset($_POST['new_skill'])){
	$new_skill = rtrim($_REQUEST['new_skill']);
	}
	else{
	$new_skill = "";
	}
	$skill_type_id = $_GET['type'];

?>


<html>
<head>
</head>
<body>

<div class="demo-section">
	<strong>Add A New Skill</strong>	
	<?php
		
		
		// Create connection
		$conn = odbc_connect("Driver={SQL Server Native Client 10.0};Server=localhost;Database=apirix;", "apirix", "goapirix14");

		// Check connection
		if (!$conn) {
			die('Something went wrong while connecting to MSSQL');
		}		
		
		if ($new_skill != ""){
			# try to insert a new skill alias if it doesn't already exist
			$sql = "select skill_id as cnt 
				from skill with(nolock)
				where skill_display_value = '" . $new_skill . "'";
			$rs=odbc_exec($conn,$sql);
			if (!$rs)
			  {exit("Error in SQL");}
			$exists = odbc_result($rs,"cnt");
			$existskillid = $exists;
			if ($exists > 0){
				$sql = "select count(*) as cnt
						from lkupSkillCategory with(nolock)
							join skillcategory with(nolock) 
								on lkupSkillCategory.fk_skillcategory_skillcategory_id = skillcategory.skillcategory_id
							join skill with(nolock) on lkupSkillCategory.fk_skill_skill_id = skill.skill_id
						where skill.skill_display_value = '$new_skill'
						and skillcategory.skillcategory_id = " . $categoryid;
				$rs=odbc_exec($conn,$sql);
				if (!$rs)
				  {exit("Error in SQL");}
				$exists = odbc_result($rs,"cnt");
				if($exists > 0){
					print '
						<script>
							alert("Skill already exists in the category.");
						</script>';
				}else{
					$sql = "insert into lkupSkillCategory (fk_skill_skill_id, fk_skillcategory_skillcategory_id)
							values ($existskillid,$categoryid)"; 
					$rs=odbc_exec($conn,$sql);
					if (!$rs)
					  {exit("Error in SQL");}
				
					print '
						<script>
							alert("Skill already exists.  It was added to the new category.");
						</script>';
				}
			}else{
				$search = preg_replace("/[\s\,\.\-\n\r\t@\/\_\(\)\x20]/","",$new_skill);
				$search = preg_replace('/[^a-z0-9]/i', '', $search); 

				$sql = "insert into skill (skill_display_value, skill_dtm_added, skill_type_id)
						values ('$new_skill', getdate(), $skill_type_id)";
				$rs=odbc_exec($conn,$sql);
				$sql = "select @@identity as ident";
				$rs=odbc_exec($conn,$sql);
				$skill_id = odbc_result($rs,"ident");

				$sql = "insert into lkupSkillCategory (fk_skill_skill_id, fk_skillcategory_skillcategory_id)
						values ($skill_id,$categoryid)"; 
				$rs=odbc_exec($conn,$sql);
				if (!$rs)
				  {exit("Error in SQL");}
				

				$sql = "insert into skillalias (skillalias_search_value, skillalias_display_value)
						values ('$search','$new_skill')";
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
		} # end of inserting new skill
	  
		$rowcount = 0;
		$parent = "";
	
print "	
<form name='mainfrm' method='post'>
Please enter a new <b>SKILL</b><br><br>
<select name='categoryid'>
";

	$sql = "select skillcategory_id, skillcategory_level, skillcategory_name
			from skillcategory with(nolock)";
	$rs=odbc_exec($conn,$sql);
	if (!$rs)
	  {exit("Error in SQL");}
	  
	while (odbc_fetch_row($rs)){
		$rowcount++;
		$skillcategorylevel=odbc_result($rs,"skillcategory_level");
		$skillcategoryid = odbc_result($rs,"skillcategory_id");
		$skillcategoryname = odbc_result($rs,"skillcategory_name");
		for ($t=0; $t < $skillcategorylevel; $t++){
			$spaces.="&nbsp;";
			}
		print "<option value='$skillcategoryid'>".$spaces.$skillcategoryname."</option>";
		}
print "
	</select><br>

<input type='text' name='new_skill' length=50 maxlength=4000 autofocus='autofocus'><br>
<input type='submit'>
</form>

</body>
</html>
";
?>