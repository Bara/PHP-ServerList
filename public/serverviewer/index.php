<?php
    # Depends on your configuration
    require_once "../../bot/config.php";

    if ($active == false)

    {
        die("Under construction!");
    }

    require_once "GeoIP2/vendor/autoload.php";
    use GeoIp2\Database\Reader;
    
    if ($debug)
    {
        ini_set('display_errors', '1');
    }
    else if ($debug == false && $debugErrors)
    {
        ini_set('display_errors', '1');
    }
    else if ($debug == false && $debugErrors == false)
    {
        ini_set('display_errors', '0');
    }

    $cplayers = 0;
    $cmaxplayers = 0;
    $servers = 0;
    $sonline = 0;

    $aServers = array();

    $reloadPage = $_SERVER['PHP_SELF'];

    // Taken from https://codeforgeek.com/time-ago-implementation-php/
    function get_timeago( $ptime )
    {
        $estimate_time = time() - $ptime;

        if ( $estimate_time < "1" )
        {
            return 'less than 1 second ago';
        }

        $condition = array(
            12 * 30 * 24 * 60 * 60  =>  'year',
            30 * 24 * 60 * 60       =>  'month',
            24 * 60 * 60            =>  'day',
            60 * 60                 =>  'hour',
            60                      =>  'minute',
            1                       =>  'second'
        );

        foreach( $condition as $secs => $str )
        {
            $d = $estimate_time / $secs;

            if ( $d >= 1 )
            {
                $r = round( $d );
                return '' . $r . ' ' . $str . ( $r > 1 ? 's' : '' ) . ' ago';
            }
        }
    }
?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <title>Serverlist | DNG | DeadNationGaming</title>

        <meta http-equiv="refresh" content="<?php echo $reloadDelay?>;URL='<?php echo $reloadPage?>'">

        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    </head>

    <body style="background-color: rgba(0,0,0,0);">
        <div style="margin-left:auto;margin-right:auto;width:100%;">
            <?php
                $mysqli = new mysqli($sqlhost, $sqluser, $sqlpass, $sqldb, $sqlport);

                if (mysqli_connect_errno())
                {
                    printf("<strong><font color='red'>Connect failed: %s\n</font></strong>", mysqli_connect_error());
                    exit();
                }

                if ($result = $mysqli->query($query))
                {
                    while ($row = $result->fetch_object())
                    {
                        $ip                 = $row->ip;
                        $port               = $row->port;
                        $id                 = $row->id;
                        $active             = $row->active;
                        $count              = $row->count;
                        $display            = $row->display;
                        $description        = $row->description;
                        $sourceBansLink     = $row->sourceBans;
                        $gameMELink         = $row->gameME;
                        $tsSlots            = $row->ts_slots;
                        $tsMaxSlots         = $row->ts_maxSlots;
                        $discordCount       = $row->discordCount;
                        $discordInvite      = $row->discordInvite;
                        $lastscan           = $row->lastscan;
                        $lastSuccessScan    = $row->lastSuccessScan;
                        $data               = @unserialize($row->data);
                        $players            = array(@unserialize($row->players));

                        if ($active == 0)
                        {
                            continue;
                        }

                        if ($display == 0)
                        {
                            continue;
                        }

                        ini_set('date.timezone', 'Europe/Berlin');

                        if ($debug == 1)
                        {
                            echo '<pre>';
                            echo "IP: $ip - Port: $port <br/>";
                            print_r($data);
                            print_r($players);
                            echo '</pre>';
                        }

                        $size = count($players);
                        $nplayers = 0;
                        $mplayers = 0;
                        $online = 0;
                        $game = "";
                        $file = "";
                        
                        if (isset($data['ModDir']))
                        {
                            $file = "img/games/" . $data['ModDir'] . ".png";
                        }

                        if (file_exists($file))
                        {
                            $game = "<img src=\"" . $file . "\" alt=\"" . $data["HostName"] . "\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"" . $data["ModDesc"] . "\"/>";
                        }
                        else if ((strpos($description, 'TS3') !== false) || (strpos($description, 'Teamspeak') !== false))
                        {
                            $game = "<img src=\"img/games/ts3.png\" alt=\"" . $description . "\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"Teamspeak\"/>";
                        }
                        else if (strpos($description, 'Discord') !== false)
                        {
                            $game = "<img src=\"img/games/discord.png\" alt=\"" . $description . "\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"Discord\"/>";
                        }
                        else
                        {
                            $game = "";
                        }

                        // Save players count
                        if (isset($data['Players']) > 0)
                        {
                            $nplayers = $data['Players'];
                        }
                        else if (strpos($description, 'TS3') !== false)
                        {
                            $nplayers = $tsSlots;
                        }
                        else if (strpos($description, 'Discord') !== false)
                        {
                            $nplayers = $discordCount;
                        }

                        // Fix a bug (no players on server)
                        if ($nplayers == "")
                        {
                            $nplayers = "0";
                        }

                        // Save players count
                        if (isset($data['MaxPlayers']) > 0)
                        {
                            $mplayers = $data['MaxPlayers'];
                        }
                        else if (strpos($description, 'TS3') !== false)
                        {
                            $mplayers = $tsMaxSlots;
                        }
                        else if (strpos($description, 'Discord') !== false)
                        {
                            $mplayers = 0;
                        }

                        // Fix a bug (no players on server)
                        if ($mplayers == "")
                        {
                            $mplayers = "0";
                        }

                        // Remove bots from stats (no fake data please)
                        if ($bots == "1")
                        {
                            if (isset($data['Bots']) > 0)
                            {
                                $nplayers = $nplayers - $data['Bots'];
                            }
                        }

                        // Don't show more players as we've slots
                        if ($fixMorePlayers)
                        {
                            if ($nplayers > $mplayers)
                            {
                                $nplayers = $mplayers;
                            }
                        }
                        
                        if ($mplayers > "0")
                        {
                            $onepercent = 100 / $mplayers;

                            if ($nplayers > "0")
                            {
                                $percent = round($nplayers * $onepercent, 0);
                            }
                            else
                            {
                                $percent = "0";
                            }
                            
                        }
                        else
                        {
                            $onepercent = "0";
                            $percent = "0";
                        }

                        $flag = "";

                        if (strpos($description, 'Discord') === false)
                        {
                            $reader = new Reader('GeoIP2/GeoLite2-Country.mmdb');
                            $fixIP = gethostbyname($ip);
                            $record = $reader->country($fixIP);
                            $flag = "<img src=\"img/flags/" . strtolower($record->country->isoCode) . ".png\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"" . $record->country->name. "\" alt=\"" . $record->country->name. "\"/>";
                        }

                        $hostname = "";

                        if (is_array($data) || (strpos($description, 'TS3') !== false) || (strpos($description, 'Discord') !== false))
                        {
                            if ($nplayers > 0 && isset($data['Password']) == 0)
                            {
                                $status = "<span class='badge badge-success'>Online</span>";
                            }
                            else if (isset($data['Password']) == 1)
                            {
                                $status = "<span class='badge badge-warning'><img src='img/password.png' alt='Password'> Online</span>";
                            }
                            else
                            {
                                $status = "<span class='badge badge-secondary'>Online</span>";
                            }

                            $sonline++;

                            if (isset($data['HostName']))
                            {
                                $hostname = $data['HostName'];
                            }
                            else
                            {
                                $hostname = "Invalid hostname";
                            }
                            $online = 1;
                        }
                        else
                        {
                            $status = "<span class='badge badge-danger'>Offline</span>";
                            $hostname = "<font color='tomat2'><strong>$description</strong></font>";
                        }
                        
                        if ($online && (is_array($data) || (strpos($description, 'TS3') !== false)))
                        {
                            $cplayers += $nplayers;
                            $cmaxplayers += $mplayers;
                        }
                        
                        $servers++;
                        $sourceBans = "";
                        $gameME = "";
                        $gametracker = "";

                        $steam = "<a href=\"steam://connect/$ip:$port/\" target=\"_blank\">
                                    <svg width=" . $svgSize . " height=" . $svgSize . " xmlns=\"http://www.w3.org/2000/svg\" xmlns:xlink=\"http://www.w3.org/1999/xlink\">
                                    <image xlink:href=\"img/steam.svg\" height=" . $svgSize . " width=" . $svgSize . " data-toggle=\"tooltip\" data-placement=\"top\" title=\"Join server\" />
                                    </svg></a>";

                        if ($showGametracker === "1")
                        {
                            $gametracker = "<a href=\"http://www.gametracker.com/server_info/$ip:$port/\" target=\"_blank\">
                                            <svg width=" . $svgSize . " height=" . $svgSize . " xmlns=\"http://www.w3.org/2000/svg\" xmlns:xlink=\"http://www.w3.org/1999/xlink\">
                                            <image xlink:href=\"img/gametracker.png\" height=" . $svgSize . " width=" . $svgSize . " data-toggle=\"tooltip\" data-placement=\"top\" title=\"Gametracker\" />
                                            </svg></a>";
                        }

                        if (strlen($sourceBansLink) > 1)
                        {
                            // $sourceBans = "<a href='$sourceBansLink' target='_blank'><img src='img/bans.png' alt='' /></a>";
                            $sourceBans = "<a href=" . $sourceBansLink . " target=\"_blank\">
                                            <svg width=" . $svgSize . " height=" . $svgSize . " xmlns=\"http://www.w3.org/2000/svg\" xmlns:xlink=\"http://www.w3.org/1999/xlink\">
                                            <image xlink:href=\"img/hammer.png\" height=" . $svgSize . " width=" . $svgSize . " data-toggle=\"tooltip\" data-placement=\"top\" title=\"Banlist\" />
                                            </svg></a>";
                        }

                        if (strlen($gameMELink) > 1)
                        {
                            $gameME = "<a href=" . $gameMELink . " target=\"_blank\">
                                            <svg width=" . $svgSize . " height=" . $svgSize . " xmlns=\"http://www.w3.org/2000/svg\" xmlns:xlink=\"http://www.w3.org/1999/xlink\">
                                            <image xlink:href=\"img/stats.png\" height=" . $svgSize . " width=" . $svgSize . " data-toggle=\"tooltip\" data-placement=\"top\" title=\"Statistics\" />
                                            </svg></a>";
                        }

                        if (is_array($players))
                        {
                            foreach( $players as $player )
                            {
                                $pl_name = "";
                                
                                if (isset($player['Name']) && strlen($player['Name']) > "1")
                                {
                                    $pl_name = htmlspecialchars($player['Name']);
                                }
                                else
                                {
                                    $pl_name = "<u>no name</u>";
                                }
                            }
                        }

                        $map = "";

                        if (isset($data['Map']))
                        {
                            $map = $data['Map'];
                        }
                        else
                        {
                            $map = "Invalid map";
                        }

                        $wMap = explode("/", $map);

                        if (isset($wMap['1']) && is_numeric($wMap['1']))
                        {
                            $map = $wMap['2'];
                        }

                        // Fix some teamspeak and discord stuff
                        if (strpos($description, 'TS3') !== false)
                        {
                            $hostname = unserialize($row->data);
                            $map = "";
                            
                            $steam = "<a href=\"ts3server://$ip?port=$port\" target=\"_blank\">
                                        <svg width=" . $svgSize . " height=" . $svgSize . " xmlns=\"http://www.w3.org/2000/svg\" xmlns:xlink=\"http://www.w3.org/1999/xlink\">
                                        <image xlink:href=\"img/ts3.svg\" height=" . $svgSize . " width=" . $svgSize . " data-toggle=\"tooltip\" data-placement=\"top\" title=\"Join server\" />
                                        </svg></a>";
                            $application = "2ts3";
                        }
                        else if (strpos($description, 'Discord') !== false)
                        {
                            $hostname           = unserialize($row->data);
                            $discordInvite      = unserialize($row->discordInvite);
                            $map                = "";
                            $ip                 = $discordInvite;
                            $gametracker        = "";
                            $steam              = "";

                            if (strlen($discordInvite) > "3")
                            {
                                
                                $steam = "<a href=\"$discordInvite\" target=\"_blank\">
                                            <svg width=" . $svgSize . " height=" . $svgSize . " xmlns=\"http://www.w3.org/2000/svg\" xmlns:xlink=\"http://www.w3.org/1999/xlink\">
                                            <image xlink:href=\"img/discord.svg\" height=" . $svgSize . " width=" . $svgSize . " data-toggle=\"tooltip\" data-placement=\"top\" title=\"Join server\" />
                                            </svg></a>";
                            }

                            $application = "1discord";
                        }
                        else if (strpos($description, 'CS:GO') !== false)
                        {
                            $application = "3csgo";
                        }

                        $aServer = [
                            "status"            => $status,
                            "flag"              => $flag,
                            "game"              => $game,
                            "hostname"          => $hostname,
                            "map"               => $map,
                            "nplayers"          => $nplayers,
                            "mplayers"          => $mplayers,
                            "id"                => $id,
                            "ip"                => $ip,
                            "port"              => $port,
                            "percent"           => $percent,
                            "steam"             => $steam,
                            "gametracker"       => $gametracker,
                            "sourceBans"        => $sourceBans,
                            "gameME"            => $gameME,
                            "application"       => $application,
                            "description"       => $description,
                            "lastscan"          => $lastscan,
                            "lastSuccessScan"   => $lastSuccessScan,
                            "online"            => $online
                        ];
                        if ($debug)
                        {
                            echo "<pre>";
                            print_r($aServer);
                            echo "</pre>";
                        }
                        array_push($aServers, $aServer);
                    }
                    $result->close();
                }
                $mysqli->close();
            ?>

            <div class="row">
            <?php
                function make_cmp(array $sortValues)
                {
                    return function ($a, $b) use (&$sortValues)
                    {
                        foreach ($sortValues as $column => $sortDir)
                        {
                            $diff = strcmp($a[$column], $b[$column]);
                            if ($diff !== 0)
                            {
                                if ('asc' === $sortDir)
                                {
                                    return $diff;
                                }
                                return $diff * -1;
                            }
                        }
                        return 0;
                    };
                }

                // TODO: Add config option
                usort($aServers, make_cmp(['application' => "asc", 'nplayers' => "desc", 'online' => "desc"]));

                $imageMap = "";

                foreach($aServers as $server)
                {
                    $list_players = "";
                    $list_scores = "";
                    $list_times = "";
                    $ipPort = "";
                    $players = "";
                    
                    // Format some outputs (required for better discord support)...
                    if ($server['port'] == "0")
                    {
                        $ipPort = "<a href='" . $server['ip'] . "' target='_blank'>" . str_replace("https://", "", $server['ip']) . "</a>";
                        $players = $server['nplayers'];
                        $server['percent'] = 100;
                    }
                    else if ($server['port'] == "9987")
                    {
                        $ipPort = "<a href='ts3server://" . $server['ip'] . "?port=" . $server['port'] . "' target='_blank'>" . $server['ip'] . "</a>";
                        $players = $server['nplayers'] . "/" . $server['mplayers'];
                    }
                    else
                    {
                        $ipPort = $server['ip'] . ":" . $server['port'];
                        $players = $server['nplayers'] . "/" . $server['mplayers'];
                    }

                    $first = false;

                    $imageMap = "";
                    $statusCard = "";
                    $mapCard = "";
                    $playersCard = "";
                    $buttonsCard = "";
                    $hostCard = "";

                    if ($showApplicationicon === "1")
                    {
                        $gameCard = "<div id=\"textbox\">
                            <p style=\"float: left;margin-bottom:0px;\" class=\"text-white\">Application:</p>
                            <p style=\"float: right;margin-bottom:0px;\" class=\"text-success\">" . $server['game'] . "</p>
                            </div><div style=\"clear: both;\"></div>";
                    }

                    if ($showCountryflag === "1")
                    {
                        $countryCard = "<div id=\"textbox\">
                            <p style=\"float: left;margin-bottom:0px;\" class=\"text-white\">Country:</p>
                            <p style=\"float: right;margin-bottom:0px;\" class=\"text-success\">" . $server['flag'] . "</p>
                            </div><div style=\"clear: both;\"></div>";
                    }

                    if ($showHostname === "1")
                    {
                        $hostCard = "</br> <p style=\"text-align:center;margin-bottom:0px;\" class=\"text-white\">" . $server['ip'] . ":" . $server['port'] . "</p>";
                    }

                    $lastscanTime = get_timeago($server['lastscan']);
                    $lastSuccessScanTime = "Success scan: ". get_timeago($server['lastSuccessScan']);
                    $updateCard = "<div id=\"textbox\">
                                    <p style=\"float: left;margin-bottom:0px;\" class=\"text-white\">Last Scan:</p>
                                    <p style=\"float: right;margin-bottom:0px;\" class=\"text-white\" data-toggle=\"tooltip\" title=\"$lastSuccessScanTime\">$lastscanTime</p>
                                    </div><div style=\"clear: both;\"></div>";

                    if (strpos($server['status'], 'Offline') !== false)
                    {
                        $statusCard = "<div id=\"textbox\">
                                        <p style=\"float: left;margin-bottom:0px;\" class=\"text-white\">Status:</p>
                                        <p style=\"float: right;margin-bottom:0px;\" class=\"text-danger\">Offline</p>
                                        </div><div style=\"clear: both;\"></div>";
                    }
                    else
                    {
                        $statusCard = "<div id=\"textbox\">
                                        <p style=\"float: left;margin-bottom:0px;\" class=\"text-white\">Status:</p>
                                        <p style=\"float: right;margin-bottom:0px;\" class=\"text-success\">Online</p>
                                        </div><div style=\"clear: both;\"></div>";

                        $mapCard = "<div id=\"textbox\">
                                        <p style=\"float: left;margin-bottom:0px;\" class=\"text-white\">Map:</p>
                                        <p style=\"float: right;margin-bottom:0px;\" class=\"text-white\">" . $server['map'] . "</p>
                                        </div><div style=\"clear: both;\"></div>";

                        $playersCard = "<div id=\"textbox\">
                                        <p style=\"float: left;margin-bottom:0px;\" class=\"text-white\">Players:</p>
                                        <p style=\"float: right;margin-bottom:0px;\" class=\"text-white\">$players</p>
                                        </div><div style=\"clear: both;\"></div>";
                    }

                    if (strpos($server['description'], 'Discord') !== false)
                    {
                        $imageMap = "<svg width=\"320px\" height=\"178px\" xmlns=\"http://www.w3.org/2000/svg\" xmlns:xlink=\"http://www.w3.org/1999/xlink\">
                                    <image xlink:href=\"img/maps/discord.png\" height=\"178px\" width=\"320px\" alt=\"" . $server['description'] . "\"/>
                                    </svg></a>";
                        $buttonsCard = "</br> <p style=\"text-align:center;margin-bottom:0px;\">" . $server['steam'] . "</p>";
                        $mapCard = "";
                        $countryCard = "";
                    }
                    else if ((strpos($server['description'], 'TS3') !== false) || (strpos($server['description'], 'Teamspeak') !== false))
                    {
                        $imageMap = "<svg width=\"320px\" height=\"178px\" xmlns=\"http://www.w3.org/2000/svg\" xmlns:xlink=\"http://www.w3.org/1999/xlink\">
                                <image xlink:href=\"img/maps/ts3.png\" height=\"178px\" width=\"320px\" alt=\"" . $server['description'] . "\"/>
                                </svg></a>";
                        $buttonsCard = "</br> <p style=\"text-align:center;margin-bottom:0px;\">" . $server['steam'] . " " . $server['gametracker'] . " " . $server['sourceBans'] . "</p>";
                        $mapCard = "";
                    }
                    else if (strpos($server['status'], 'Offline') !== false)
                    {
                        $imageMap = "<svg width=\"320px\" height=\"178px\" xmlns=\"http://www.w3.org/2000/svg\" xmlns:xlink=\"http://www.w3.org/1999/xlink\">
                                    <image xlink:href=\"img/maps/server-offline.png\" height=\"178px\" width=\"320px\" alt=\"" . $server['description'] . "\"/>
                                    </svg></a>";
                    }
                    else
                    {
                        $imageMap = "<svg width=\"320px\" height=\"178px\" xmlns=\"http://www.w3.org/2000/svg\" xmlns:xlink=\"http://www.w3.org/1999/xlink\">
                                    <image xlink:href=\"img/maps/no-image.png\" height=\"178px\" width=\"320px\" alt=\"" . $server['description'] . "\"/>
                                    </svg></a>";

                        $buttonsCard = "</br> <p style=\"text-align:center;margin-bottom:0px;\">" . $server['steam'] . " " . $server['gametracker'] . " " . $server['sourceBans'] . " " . $server['gameME'] . "</p>";
                    }

                    echo "
                        <div class='col-sm-3' style='padding:12px'>
                            <div class='card text-white bg-dark' style='border-width:0px !important;width:20rem;'>
                                $imageMap
                                <div class='card-body'>
                                    <h5 class='card-title' style='text-align:center;'>" . $server['hostname'] . "</h5>
                                    $statusCard
                                    $countryCard
                                    $gameCard
                                    $mapCard
                                    $playersCard
                                    $updateCard
                                    $buttonsCard
                                    $hostCard
                                </div>
                            </div>
                        </div>
                    ";
                }
            ?>

            </div>

            <?php 
                $mysqli = new mysqli($sqlhost, $sqluser, $sqlpass, $sqldb, $sqlport);

                if (mysqli_connect_errno())
                {
                    printf("<strong><font color='red'>Connect failed: %s\n</font></strong>", mysqli_connect_error());
                    exit();
                }
            
                $last = 0;
            
                if ($result = $mysqli->query($scanquery))
                {
                    while ($row = $result->fetch_object())
                    {
                        $last = $row->last;
                    }
                    $result->close();
                }
                $mysqli->close();
                echo "<br>";
                echo "<center><h8 style='color:#bebebe;'><strong>Last Scan:</strong> " . date('d M Y H:i:s', $last) . " | <strong>Servers online:</strong> $sonline <strong>Servers:</strong> $servers | <strong>Players online:</strong> $cplayers <strong>Max. Players:</strong> $cmaxplayers</h8>";
            ?>
        </div>
        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

        <script>
            $(document).ready(function(){
                $('[data-toggle="tooltip"]').tooltip();   
            });
        </script>
</html>
