
<!--BEGIN EMBED CODE FOR ARTIST DISCOGRAPHY AND PLAYER -->

<?php
/*
Modify this variables below to reflect your actual CreRo installation's protocol if needed
*/
$protocol="https://";


$server=trim(file_get_contents('./d/server.txt'));

if ($server===false){
	die();
}

$server_root=$protocol.$server."/";

$artist=$_GET['artist'];



$old_user_agent = ini_get('user_agent');
ini_set('user_agent', 'Musician site for Crero (https://github.com/shangril/crero)');

?>

		<hr/>
		<a name="discog"><h2>Discography</h2></a>
		<script>
		var current = <?php
		if (isset($_GET['c'])&&is_numeric($_GET['c'])){
		 echo $_GET['c'];
		}
		else{
			echo '0';
		}
		 
		 ?>;
			
		<?php
		$discog = file_get_contents($server_root."/crero-yp-api.php?a=list_albums_with_covers&list_albums_with_covers=".urlencode($artist));
		if ($discog!==false){
			$disc = explode ("\n", $discog);
			
			$alb = Array();
			$cov = Array();
			
			for ($i=0;$i<count($disc);$i++){
				if ($disc[$i]!='')
				{
					array_push($alb, $disc[$i]);
					if (isset($disc[$i+1])){
						array_push($cov, $disc[$i+1]);
					}
					else {
						array_push($cov, '');
					}
				}
				$i++;
			}
			echo "var albums = [";
			for ($i=0;$i<count($alb);$i++){
				echo "'".str_replace("'", "\'", $alb[$i])."'";
				if ($i!=count($alb)-1){
					echo ", ";
				}
				
			}
			echo "];\n";
			echo "var covers = [";
			for ($i=0;$i<count($cov);$i++){
				echo "'".str_replace("'", "\'", $cov[$i])."'";
				if ($i!=count($cov)-1){
					echo ", ";
				}
				
			}
			echo "];\n";
			
			
		}
		
		?>
		function init(){}
		function next(){
			if (albums[current+8]==undefined){return;}
			document.getElementById('i7').src="<?php echo $server_root;?>favicon.png";
			current++;
			display();
		}
		
		function previous(){
			if (current==0){return;}
			document.getElementById('i0').src="<?php echo $server_root;?>favicon.png";
			current--;
			display();
		}
		function display(){

			for (i=0;i<8;i++){
					if (albums[current+i]!=undefined){
					
					document.getElementById('i'+i.toString()).alt=albums[current+i];
					
					target=covers[current+i];
					if (target==''){
						target='../favicon.png';
					}
					
					document.getElementById('i'+i.toString()).src='<?php echo $server_root;?>covers/'+encodeURI(target);
				
					document.getElementById('l'+i.toString()).href='?album='+encodeURIComponent(albums[current+i])+"&c="+(current+i).toString()+"#discog";
				}
			}



			
		}


		</script>
		<div style="width:100%;" onload="init();">
		<div style="width:100%;color:yellow;background-color:black;font-size:200%;text-align:center;" onclick="previous();">&lt;</div>
		<span style="width:12.5%;float:left;" id="a0"><a id="l0"><img style="width:100%;" id="i0" src="<?php echo $server_root;?>favicon.png" /></a></span>
		<span style="width:12.5%;float:left;" id="a1"><a id="l1"><img style="width:100%;" id="i1" src="<?php echo $server_root;?>favicon.png"/></a></span>
		<span style="width:12.5%;float:left;" id="a2"><a id="l2"><img style="width:100%;" id="i2" src="<?php echo $server_root;?>favicon.png"/></a></span>
		<span style="width:12.5%;float:left;" id="a3"><a id="l3"><img style="width:100%;" id="i3" src="<?php echo $server_root;?>favicon.png"/></a></span>
		<span style="width:12.5%;float:left;" id="a4"><a id="l4"><img style="width:100%;" id="i4" src="<?php echo $server_root;?>favicon.png"/></a></span>
		<span style="width:12.5%;float:left;" id="a5"><a id="l5"><img style="width:100%;" id="i5" src="<?php echo $server_root;?>favicon.png"/></a></span>
		<span style="width:12.5%;float:left;" id="a6"><a id="l6"><img style="width:100%;" id="i6" src="<?php echo $server_root;?>favicon.png"/></a></span>
		<span style="width:12.5%;float:left;" id="a7"><a id="l7"><img style="width:100%;" id="i7" src="<?php echo $server_root;?>favicon.png"/></a></span>
		<div style="clear:both;width:100%;color:yellow;background-color:black;font-size:200%;text-align:center;" onclick="next();">&gt;</div>
		<hr style="clear:both;"/>
		</div>
		<script>
			display();
		</script>

		<iframe src="<?php echo $server_root;?>?artist=<?php echo urlencode($artist);?>&embed=<?php echo urlencode($artist);?><?php
		
		if (isset($_GET['album'])) {
			echo '&album='.urlencode($_GET['album']);
		}
		
		
		?>" title="<?php echo htmlspecialchars($artist);?> albums on <?php echo explode("/", $server_root)[1]; ?>" style="width:100%;height:1024px;"></iframe>
		
		<?php
		ini_set('user_agent', $old_user_agent);
		?>
		
<!-- END EMBED CODE FOR ARTIST DISCOGRAPHY AND PLAYER -->
