<?PHP
	include("libmyabotv.php");
	
	$days = 31;

	
	$myAbo = new MyAboTV();
	$myAbo->authenticate("username", "password");
	$shows = $myAbo->enumerateShowsByGenre("7-movie", $days);
	
	foreach($shows as $show)	{
		$file = $show->files[0];
		$fn = $show->safeTitle.".".$file->extension;
		echo("wget -O \"$fn\" \"{$file->url}\"\n");
	}

 ?>
