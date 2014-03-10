<html>
<head>
<script type="text/javascript" src="jkit.complete/jquery-1.9.1.min.js"></script>
<script type="text/javascript" src="jkit.complete/jquery.jkit.1.2.16.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('body').jKit();
});
</script>
<style>
	body{
	    /* Workaround for some mobile browsers */
	    min-height:100%;
	}
	p{
	     font-family:"arial";
	     font-style:normal;
	     font-size: 32px;
	     color:#FFFFFF;
	}
	div.parallax-container {
	position: absolute;
	top: 0px;
	left: 0px;
	width: 100%;
	height: 100%;
	overflow: hidden;

	}
	div.parallax-container div.parallax {
		position: absolute;
		top: 0px;
		left: 0px;
		width: 100%;
		text-align: center;
		height: 100%;
		font-weight: bold;
	}
	.parallax1 {
		color: #555;
		font-size: 250px;
		line-height: 80%;
	}
	.parallax2 {
		color: #999;
		font-size: 100px;
		line-height: 320%;
	}
	.parallax3 {
		color: #fff;
		font-size: 50px;
		line-height: 760%;
	}

</style>
</head>
<body>
<img data-jkit="[background:distort=no]" id="bg" src="images/building.jpg" width="100%" height="100%">

<div class="parallax-container" data-jkit="[parallax:strength=2;axis=both]">
	<div class="parallax parallax1">Apirix.com</div>
	<div class="parallax parallax2">Jobs</div>
	<div class="parallax parallax3">+ You</div>
</div>
</body>
</html>