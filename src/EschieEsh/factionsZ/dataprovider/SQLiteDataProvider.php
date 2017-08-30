<?php
namespace EschieEsh\factionsZ\dataprovider
use EschieEsh\factionsZ\factionsZ;

class SQLiteDataProvider extends DataProvider
{
    /** @var \SQLite3 */
    private $db;
    /** @var \SQLite3Stmt */
    private $sqlCreateFaction_PlayerData, $sqlCreateFaction_FactionData, $sqlGetPlayerFaction, $sqlFactionExists,
            $sqlDeleteFaction_PlayerData, $sqlDeleteFaction_FactionData, $sqlKickPlayer, $sqlInvitePlayer, $sqlGetDescription,                 $sqlSetDescription, $sqlAcceptInvite, $sqlDenyInvite, $sqlDeleteInvitation;
    /**
    * @param factionsZ $plugin
    */
    public function __construct(factionsZ $plugin)
    {
        parent::__construct($plugin);
        $this->db = new \SQLite3($this->plugin->getDataFolder() . "factionsZ.db");
        $this->db->exec(
            "CREATE TABLE IF NOT EXISTS players
            (player TEXT PRIMARY KEY COLLATE NOCASE, faction TEXT, rank TEXT);"
        );
        $this->db->exec(
            "CREATE TABLE IF NOT EXISTS factions
            (faction TEXT PRIMARY KEY, description TEXT, home TEXT, claim TEXT, str TEXT, allies TEXT);"
        );
        $this->db->exec(
            "CREATE TABLE IF NOT EXISTS confirm
            (player TEXT PRIMARY KEY COLLATE NOCASE, faction TEXT, invitedby TEXT, timestamp INT);"
        );
        $this->sqlCreateFaction_PlayerData = $this->db->prepare(
            "INSERT OR REPLACE INTO players (player, faction, rank) VALUES 
            (:player, :faction, :rank);"
        );
        $this->sqlCreateFaction_FactionData = $this->db->prepare(
            "INSERT OR REPLACE INTO factions (faction, description, home, claim, strength, allies) VALUES
            (:faction, :description, :home, :claim, :strength, :allies);"
        );
        $this->sqlInvitePlayer = $this->db->prepare(
            "INSERT OR REPLACE INTO confirm (player, faction, invitedby, timestamp) VALUES
            (:player, :faction, :invitedby, :timestamp);"
        );
        $this->sqlInvitedTo = $this->db->prepare(
            "SELECT faction FROM confirm WHERE player = :player;"
        );
        
        $this->sqlDeleteInvitation = $this->db->prepare(
            "DELETE * FROM confirm WHERE player = :player;"
        );
        $this->sqlFactionExists = $this->db->prepare(
            "SELECT faction FROM factions WHERE faction = :faction;"
        );
        $this->sqlGetPlayerFaction = $this->db->prepare(
            "SELECT faction FROM players WHERE player = :player;"
        );
        $this->sqlDeleteFaction_FactionData = $this->db->prepare(
            "DELETE * FROM factions WHERE faction = :faction;"
        );
        $this->sqlDeleteFaction_PlayerData = $this->db->prepare(
            "DELETE * FROM players WHERE faction = :faction;"
        );
        $this->sqlKickPlayer = $this->db->prepare(
            "DELETE * FROM players WHERE player = :player;"
        );
        $this->sqlSetDescription = $this->db->prepare(
            "INSERT OR REPLACE INTO factions (faction, description) VALUES (:faction, :description);"
        );
        $this->sqlGetDescription = $this->db->prepare(
            "SELECT description FROM factions WHERE faction = :faction;"
        );
        $this->sqlGetFactionInfo = $this->db->prepare(
            "SELECT * FROM factions WHERE faction = :faction;"
        );
        $this->sqlGetPlayersByRank = $this->db->prepare(
            "SELECT player FROM players WHERE rank = :rank;"
        );
        $this->plugin->getLogger()->debug("SQLite data provider registered");
    }
    /**
    * @param string $player
    * @param string $faction
    */
    public function createFaction(string $player, string $faction)
    {
    
        $stmt = $this->sqlCreateFaction_PlayerData;
        $stmt->bindValue(":player", $player);
        $stmt->bindValue(":faction", $faction);
        $stmt->bindValue(":rank", "Leader");
        $stmt->reset();
        $stmt->execute();
        $stmt = $this->sqlCreateFaction_FactionData;
        $stmt->bindValue(":faction", $faction);
        $stmt->bindValue(":strength", 0);
        $stmt->bindValue(":description", "None");
        $stmt->reset();
        $stmt->execute();
    }
    /**
    * @param string $player
    * @param string $faction
    * @param string $invitedby
    */
    public function invitePlayer(string $player, string $faction, string $invitedby)
    {
        $this->sqlInvitePlayer->bindValue(":player", $player);
        $this->sqlInvitePlayer->bindValue(":faction", $faction);
        $this->sqlInvitePlayer->bindValue(":invitedby", $invitedby);
        $this->sqlInvitePlayer->bindValue(":timestamp", time());
    
        $this->sqlInvitePlayer->reset();
        $this->sqlInvitePlayer->execute();
    }
    /**
    * @param string $player
    * @param bool $state
    * @return bool
    */
    public function inviteAccepted(string $player, bool $state) : bool
    {
        
        if ($state)
        {
            $this->sqlInvitedTo->bindValue(":player", $player);
            $this->sqlInvitedTo->reset();
            $result = $this->sqlInvitedTo->execute();
            $result->fetchArray(SQLITE3_ASSOC);
            
            $this->joinFaction($player, $result["faction"]);
        }
        
        $this->sqlDeleteInvitation->bindValue(":player", $player);
        $this->sqlDeleteInvitation->reset();
        $this->sqlDeleteInvitation->execute();
        
        return $state;
    }
    /**
    * @param string $player
    * @param string $faction
    */
    public function joinFaction(string $player, string $faction)
    {
        $stmt = $this->sqlCreateFaction_PlayerData;
        $stmt->bindValue(":player", $player);
        $stmt->bindValue(":faction", $faction);
        $stmt->bindValue(":rank", "Member");
        $stmt->reset();
        $stmt->execute();
    }
    /**
    * @param string $player
    */
    public function kickPlayer(string $player)
    {
        $this->sqlKickPlayer->bindValue(":player", $player);
        $this->sqlKickPlayer->reset();
        $this->sqlKickPlayer->execute();
    }
    /**
    * @param string $faction
    */
    public function deleteFaction(string $faction)
    {
        $stmts = [$this->sqlDeleteFaction_PlayerData, $this->sqlDeleteFaction_FactionData];
        foreach ($stmt in $stmts)
        {
            $stmt->bindValue(":faction", $faction);
            $stmt->reset();
            $stmt->execute();
        }
    }
    /**
    * @param string $faction
    * @return string
    */
    public function getDescription(string $faction) : string
    {
        $this->sqlGetDescription->bindValue(":faction", $faction);
        $this->sqlGetDescription->reset();
        $result = $this->sqlGetDescription->execute();
        $result->fetchArray(SQLITE3_ASSOC);
        
        return $result["description"];
        
    }
    /**
    * @param string $faction
    * @param string $description
    */
    public function setDescription(string $faction, string $description)
    {
        $this->sqlSetDescription->bindValue(":faction", $faction);
        $this->sqlSetDescription->bindValue(":description", $description);
        $this->sqlSetDescription->reset();
        $this->sqlSetDescription->execute(); 
    }
    /**
    * @param string $player
    */
    public function getFaction(string $player)
    {
        $this->sqlGetPlayerFaction->bindValue(":player", $player);
        $this->sqlGetPlayerFaction->reset();
        $result = $this->sqlGetPlayerFaction->execute();
        $result->fetchArray(SQLITE3_ASSOC);
        
        if (empty($result["faction"]))
        {
            return false;
        }
        
        return $result["faction"];
    }
    /**
    * @param string $faction
    * return []
    */
    public function getFactionInfo(string $faction) : array
    {
        $this->sqlGetFactionInfo->bindValue(":faction", $faction);
        $this->sqlGetFactionInfo->reset();
        $result = $this->sqlGetFactionInfo->execute();
        $result->fetchArray(SQLITE3_ASSOC);
        
        if (empty($result["faction"]))
        {
            return false;
        }
        
        return $result["faction"];
    }
    /**
    * @param string $faction
    */
    public function factionExists(string $faction) : bool
    {
        $this->sqlFactionExists->bindValue(":faction", $faction);
        $this->sqlFactionExists->reset();
        $result = $this->sqlFactionExists->execute();
        $result->fetchArray(SQLITE3_ASSOC);
    
        if (empty($result["faction"]))
        {
            return false;
        }
        return true;
    }
    
    public function close()
    {
        $this->db->close();
        $this->plugin->getLogger()->debug("SQLite database closed!");
    }
}
