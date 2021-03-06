
<html>
    <head>
        <title>Statistique Audiovideocast</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"> 
	<link rel="stylesheet" type="text/css" href="style.css" media="screen">
	<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css">
	<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
	<script src="http://code.jquery.com/ui/1.10.4/jquery-ui.js"></script>
	<script>
	$(function() {
	$( "#debut" ).datepicker();
	$( "#fin" ).datepicker();
	});
	</script>
	<script>
	$(document).ready(function() {
		//click puis selection du texte du lien selectionne
		$('a').on('click', function() {
			
			var recherche = $(this).text();	
			$('#titre').text(recherche);
			

			$("a").each(function() {
    				$(this).addClass('lien');
				$(this).removeClass('highlight');
			});


			$(this).removeClass('lien');
			$(this).addClass('highlight');
			
			var typevalue = $(this).attr("href").match(/type=([0-9]+)/)[1];
			$('#type').val(typevalue);
			return false;
			
		});
		

		$("#target").submit(function (event) {
			//recuperer
			var recherche = $('#titre').text();	
			$('#titre').text(recherche);
			isvalid = false
			var debut = $('#debut').val();
        		var fin = $('#fin').val();
			var echan = $('#echan').val();
			var repart = $('#repart').val();
			var type = $('#type').val();
			if ( (echan!='n') && ((debut=='')||(fin=='')) )
			{
				alert('Les dates de début et de fin sont obligatoires \nsi vous souhaitez échantilloner les résultats');
			}
			else 
			{
				isvalid=true;
			}
			var $form = $('#target');
                        var serializedData = $form.serialize();
			if (isvalid)
			{ 
				$.ajax({
            				type: "POST",
					url: "audiovideocast_stats.php",
					data: serializedData,
				        success: function(data) {
						 console.log("Hooray, it worked!");
					    },
					error: function(jqXHR, textStatus, errorThrown) {
						console.log("Status: " + textStatus + "Error: " + errorThrown);
					    }
					});
			}
			//return false;

		});
			

	});
	</script>
    </head>


<body>
<div id="container">
<div id="topbar"> <h1 align="center">Statistiques AVC</h1></div>
<div id="navbar"></div>
<div id="helpbar" ><span class="help">Cliquez sur le type de résultats que vous désirez puis remplissez le formulaire.</span></div>

<div id="main">
<div id="column_left"> 
<a href="audiovideocast_stats.php?type=1" class="lien" id="lien1">Auteurs  ( répartion selon le cours)</a><br>
<a href="audiovideocast_stats.php?type=2" class="lien" id="lien2">Diffuseurs  (repartition cours / utilisateur )</a><br>
<a href="audiovideocast_stats.php?type=3" class="lien" id="lien3">Etudiants diffuseurs (repartition cours / utilisateur )</a><br>
<a href="audiovideocast_stats.php?type=4" class="lien" id="lien4">Biatoss et Enseignants diffuseur (repartition cours / utilisateur )</a><br>
<a href="audiovideocast_stats.php?type=6" class="lien" id="lien5">Enregistrements de plus de 30 secondes (repartition cours / utilisateur )</a><br>
<a href="audiovideocast_stats.php?type=7" class="lien" id="lien6">Visualisations (repartition cours / utilisateur )</a><br>
<a href="audiovideocast_stats.php?type=8" class="lien" id="lien7">Etudiants connectés ayant visualisé au moins un cours(repartition cours / utilisateur )</a><br>
<a href="audiovideocast_stats.php?type=9" class="lien" id="lien8">Enseignants connectés ayant visualisé au moins un cours (repartition cours / utilisateur )</a><br>
<a href="audiovideocast_maj_composantes.php" class="lien">MaJ composante</a><br>
<a href="logout.php" class="lien">Se deloguer</a>
<?

// On appelle la session
session_start ();
if (!$_SESSION['loginOK']) {


	echo '<body onLoad="alert(\'Les données ne permettent pas de vous authentifier...\')">';
	echo '<meta http-equiv="refresh" content="0;URL=index.php">';
}

include 'config.php';

$connection = pg_connect ("host=$host dbname=$db user=$user password=$pass");
if (!$connection)
{
	die("Could not open connection to database server");
}


$type=$_GET['type']; 
$cache_debut= $_POST['debut']; $cache_fin= $_POST['fin']; 
$cache_echan= $_POST['echan'];$cache_repart= $_POST['repart'];
$selected ="selected";
//mettre les composantes et discipline en sessions
if (!isset($_SESSION['composantes']))
{
	$composantes_utilisateur = getComposantes($ldapserver,$ldapport,$ldapuser,$ldappass,$ldaptree);
	$_SESSION['composantes']=$composantes_utilisateur;
}
else
{
	$composantes_utilisateur = $_SESSION['composantes'];	
}

if (!isset($_SESSION['disciplines']))
{
	$disciplinelevel_cours = getDisciplineLevel($connection);
	$_SESSION['disciplines']=$disciplinelevel_cours;
}
else
{
	$disciplinelevel_cours = $_SESSION['disciplines'];	
}

if( $_POST['submit']=="poster")
{
	$type= $_POST['type'];
}
?>
    <h2><label id="titre"><? echo donner_titre($type);?></label></h2>
    <FORM action="audiovideocast_stats.php" method="post" id="target">
    Date début : <input type="text" id="debut" name="debut" style="width: 100px"    value="<? echo $cache_debut;?>" > 
    Date de fin : <input type="text" id="fin" name="fin"  style="width: 100px"     value="<? echo $cache_fin;?>"> 
  
	<select name="echan" id="echan">
	<option value="n">Pas d'échantillonage</option>
	<option value="1 day" <? if ($cache_echan=='1 day') echo "selected";?>>journalier</option>
	<option value="1 week" <? if ($cache_echan=='1 week') echo "selected";?>>hebdomadaire</option>
	<option value="1 month" <? if ($cache_echan=='1 month') echo "selected";?>>Mensuel</option>
	<option value="1 year" <? if ($cache_echan=='1 year') echo "selected";?>>Annuel</option>
	</select> 
	<select name="repart" id="repart">
        <option value="n" >pas de répartition</option>
	<option value="c"  <? if ($cache_repart=='c') echo "selected";?>>composantes des cours</option>
	<option value="l"  <? if ($cache_repart=='l') echo "selected";?>>niveaux des cours</option>
	<option value="cl" <? if ($cache_repart=='cl') echo "selected";?>>composantes-niveau des cours</option>
	<option value="cu" <? if ($cache_repart=='cu') echo "selected";?>>composantes des utilisateurs</option>
	</select>
   <input type ="text" value="<? echo $_GET['type'];?><? echo $_POST['type'];?>" name='type' id='type'>
   <input type="submit" value="poster" name="submit" id="submit">

  </FORM>

<?

$debut= $_POST['debut']; $fin= $_POST['fin']; $echan= $_POST['echan'];$repart= $_POST['repart']; 
$queries =  array();
$estdatee=false;$estechant=false;
$resultat=array() ; $lignes = array(); $compteurl=0;
if( $_POST['submit']=="poster")
{
	$type= $_POST['type'];
	if($type==1||$type==2||$type==3||$type==4||$type==5||$type==6||$type==7||$type==8||$type==9)
	{	

		//construire la query en fonction de la demande
		$query = donner_requete($type);
		if($debut!=='' && $fin!='')
		{	
			
			//echantillonage : selon le type d'echantillonage echantillonner
			echo "Dates sélectionnées\n";
			
			
			if($echan!==''&& $echan!=='n')
			{
				if ($debut==$fin)
				{	
					$debut = $debut." 00:00:00";
					$fin = $fin." 23:59:59";
					$wheredate = " and recorddate >='$debut' and recorddate <'$fin'";
					if ($type==7||$type==8||$type==9) $wheredate = " and l.date >='$debut' and l.date <'$fin'";
					array_push($queries,$query.$wheredate);
					$lignes[$compteurl] = "$debut;$fin;"; 
					$compteurl++;	
				}
				else
				{
					if ($echan=='1 day')
					{
						//Prendre jour apres jour
						$date_debut= date_create($debut); $date_fin= date_create($fin);
						while ($date_debut -> format('Y-m-d') <= $date_fin -> format('Y-m-d'))
						{
						    	
							$debut = $date_debut->format('Y-m-d'). " 00:00:00";
							$fin = 	$date_debut->format('Y-m-d'). " 23:59:59";
						    	$wheredate = " and recorddate >='$debut' and recorddate <='$fin'";
							if ($type==7||$type==8||$type==9) $wheredate = " and l.date >='$debut' and l.date <'$fin'";
						    	array_push($queries,$query.$wheredate);
							$lignes[$compteurl] = "$debut;$fin;"; 
							$compteurl++;
							$date_debut -> modify('+1 day'); 
						}
						
					}
					else if ($echan=='1 week')
					{
						$date_debut=date_create($debut); $date_fin = date_create($fin);
						$interval = DateInterval::createFromDateString($echan);
						$period = new DatePeriod($date_debut, $interval, $date_fin);
						$new_date = "";
					
						foreach ( $period as $dt )
						{
							
							$debut_requete = $dt->format( 'Y-m-d' ). " 00:00:00";
							$dt -> modify('+6 day'); 
							$fin_requete = $dt->format( 'Y-m-d' ). " 23:59:59";
							$wheredate = " and recorddate >='$debut_requete' and recorddate <'$fin_requete'";
							if ($type==7||$type==8||$type==9) $wheredate = " and l.date >='$debut_requete' and l.date <'$fin_requete'";
							array_push($queries,$query.$wheredate);
							$lignes[$compteurl] = "$debut_requete;$fin_requete;"; 
							$compteurl++;
							
						}
					}
					else if ($echan=='1 month')
					{
						$date_debut=date_create($debut); $date_fin = date_create($fin);
						$interval = DateInterval::createFromDateString($echan);
						$period = new DatePeriod($date_debut, $interval, $date_fin);
						$new_date = "";
					
						foreach ( $period as $dt )
						{
							$debut_requete = $dt->format( 'Y-m-d' ). " 00:00:00";
							$dt -> modify('+1 month'); 
							$fin_requete = $dt->format( 'Y-m-d' ). " 00:00:00";
							$wheredate = " and recorddate >='$debut_requete' and recorddate <'$fin_requete'";
							if ($type==7||$type==8||$type==9) $wheredate = " and l.date >='$debut_requete' and l.date <'$fin_requete'";
							array_push($queries,$query.$wheredate);
							$lignes[$compteurl] = "$debut_requete;$fin_requete;"; 
							$compteurl++;
							
						}
					}
					else if ($echan=='1 year')
					{
						echo "année selectionnée";
						$date_debut=date_create($debut); $date_fin = date_create($fin);
						$interval = DateInterval::createFromDateString($echan);
						$period = new DatePeriod($date_debut, $interval, $date_fin);
						$new_date = "";
					
						foreach ( $period as $dt )
						{
							$debut_requete = $dt->format( 'Y-m-d' ). " 00:00:00";
							$dt -> modify('+1 year'); 
							$fin_requete = $dt->format( 'Y-m-d' ). " 23:59:59";
							$wheredate = " and recorddate >='$debut_requete' and recorddate <'$fin_requete'";
							if ($type==7||$type==8||$type==9) $wheredate = " and l.date >='$debut_requete' and l.date <'$fin_requete'";
							array_push($queries,$query.$wheredate);
							$lignes[$compteurl] = "$debut_requete ;$fin_requete;"; 
							$compteurl++;
							
						}
					}
					
				}
			}
			else 
			{
				echo " Echantillonage non selectionné\n"; 
				$debut = $debut ." 00:00:00";
				$fin = $fin. " 23:59:59";
				$wheredate = " and recorddate >='$debut' and recorddate <'$fin'";
				if ($type==7||$type==8||$type==9) $wheredate = " and l.date >='$debut' and l.date <'$fin'";
				array_push($queries,$query.$wheredate); 
				$lignes[0] = "$debut;$fin;";
			}
		}
		else 
		{	
			
			echo "Dates Non sélectionnées Echantillonage non selectionné\n";
			$wheredate = " and 1=1";
			array_push($queries,$query.$wheredate);
		        $lignes[0] = "resultats "; 
			
		}
		//repartion par composantes
		$composantes = array();
		if($repart!==''&& $repart!=='n')
		{
			if ($repart=='cu')
			{
				
				$queries_temp = $queries; $lignes_temp = $lignes;
				$queries= array();$lignes=array();
				echo "repartition selectionnée par composante utilisateurs<br>";
				$composantes = array_keys($composantes_utilisateur);
				for ($d=0; $d<count($queries_temp); $d++) 
				{ 
					$query_temp = $queries_temp[$d];
					$ligne_temp = $lignes_temp[$d];
					for ($e=0; $e<count($composantes); $e++) 
					{
						
						$whererepart = " and institute ilike '$composantes[$e]'";
						array_push($queries,$query_temp.$whererepart); 
						array_push($lignes,$ligne_temp."".$composantes_utilisateur[$composantes[$e]].";" ); 
					}
				}
				
				
			}
			
			if ($repart=='c')
			{
				echo "repartition selectionnée par composante cours<br>";
				afficher_tableau($queries_temp);
				$queries_temp = $queries; $lignes_temp = $lignes;
				$queries= array();$lignes=array();
				$composantes = array();
				$queryc = "select distinct SUBSTRING(formation, 1, 2) as composante, d.namecomp from course c, discipline d 
				where SUBSTRING(formation, 1, 2) = d.codecomp order by d.namecomp";
				$resultc = pg_query($connection, $queryc) ;
				$rowsc = pg_num_rows($resultc);
				for ($c=0; $c<$rowsc; $c++) 
				{ 
					$rowc = pg_fetch_row($resultc, $c);
					$composantes[$c] = $rowc[0]; 
				}
				
				for ($d=0; $d<count($queries_temp); $d++) 
				{ 
					$query_temp = $queries_temp[$d];
					$ligne_temp = $lignes_temp[$d];
					for ($e=0; $e<count($composantes); $e++) 
					{
						
						$whererepart = " and formation ilike '$composantes[$e]-%'";
						array_push($queries,$query_temp.$whererepart); 
						array_push($lignes,$ligne_temp."".$disciplinelevel_cours[$composantes[$e]].";"  ); 
					}
				
				}
				
				
				
			} //repartition composantes
			if ($repart=='cl')
			{
				$queries_temp = $queries; $lignes_temp = $lignes;
				$queries= array();$lignes=array();
				//reprendre les composantes
				echo "repartition selectionnée par composante et niveau cours<br>";
				$composantes = array();
				$queryc = "select distinct SUBSTRING(formation, 1, 2) as composante, d.namecomp from course c, discipline d 
				where SUBSTRING(formation, 1, 2) = d.codecomp order by d.namecomp";
				$resultc = pg_query($connection, $queryc) ;
				$rowsc = pg_num_rows($resultc);
				for ($c=0; $c<$rowsc; $c++) 
				{ 
					$rowc = pg_fetch_row($resultc, $c);
					$composantes[$c] = $rowc[0]; 
				}
				for ($d=0; $d<count($queries_temp); $d++) 
				{ 
					$query_temp = $queries_temp[$d];
					$ligne_temp = $lignes_temp[$d];
					for ($e=0; $e<count($composantes); $e++) 
					{
						
						$whererepart = " and formation ilike '$composantes[$e]-%'";
						array_push($queries,$query_temp.$whererepart); 
						array_push($lignes,$ligne_temp."".$disciplinelevel_cours[$composantes[$e]].";" ); 
					}
				}
				$queries_temp = $queries; $lignes_temp = $lignes;
				$queries= array();$lignes=array();
				//Les niveaux
				$niveaux = array();
				$queryf = "select code  from level order by name";
				$resultf = pg_query($connection, $queryf) ;
				$rowsf = pg_num_rows($resultf);
				for ($f=0; $f<$rowsf; $f++) 
				{ 
					$rowf = pg_fetch_row($resultf, $f);
					$niveaux[$f] = $rowf[0];
				}

				for ($h=0; $h<count($queries_temp); $h++) 
				{ 
					$query_temp = $queries_temp[$h];
					$ligne_temp = $lignes_temp[$h];
					for ($o=0; $o<count($niveaux); $o++) 
					{
						
						$whererepart = " and formation ilike '%-$niveaux[$o]'";
						array_push($queries,$query_temp.$whererepart); 
						array_push($lignes,$ligne_temp."".$disciplinelevel_cours[$niveaux[$o]].";" ); 
					}
				}
				
				
			} //repartition  niveau

			if ($repart=='l')
			{
				echo "repartition selectionnée niveau <br>";
				$queries_temp = $queries; $lignes_temp = $lignes;
				$queries= array();$lignes=array();
				//Les niveaux
				$niveaux = array();
				$queryp = "select code  from level order by name";
				$resultp = pg_query($connection, $queryp) ;
				$rowsp = pg_num_rows($resultp);
				for ($p=0; $p<$rowsp; $p++) 
				{ 
					$rowp = pg_fetch_row($resultp, $p);
					$niveaux[$p] = $rowp[0];
				}
				
				for ($m=0; $m<count($queries_temp); $m++) 
				{ 
					$query_temp = $queries_temp[$m];
					$ligne_temp = $lignes_temp[$m];
					for ($m=0; $m<count($niveaux); $m++) 
					{
						
						$whererepart = " and formation ilike '%-$niveaux[$m]'";
						array_push($queries,$query_temp.$whererepart); 
						array_push($lignes,$ligne_temp."".$disciplinelevel_cours[$niveaux[$m]].";"); 
					}
				}
				
				
			} //repartition composantes
			
		}
		echo "<p></p>";
		$total = 0;

		for ($j = 0; $j < count($queries); $j++) 
		{
			$result = pg_query($connection, $queries[$j]) ;
			$rows = pg_num_rows($result);
			$index = $lignes[$j];
			if($type==1||$type==2||$type==3||$type==4||$type==5||$type==6)
			{
				
				$resultat[$index] = $rows;
				$total +=$rows;
			} 
			else if ($type==7||$type==8||$type==9)
			{
				$row = pg_fetch_assoc($result); 
				$resultat[$index] = $row['row0'];
				$total +=$row['row0'];
			}

			
			
		}
		//afficher_tableau($lignes) ;
		
		echo "<h3>Total : $total</h3>";
		afficher_tableau($resultat) ;
		
	
		
	}//fin if type=1
}

function chrono($TotSec) {
        $heures  =  bcdiv($TotSec,  3600,  0);
        $minutes  =  (bcdiv($TotSec,  60,  0)  %  60);
        $secondes = $TotSec-(($heures * 3600) + ($minutes * 60));
        return $heures  .  "h "  .  $minutes  .  "min"  .  $secondes."s";
}

function afficher_tableau($tableau) 
{
	foreach($tableau as $cle => $valeur) 
  	{
    		echo "<b>$cle</b>". $valeur."<br/>";
  	}
} 

function donner_requete($type)
{
	switch ($type)
	 {
	 case 1: //auteurs
	   $retour =  "select distinct(name||' '||firstname) as result from course where 1=1 ";
	   break;
	 case 2://diffuseurs
	   $retour ="select distinct u.userid from \"user\" u left outer join course c on c.userid = u.userid where 1=1";
	   break;
	 case 3://nombre détudiants qui ont diffusés
	   $retour = "select distinct u.userid from \"user\" u left outer join  course c on c.userid = u.userid where profile='student'";
	   break;
	 case 4: //nombre d'enseignant et biatoss qui ont diffuses
	   $retour = "select distinct u.userid from \"user\" u left outer join course c on c.userid = u.userid where  (profile='employee' or profile ='member' or profile='faculty')";
	   break;
         case 6://nombre d'enregistrement de plus de 30 s
	   $retour = "select courseid from course c left outer join \"user\" u  ON u.userid = c.userid where duration > 30 ";
	   break;
	case 7://nombre total de visualisations
	   $retour = "select count(*)  as row0 from log_user_action l left outer join \"user\" u on u.userid = l.userid 
			left outer join course c on l.courseid = c.courseid 
			where action = '/avc/courseaccess'";
		//SELECT sum(consultations) as row0 from course c LEFT OUTER JOIN \"user\" u  ON u.userid = c.userid where duration > 30 ";
	   break;
	case 8:
	   $retour = "select count(distinct l.userid)  as row0 from log_user_action l left outer join \"user\" u on u.userid = l.userid 
			left outer join course c on l.courseid = c.courseid 
			where action = '/avc/courseaccess' and u.profile='student' " ;
	   break;
	case 9:
	   $retour = "select count(distinct l.userid)  as row0 from log_user_action l left outer join \"user\" u on u.userid = l.userid 
			left outer join course c on l.courseid = c.courseid 
			where action = '/avc/courseaccess' and u.profile='faculty' " ;
	   break;
	
	 }
	return $retour;
}



//get disciplineLevel
function getDisciplineLevel($connection)
{
	$liste = array();
	$query = "select codecomp as code, namecomp as name  from discipline order by namecomp";
	$result = pg_query($connection,$query);
        while($row = pg_fetch_assoc($result)) {

		$code = $row['code'];
		$name= $row['name'];
        	$liste[$code] = $name;
        } 
	$query1 = "select code as code, name as name from level order by name";
	$result1 = pg_query($connection,$query1);
        while($row1 = pg_fetch_assoc($result1)) {
		$code = $row1['code'];
		$name= $row1['name'];
        	$liste[$code] = $name;
        } 
	return $liste;

}

function getComposantes($ldapserver,$ldapport,$ldapuser,$ldappass,$ldaptree )
{
	
	$listeComposantes = array();
	// connect
	$ldapconn = ldap_connect($ldapserver,$ldapport) or die("Could not connect to LDAP server.");

	ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
	ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);

	$ldapbind = ldap_bind($ldapconn, $ldapuser, $ldappass) or 
	die ("<br>Error trying to bind: ".ldap_error($ldapconn));

	$filtre="(ou=*)";
     	$result = ldap_search($ldapconn,$ldaptree, $filtre) or die ("Error in search query: ".ldap_error($ldapconn));
	$nbr = ldap_count_entries ( $ldapconn,$result );
	$info = ldap_get_entries( $ldapconn,$result);
	

	for ($z=0; $z < $info["count"]; $z++) {
		
		$code= $info[$z]["ou"][0];
        	$description= $info[$z]["udslongdesc"][0];
        	$listeComposantes[$code] = $description;
		//echo $description;
	}
	return $listeComposantes;
}



function donner_titre($type)
{
	switch ($type)
	 {
	 case "1":
	   $retour =  "Auteurs  ( répartion selon le cours)";
	   break;
	 case "2":
	   $retour = "Diffuseurs  (repartition cours / utilisateur )";
	   break;
	 case "3":
	   $retour = "Etudiants diffuseurs (repartition cours / utilisateur )";
	   break;
	 case "4":
	   $retour = "Biatoss et Enseignants diffuseur (repartition cours / utilisateur )";
	   break;
	 case "6":
	   $retour = "Enregistrements de plus de 30 secondes (repartition cours / utilisateur )";
	   break;
	 case "7":
	   $retour = "Visualisations (repartition cours / utilisateur )";
	   break;
	 case "8":
	   $retour = "Etudiants connectés ayant visualisé au moins un cours(repartition cours / utilisateur )";
	   break;
	 case "9":
	   $retour = "Enseignants connectés ayant visualisé au moins un cours (repartition cours / utilisateur )";
	   break;
	
	 }
	return $retour;
}

?>
 </div>
<div id="column_right"> <p align="left">

</p></div>
<!-- this layer solve some issue about the css design, forced the #main layer height equal to height of two column layer (#columnt_left and #column_right) inside itself -->
<div class="spacer"></div>
<!-- close #main content -->
</div>
<!-- close #container -->
</div>
</body>
</html>
