<?php

if(!isset($_SESSION['id_player'])){
    $_SESSION['errorMsg'] = 'Déconnecté pour cause d\'inactivité';
    header("location: index.php?page=login");
    die();
}

// Prevent backtab
if(isset($_SESSION['player_area']) && $_SESSION['player_area'] != 'home')
{
    header("location: index.php?page=".$_SESSION['player_area']);
    die();
}

#region File inclusions

$database = new Database();
$log = new Log();
$cache = new Cache($_SESSION['id_city']);
$news = new News($database, $log);
$city = new City($database, $log, $news);

#endregion

function getNewsList($database, $log)
{
    $query="SELECT id_new, new_date, new_type, new_descr, new_link
        FROM game_news 
        ORDER BY id_new DESC
        LIMIT 20";
	$result=[];
	try
	{
		$res = $database->mysql->query($query);
		if($res)
		{
			while($data=$res->fetch(PDO::FETCH_ASSOC))
			{
                $result[]=$data; //OBLIGATOIRE POUR FOREACH 
			}
		}
	}
	catch (PDOException $e)
	{
		$_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";        
		$err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();         
		$log->addLog($err, 'error', $_SERVER['REMOTE_ADDR']);
		die();
	}
	return $result;
}
