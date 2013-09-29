<?PHP
	include("libmyabotv.php");
	
  $werbefrei = true;

	$myAbo = new MyAboTV();
	$myAbo->authenticate("username", "password");
	$shows = $myAbo->search("Simpsons", "Pro7", $werbefrei);
	
	if($shows)	{
		foreach((array)$shows as $show)	{
			$file = $show->files[0];
			$fn = $show->safeTitle."-".$show->datetime.".".$file->extension;
			
			echo("wget -O \"/$fn\" \"{$file->url}\"\n");

			
		}
	}

 ?>
