#!/usr/bin/php
<?php
    ini_set('display_errors', '1');

    require 'viewer.class.php';
    require 'ts3admin.class.php';

    $viewer = new viewer( );

    # Source part
    $result = $viewer->query("SELECT `id`, `ip`, `port`, `queryPort`, `active` FROM `servers` WHERE description LIKE '%%%CS:GO%%%' ORDER BY `id`;");
    while ($row = $result->fetch_object())
    {
        $server = array(
            "id"            => $row->id,
            "ip"            => $row->ip,
            "port"          => $row->port,
            "queryPort"     => $row->queryPort,
            "active"        => $row->active
        );

        try
        {
            if($server['active'] == 1)
            {
                $viewer->sourcequery->Connect($server['ip'], $server['port'], 3, 1);

                $data    = $viewer->sourcequery->GetInfo();
                $players = $viewer->sourcequery->GetPlayers();

                $viewer->saveSource($server['id'], $data, $players);

                echo "\n\n" . $server['ip'] . ":" . $server['port'] . "\n";
            }
        }
        catch( Exception $e )
        {
            $error = $e->getMessage( );
            echo $error;
            
            if (strpos($error, "Failed to read any data from socket") !== "false")
            {
               $viewer->saveSource($server['id'], "offline", "offline");
            }
        }

        $viewer->updateGlobalScan();

    }
    $result->close();

    # Teamspeak part
    $result = $viewer->query("SELECT `id`, `ip`, `port`, `queryPort`, `active` FROM `servers` WHERE description LIKE '%%%TS3%%%' ORDER BY `id`;");
    while ($row = $result->fetch_object())
    {
        $server = array(
            "id"            => $row->id,
            "ip"            => $row->ip,
            "port"          => $row->port,
            "queryPort"     => $row->queryPort,
            "active"        => $row->active
        );

        try
        {
            if($server['active'] == 1)
            {
                $teamspeak = new ts3admin($server['ip'], $server['queryPort']);

                if($teamspeak->getElement('success', $teamspeak->connect()))
                {
                    $teamspeak->login("serveradmin", "8JgIJ60m");

                    $servers = $teamspeak->serverList();
                    $maxSlots = 0;
                    $name = "Offline";

                    foreach($servers['data'] as $instance)
                    {
                        if ($instance['virtualserver_port'] == $server['port'])
                        {
                            $maxSlots = $instance['virtualserver_maxclients'];
                            $name = $viewer->escape($instance['virtualserver_name']);
                        }
                    }

                    $teamspeak->selectServer($server['port']);
                    $clients = $teamspeak->clientList();
                    $slots = count($clients['data']);

                    $viewer->saveTS3($server['id'], $slots, $maxSlots, $name);
                }
                else
                {
                    echo "TS3 Connection failed!";
                }

                echo "\n\n" . $server['ip'] . ":" . $server['port'] . "\n";
            }
        }
        catch( Exception $e )
        {
            echo $e->getMessage( );
        }

        $viewer->updateGlobalScan();

    }
    $result->close();

    # Discord part
    $result = $viewer->query("SELECT `id`, `ip`, `active` FROM `servers` WHERE description LIKE '%%%Discord%%%' ORDER BY `id`;");
    while ($row = $result->fetch_object())
    {
        $server = array(
            "id"            => $row->id,
            "ip"            => $row->ip,
            "active"        => $row->active
        );

        try
        {
            if($server['active'] == 1)
            {
                $jsonIn = file_get_contents('https://discordapp.com/api/guilds/' . $server["ip"] . '/embed.json');
                $JSON = json_decode($jsonIn, true);
                $invite = $JSON['instant_invite'];
                $name = $JSON['name'];
                $membersCount = count($JSON['members']);

		$invite = str_replace("discordapp.com/invite", "discord.gg", $invite);

                $viewer->saveDiscord($server['id'], $name, $membersCount, $invite);

                echo "\n\n" . $server['ip'] . "\n";
            }
        }
        catch( Exception $e )
        {
            echo $e->getMessage( );
        }

        $viewer->updateGlobalScan();

    }
    $result->close();
?>