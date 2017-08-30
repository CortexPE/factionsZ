<?php
namespace EschieEsh\factionsZ;

use pocketmine\utils\TextFormat;
use EschieEsh\factionsZ\factionsZ;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use EschieEsh\factionsZ\subcommand\SubCommand;
use EschieEsh\factionsZ\subcommand\CreateSubCommand;

class Commands extends PluginCommand
{
	/** @var SubCommand[] */
	private $subCommands = [];
	/** @var SubCommand[] */
	private $aliasSubCommands = [];
	/** @var factionsZ */
	private $plugin;
    
	public function __construct(factionsZ $plugin)
    {
		$this->plugin = $plugin;
		parent::__construct($plugin->getLanguage()->get("command.name"), $plugin);
		$this->setPermission("factionsZ.command");
		$this->setAliases([$plugin->getLanguage()->get("command.alias")]);
		$this->setDescription($plugin->getLanguage()->get("command.desc"));
		$this->setUsage($plugin->getLanguage()->get("command.usage"));
		$this->loadSubCommand(new CreateSubCommand($plugin, "create", $this));
        /// register commands
	}
	/**
	 * @return SubCommand[]
	 */
	public function getCommands(): array
    {
		return $this->subCommands;
	}
	/**
	 * @param SubCommand $command
	 */
	private function loadSubCommand(SubCommand $command)
    {
		$this->subCommands[$command->getName()] = $command;
		if ($command->getAlias() != "")
        {
			$this->aliasSubCommands[$command->getAlias()] = $command;
		}
	}
	/**
	 * @param CommandSender $sender
	 * @param string $alias
	 * @param string[] $args
	 * @return bool
	 */
	public function execute(CommandSender $sender, string $alias, array $args)
	{
		if (!isset($args[0]))
        {
			return false;
		}
		$subCommand = strtolower(array_shift($args));
		if (isset($this->subCommands[$subCommand]))
        {
			$command = $this->subCommands[$subCommand];
		} 
        elseif (isset($this->aliasSubCommands[$subCommand]))
        {
			$command = $this->aliasSubCommands[$subCommand];
		} 
        else
        {
			$sender->sendMessage(TextFormat::RED . $this->plugin->getLanguage()->get("command.unknown"));
			return true;
		}
        
		if ($command->canUse($sender))
        {
			if (!$command->execute($sender, $args))
            {
				$usage = $this->plugin->getLanguage()->translateString("subcommand.usage", [$command->getUsage()]);
				$sender->sendMessage($usage);
			}
		} 
        else
        {
			$sender->sendMessage(TextFormat::RED . $this->plugin->getLanguage()->get("command.unknown"));
		}
		return true;
	}
}
