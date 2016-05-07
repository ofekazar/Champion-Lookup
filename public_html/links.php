<!doctype html>
<html>
  <head>
    <title>Champion Lookup</title>
    <link href='http://fonts.googleapis.com/css?family=Oswald:400,300' rel='stylesheet'>
    <link href='https://fonts.googleapis.com/css?family=Montserrat:400,500' rel='stylesheet' type='text/css'>
    <link href='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css' rel="stylesheet">
    <link href="style.css" rel="stylesheet">
  </head>
  <body>
    <div class="header">
      <div class="container">
        <div class="row">
          <div class="col-xs-4 col-md-4">
            <h1 class="site_title">Champion Lookup</h1>
          </div>
            <div class="col-xs-8 col-md-8">
            <ul class="menu">
              <li><a href="index.html">Home</a></li>
              <li><a href="about.html">About</a></li>
              <li><a href="contact.html">Contact Us</a></li>
              <li><a href="updates.html">Updates</a></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
    
    <?php
      require_once '../riot_api/CONSTS.php';
      require_once '../riot_api/riot_api.php';

      $id = htmlspecialchars($_GET["id"]);
      $name = htmlspecialchars($_GET["name"]);
      $key = htmlspecialchars($_GET["key"]);
      $title = htmlspecialchars($_GET["title"]);
      $title = strtoupper(substr($title, 0, 1)).substr($title, 1);

      $riotAPI = new RiotAPI($API_KEY);
      $tips = $riotAPI->get_champion_tips($id, 10);
    ?>

    <div class="container">
      <div class="row">
        <h1 style="text-align: center;"><?php echo $name." - ".$title;?></h1>
      </div>
    </div>

    <div class="container">
      <div class="row">
        <div class="col-xs-4 col-md-4">
          <img class="splash" src=<?php echo "\"http://ddragon.leagueoflegends.com/cdn/img/champion/loading/".$key."_0.jpg\"";?> alt=<?php echo $key.".jpg";?> />
        </div>
        <div class="col-xs-8 col-md-8">
          <h3 style="text-align: center;"><?php echo "Learn About ".$name;?></h3>
          <h4><a href=<?php echo "\"http://gameinfo.na.leagueoflegends.com/en/game-info/champions/".strtolower($key)."/\""?>>League of Legends</a> - Champion stats, Abilities and Lore</h4>
          <h4><a href=<?php echo "\"http://leagueoflegends.wikia.com/wiki/".$key."\"";?>>LOL Wiki</a> - Wiki page about <?php echo $name;?></h4> 
          <h4><a href=<?php echo "\"https://www.youtube.com/results?search_query=league+of+legends+".$key."+guide\""?>>YouTube</a> - Automatcly search for a guide on youtube</h4>
          <h4><a href=<?php echo "\"http://www.lolking.net/guides/champion/".strtolower($key)."\""?>>LOL King</a> / <a href=<?php echo "\"http://www.mobafire.com/league-of-legends/".strtolower($key)."-guide\""?>>Moba Fire</a> - Community Guides and Builds</h4>
          <h4><a href=<?php echo "\"http://probuilds.net/champions/details/".$key."\"";?>>Pro Builds</a> - Builds and Stats from the pros</h4>

          <h3 style="text-align: center;">Ally Tips</h3>
          <ul>
            <?php 
              foreach ($tips["allytips"] as $allytip) {
                echo "<li style=\"font-size: 16px;\">".$allytip."</li>";
              }
            ?>
          </ul>

          <h3 style="text-align: center;">Enemy Tips</h3>
          <ul>
            <?php 
              foreach ($tips["enemytips"] as $enemytip) {
                echo "<li style=\"font-size: 16px;\">".$enemytip."</li>";
              }
            ?>
          </ul>

        </div>
      </div>
    </div>


  </body>
</html>