<?php
    $active = true;
    $debug = false;
    $debugErrors = false;

    $sqlhost = "localhost";
    $sqlport = 3306;
    $sqluser = "username";
    $sqlpass = "password";
    $sqldb = "database";

    $query = "SELECT ip, port, active, data, players, id, count, display, description, sourceBans, gameME, ts_slots, ts_maxSlots, discordCount, discordInvite, lastscan, lastSuccessScan FROM servers WHERE display = 1 AND description LIKE \"CS:GO%\" OR description LIKE \"TS3%\" OR description LIKE \"Discord%\" ORDER BY ip ASC, port ASC";
    $scanquery = "SELECT last FROM lastscan";

    // Reload page every x Seconds
    $reloadDelay = "60";

    // Remove bots from players count
    $bots = "1";

    // Prevent more players as max players (65/64)
    $fixMorePlayers = "1";

    // Change svg/image size for connect icons
    $svgSize = "36";

    // Show hostname (under icons)?
    $showHostname = "0";

    // Show gametracker icon?
    $showGametracker = "1";

    // Show application icons (Discord/TS3/Games(Classic Offensive/CSS/CSGO))
    $showApplicationicon = "1";

    // Show country flags?
    $showCountryflag = "1";
?>
