<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mir</title>

        <style>
    table {
    font-family: Arial, Helvetica, sans-serif;
    border-collapse: collapse;
    width: 100%;
    }

    table td, table th {
    border: 1px solid #ddd;
    padding: 8px;
    }

    table tr:nth-child(even){background-color: #f2f2f2;}

    table tr:hover {background-color: #ddd;}

    table th {
    padding-top: 12px;
    padding-bottom: 12px;
    text-align: left;
    background-color: #04AA6D;
    color: white;
    }
</style>
</head>
<body>
    <h1>Gestion des signalements de messages</h1>
    <table>
        <tr>
            <th>Message signalé</th>
            <th>Détails fournis</th>
            <th>Btn suppr msg</th>
        </tr>
        <?php echo $reportMessagesListHTML; ?>
    </table>

    <h1>Joueurs souvent signalés</h1>
    <table>
        <tr>
            <th>id joueur</th>
            <th>nombre de signalements depuis 6 mois</th>
            <th>Btn donner avertissement</th>
            <th>Btn ban 1 jour</th>
            <th>Btn ban 1 semaine</th>
            <th>Btn ban à vie</th>
        </tr>
        <?php echo $frequentlyReportedPlayersListHTML; ?>
    </table>
</body>
</html>