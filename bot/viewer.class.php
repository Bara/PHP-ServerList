<?php

    require 'SourceQuery/bootstrap.php';
    
    use xPaw\SourceQuery\SourceQuery;

class viewer {
    private $database;
    public  $sourcequery;

    function __construct() {
        require_once "config.php";

        $this->database = mysqli_connect($sqlhost, $sqluser, $sqlpass, $sqldb, $sqlport);
        if ($this->database->connect_errno) {
            exit();
        }
        $this->sourcequery = new SourceQuery( );
    }

    function __destruct () {
        $this->database->close( );
        $this->sourcequery->Disconnect( );
    }

    public function query($query) {
        $query = $this->database->query($query);
        if(!$query)
            printf("Errormessage: %s\n", $this->database->error);
        else
            return $query;
    }

    public function escape($str) {
        return $this->database->real_escape_string($str);
    }
	
    public function saveSource($id, $data, $players) {
        if(is_array($data)) {
            $data 	    = $this->database->real_escape_string(serialize($data));
            $players    = $this->database->real_escape_string(serialize($players));

            $this->database->query("UPDATE `servers` SET `data` = '$data', `players` = '$players', `lastscan` = UNIX_TIMESTAMP(), `lastSuccessScan` = UNIX_TIMESTAMP() WHERE `id`='$id'");
        }
        else {
            $this->database->query("UPDATE `servers` SET `data` = 'Offline', `lastscan` = UNIX_TIMESTAMP() WHERE `id`='$id'");
        }
    }

    public function saveTS3($id, $slots, $maxSlots, $name) {
        $name 	    = $this->database->real_escape_string(serialize($name));
        if($maxSlots >= "2") {
            $this->database->query("UPDATE `servers` SET `data` = '$name', ts_slots = '$slots', ts_maxSlots = '$maxSlots', `lastscan` = UNIX_TIMESTAMP(), `lastSuccessScan` = UNIX_TIMESTAMP() WHERE `id`='$id'");
        }
        else {
            echo "Offline";
            $this->database->query("UPDATE `servers` SET `data` = '$name', `lastscan` = UNIX_TIMESTAMP() WHERE `id`='$id'");
        }
    }

    public function saveDiscord($id, $name, $count, $invite) {
        $name 	    = $this->database->real_escape_string(serialize($name));
        $invite 	= $this->database->real_escape_string(serialize($invite));
        if(strlen($name) > 6) {
            $this->database->query("UPDATE `servers` SET `data` = '$name', discordCount = '$count', discordInvite = '$invite', `lastscan` = UNIX_TIMESTAMP(), `lastSuccessScan` = UNIX_TIMESTAMP() WHERE `id`='$id'");
        }
        else {
            $this->database->query("UPDATE `servers` SET `data` = 'Offline', `lastscan` = UNIX_TIMESTAMP() WHERE `id`='$id'");
        }
    }


    public function updateGlobalScan() {
        $this->database->query("UPDATE `lastscan` SET `last` = UNIX_TIMESTAMP()");
    }
}
