<?php
namespace EschieEsh\factionsZ\subcommand;

use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\command\CommandSender;

class CreateSubCommand extends SubCommand
{
    /**
    * @param CommandSender $sender
    * @return bool
    */
    public function canUse(CommandSender $sender)
    {
        return $sender->hasPermission("factionsZ.command.create");
    }
    /**
    * @param CommandSender $sender
    * @param string[] $args
    * @return bool
    */
    public function execute(CommandSender $sender, array $args)
    {
        if (empty($args))
        {
            return false;
        }
        
        $faction = $args[0];
        
        if (!(ctype_alnum($faction))) 
        {
            $sender->sendMessage(TextFormat::RED . $this->translateString("error.create.notalphanum"));
            return true;
        }
        if(strlen($faction) < 3 || strlen($faction) > 15)
        {
            $sender->sendMessage(TextFormat::RED . $this->translateString("error.create.namelength"));
            return true;
        }
        
        if ($this->getPlugin()->getFaction($faction) !== false)
        {
            $sender->sendMessage(TextFormat::RED . $this->translateString("error.create.infaction"));
            return true;
        }
        
        if ($this->getPlugin()->factionExists($faction))
        {
            
            $sender->sendMessage(TextFormat::RED . $this->translateString("error.create.exists"));
            return true;
        }
        
        $player = $sender->getName();
        
        $this->getPlugin()->createFaction($player, $faction);
        $sender->sendMessage(TextFormat::LIGHT_GREEN . $this->translateString("create.success"));
        return true;
    }
}
