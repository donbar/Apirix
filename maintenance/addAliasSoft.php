<!DOCTYPE html>
<html>
<head>
<script language='javascript'>
  function AddNewValue(e, data){
	var thevalue = data.selected; // returns an array
	var thenewvalue = thevalue[0];	// 0 is the name in the array
	if (thenewvalue.substr(0,2) == "na"){ // New alias
		var numvalue = thenewvalue.substr(3);
		newwindow = window.open('addAliasDetail.php?id='+numvalue, "_blank", "resizable=yes, scrollbars=yes, titlebar=yes, width=500, height=400, top=10, left=10");
		}
	if (thenewvalue.substr(0,2) == "np"){ // New parent - skill
		newwindow = window.open('addSkillDetail.php?type=1', "_blank", "resizable=yes, scrollbars=yes, titlebar=yes, width=500, height=400, top=10, left=10");
		}
		
  }
</script>
	
</head>
<body>

<div class="demo-section">
	<strong>Skill / Alias Maintenance Page</strong>	
	<?php
		
		
		// Create connection
		$conn = odbc_connect("Driver={SQL Server Native Client 10.0};Server=localhost;Database=apirix;", "apirix", "goapirix14");

		// Check connection
		if (!$conn) {
			die('Something went wrong while connecting to MSSQL');
		}		
		
		
		$sql = "
				select skill.skill_id, skillalias.skillalias_display_value, skill.skill_display_value,
				skillalias.skillalias_id
				from skill with(nolock)
					join lkupskillalias with(nolock) on lkupskillalias.fk_skill_skill_id = skill.skill_id
					join skillalias with(nolock) on skillalias.skillalias_id = lkupskillalias.fk_skillalias_skillalias_id
					join lkupSkillCategory with(nolock) on lkupSkillCategory.fk_skill_skill_id = skill.skill_id
				-- where lkupSkillCategory.fk_skillcategory_skillcategory_id = 1
				where skill_type_id = 1
				order by skill_display_value

			";		
		$rs=odbc_exec($conn,$sql);
		if (!$rs)
		  {exit("Error in SQL");}
		  
		$rowcount = 0;
		$parent = "";
	?>
	
<html>
<head>
  <meta charset="utf-8">
  <title>Add Skill / Alias SOFT</title>
  <!-- 2 load the theme CSS file -->
  <link rel="stylesheet" href="/dist/themes/default/style.min.css" />
</head>
<body>
  <!-- 3 setup a container element -->
  <div id="jstree">
  <ul>
    <!-- in this example the tree is populated from inline HTML -->
		<li id='np_0'><a href='#'><b>Create New Skill</b></a></li>	
	<?php
		$parent = "";
		$id = "";
		while (odbc_fetch_row($rs)){
			$id = odbc_result($rs,"skill_id");
			$aliasid = odbc_result($rs,"skillalias_id");
			$root = odbc_result($rs,"skill_display_value");
			$child = odbc_result($rs, "skillalias_display_value");
			if ($root != $parent){
				if ($parent != ""){
					# we had an open parent, so close it
					print "<li id='na_$parentid'><a href='#'><b>Create New Alias</b></a></li>
					</ul></li>";
				}					
				$parent = $root;
				$parentid = $id;
				print "<li id='p$id'>$root<ul>";
			}
			# this is a child record
			print "<li id='c$aliasid'>$child</li>";
		}
		if ($parent != ""){
			# had an open parent, close it
			print "<li id='na_$parentid'><a href='#'>Create New Alias</a></li></ul></li>";
		}
		?>

		</ul></div>

  <!-- 4 include the jQuery library -->
  <script src="/lib/jquery.js"></script>
  <!-- 5 include the minified jstree source -->
  <script src="/lib/jstree.min.js"></script>
  <script>
  $(function () {
    $('#jstree').jstree();
	$('#jstree').on("select_node.jstree", function(e, data){AddNewValue(e, data);});
  });
  
  </script>
</body>
</html>