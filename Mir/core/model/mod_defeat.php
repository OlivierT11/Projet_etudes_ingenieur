<?php

if(!isset($_SESSION['id_player'])){
    $_SESSION['errorMsg'] = 'Déconnecté pour cause d\'inactivité';
    header("location: index.php?page=login");
    die();
}

// This session variable is only used to prevent a redirect infinite loop when the user is on the page defeat (as it calls cron.php)
$_SESSION['city_has_ended'] = 1;

#region File inclusions

$database = new Database();
$log = new Log();
$cache = new Cache($_SESSION['id_city']);
$news = new News($database, $log);
$action = new Action($database, $log);
$stat = new Stat($database, $log, $action);
$statistic = new Statistic($database, $log, $news);
$city = new City($database, $log, $news);

function getCityStats($database)
{
    $query="SELECT id_city_statistic,
        sum(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 1 then amount else 0 end) as a,
        sum(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 2 then amount else 0 end) as b,
        sum(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 3 then amount else 0 end) as c,
        sum(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 4 then amount else 0 end) as d,
        sum(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 5 then amount else 0 end) as e,
        sum(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 6 then amount else 0 end) as f,
        sum(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 7 then amount else 0 end) as g,
        sum(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 8 then amount else 0 end) as h,
        sum(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 9 then amount else 0 end) as i,
        sum(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 10 then amount else 0 end) as j,
        sum(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 11 then amount else 0 end) as k,
        sum(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 12 then amount else 0 end) as l,
        sum(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 13 then amount else 0 end) as m,
        sum(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 14 then amount else 0 end) as n,
        sum(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 15 then amount else 0 end) as o,
        sum(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 16 then amount else 0 end) as p,
        sum(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 17 then amount else 0 end) as q,
        sum(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 18 then amount else 0 end) as r,
        sum(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 19 then amount else 0 end) as s,
        sum(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 20 then amount else 0 end) as t
        FROM statistic_local_player
        GROUP BY id_city";
    $res = $database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $res->execute();
    $result=[];
    $row = $res->fetchAll(PDO::FETCH_OBJ);

    //get the summed amount for the city
    $result[]['amount'] = $row[0]->a;
    $result[]['amount'] = $row[0]->b;
    $result[]['amount'] = $row[0]->c;
    $result[]['amount'] = $row[0]->d;
    $result[]['amount'] = $row[0]->e;
    $result[]['amount'] = $row[0]->f;
    $result[]['amount'] = $row[0]->g;
    $result[]['amount'] = $row[0]->h;
    $result[]['amount'] = $row[0]->i;
    $result[]['amount'] = $row[0]->j;
    $result[]['amount'] = $row[0]->k;
    $result[]['amount'] = $row[0]->l;
    $result[]['amount'] = $row[0]->m;
    $result[]['amount'] = $row[0]->n;
    $result[]['amount'] = $row[0]->o;
    $result[]['amount'] = $row[0]->p;
    $result[]['amount'] = $row[0]->q;
    $result[]['amount'] = $row[0]->r;
    $result[]['amount'] = $row[0]->s;
    $result[]['amount'] = $row[0]->t;

    //add the stat names
    $result[0]['name'] = L('harvested_wood');
    $result[1]['name'] = L('harvested_rare_wood');
    $result[2]['name'] = L('oranges_harvested_outside');
    $result[3]['name'] = L('honey_harvested_outside');
    $result[4]['name'] = L('oranges_produced_in_farm');
    $result[5]['name'] = L('honey_producted_in_farm');
    $result[6]['name'] = L('shadows_slained');
    $result[7]['name'] = L('unexplored_regions_discovered');
    $result[8]['name'] = L('resources_given_to_city');
    $result[9]['name'] = L('upper_armors_crafted');
    $result[10]['name'] = L('lower_armors_crafted');
    $result[11]['name'] = L('helmets_crafted');
    $result[12]['name'] = L('reinforced_masks_crafted');
    $result[13]['name'] = L('shields_crafted');
    $result[14]['name'] = L('spears_crafted');
    $result[15]['name'] = L('oranges_baked');
    $result[16]['name'] = L('honey_refined');
    $result[17]['name'] = L('cakes_baked');
    $result[18]['name'] = L('caves_discovered');
    $result[19]['name'] = L('metal_looted_on_shadows');

    return $result;
}


function getBestPlayerStats($database)
{
    //TODO combiner SUM et MAX dans la même quer^y

    // select the max individual value for each statistic
    $query="SELECT id_city_statistic,
        max(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 1 then amount else 0 end) as a,
        max(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 2 then amount else 0 end) as b,
        max(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 3 then amount else 0 end) as c,
        max(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 4 then amount else 0 end) as d,
        max(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 5 then amount else 0 end) as e,
        max(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 6 then amount else 0 end) as f,
        max(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 7 then amount else 0 end) as g,
        max(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 8 then amount else 0 end) as h,
        max(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 9 then amount else 0 end) as i,
        max(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 10 then amount else 0 end) as j,
        max(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 11 then amount else 0 end) as k,
        max(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 12 then amount else 0 end) as l,
        max(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 13 then amount else 0 end) as m,
        max(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 14 then amount else 0 end) as n,
        max(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 15 then amount else 0 end) as o,
        max(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 16 then amount else 0 end) as p,
        max(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 17 then amount else 0 end) as q,
        max(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 18 then amount else 0 end) as r,
        max(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 19 then amount else 0 end) as s,
        max(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 20 then amount else 0 end) as t
        FROM statistic_local_player
        GROUP BY id_city";
    $res = $database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $res->execute();
    $result=[];
    $row = $res->fetchAll(PDO::FETCH_OBJ);

    //get the summed amount for the city
    $result[]['amount'] = $row[0]->a;
    $result[]['amount'] = $row[0]->b;
    $result[]['amount'] = $row[0]->c;
    $result[]['amount'] = $row[0]->d;
    $result[]['amount'] = $row[0]->e;
    $result[]['amount'] = $row[0]->f;
    $result[]['amount'] = $row[0]->g;
    $result[]['amount'] = $row[0]->h;
    $result[]['amount'] = $row[0]->i;
    $result[]['amount'] = $row[0]->j;
    $result[]['amount'] = $row[0]->k;
    $result[]['amount'] = $row[0]->l;
    $result[]['amount'] = $row[0]->m;
    $result[]['amount'] = $row[0]->n;
    $result[]['amount'] = $row[0]->o;
    $result[]['amount'] = $row[0]->p;
    $result[]['amount'] = $row[0]->q;
    $result[]['amount'] = $row[0]->r;
    $result[]['amount'] = $row[0]->s;
    $result[]['amount'] = $row[0]->t;

    //add the stat names
    $result[0]['name'] = L('harvested_wood');
    $result[1]['name'] = L('harvested_rare_wood');
    $result[2]['name'] = L('oranges_harvested_outside');
    $result[3]['name'] = L('honey_harvested_outside');
    $result[4]['name'] = L('oranges_produced_in_farm');
    $result[5]['name'] = L('honey_producted_in_farm');
    $result[6]['name'] = L('shadows_slained');
    $result[7]['name'] = L('unexplored_regions_discovered');
    $result[8]['name'] = L('resources_given_to_city');
    $result[9]['name'] = L('upper_armors_crafted');
    $result[10]['name'] = L('lower_armors_crafted');
    $result[11]['name'] = L('helmets_crafted');
    $result[12]['name'] = L('reinforced_masks_crafted');
    $result[13]['name'] = L('shields_crafted');
    $result[14]['name'] = L('spears_crafted');
    $result[15]['name'] = L('oranges_baked');
    $result[16]['name'] = L('honey_refined');
    $result[17]['name'] = L('cakes_baked');
    $result[18]['name'] = L('caves_discovered');
    $result[19]['name'] = L('metal_looted_on_shadows');

    // select the player id for all max value.
    $query="SELECT id_city_statistic,
        max(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 1 then id_player else 0 end) as a,
        max(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 2 then id_player else 0 end) as b,
        max(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 3 then id_player else 0 end) as c,
        max(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 4 then id_player else 0 end) as d,
        max(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 5 then id_player else 0 end) as e,
        max(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 6 then id_player else 0 end) as f,
        max(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 7 then id_player else 0 end) as g,
        max(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 8 then id_player else 0 end) as h,
        max(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 9 then id_player else 0 end) as i,
        max(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 10 then id_player else 0 end) as j,
        max(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 11 then id_player else 0 end) as k,
        max(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 12 then id_player else 0 end) as l,
        max(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 13 then id_player else 0 end) as m,
        max(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 14 then id_player else 0 end) as n,
        max(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 15 then id_player else 0 end) as o,
        max(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 16 then id_player else 0 end) as p,
        max(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 17 then id_player else 0 end) as q,
        max(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 18 then id_player else 0 end) as r,
        max(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 19 then id_player else 0 end) as s,
        max(case when id_city = ".$_SESSION['id_city']." AND id_statistic = 20 then id_player else 0 end) as t
        FROM statistic_local_player
        GROUP BY id_city";
    $res = $database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $res->execute();
    $row = $res->fetchAll(PDO::FETCH_OBJ);

    $result[0]['id_player'] = $row[0]->a;
    $result[1]['id_player'] = $row[0]->b;
    $result[2]['id_player'] = $row[0]->c;
    $result[3]['id_player'] = $row[0]->d;
    $result[4]['id_player'] = $row[0]->e;
    $result[5]['id_player'] = $row[0]->f;
    $result[6]['id_player'] = $row[0]->g;
    $result[7]['id_player'] = $row[0]->h;
    $result[8]['id_player'] = $row[0]->i;
    $result[9]['id_player'] = $row[0]->j;
    $result[10]['id_player'] = $row[0]->k;
    $result[11]['id_player'] = $row[0]->l;
    $result[12]['id_player'] = $row[0]->m;
    $result[13]['id_player'] = $row[0]->n;
    $result[14]['id_player'] = $row[0]->o;
    $result[15]['id_player'] = $row[0]->p;
    $result[16]['id_player'] = $row[0]->q;
    $result[17]['id_player'] = $row[0]->r;
    $result[18]['id_player'] = $row[0]->s;
    $result[19]['id_player'] = $row[0]->t;

    // select the player name for all ids
    $query="SELECT player_name
        FROM player
        WHERE id_player = :id";
    
    $sth = $database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

    $n = sizeof($result);

    for($i=0; $i<$n; $i++)
    {
        $sth->execute(array(':id' => $result[$i]['id_player']));
        $res = $sth->fetchAll();

        if (isset($res[0]['player_name']))
        {
            $result[$i]['player'] = $res[0]['player_name'];
        }
        else
        {
            $result[$i]['player'] = "Personne";
        }
    }

    return $result;
}
