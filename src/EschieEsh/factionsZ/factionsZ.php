<?php

use pocketmine\plugin\PluginBase;
use pocketmine\Command\Command;
use pocketmine\Command\CommandSender;

class factionsZ extends PluginBase
{
    /** @var DataProvider $dataProvider */
    private $dataProvider = null;
    
    /** @var BaseLang $baseLang */
    private $baseLang = null;
    
    /**
    * @api
    * @return BaseLang
    */
    public function getLanguage(): BaseLang
    {
        return $this->baseLang;
    }
    
    public function createFaction($player, $faction)
    {
        $this->dataProvider->createFaction($player, $faction);
    }
    /**
    * @api
    * @return DataProvider
    */
    public function getProvider(): DataProvider
    {
        return $this->dataProvider;
    }
    
    public function onEnable()
    {
        @mkdir($this->getDataFolder());
        $this->getLogger()->notice("Loading...");
        $this->saveDefaultConfig();
        $this->reloadConfig();
		
        $lang = $this->getConfig()->get("language", BaseLang::FALLBACK_LANGUAGE);
        $this->baseLang = new BaseLang($lang, $this->getFile() . "resources/");

        switch (strtolower($this->getConfig()->get("DataProvider")))
        {
            case "sqlite":
            case "sqlite3":  
            default:
                $this->dataProvider = new SQLiteDataProvider($this); /// yaml, others
                break;
        }
		
		
        //$this->getServer()->getPluginManager()->registerEvents($eventListener, $this); // For listener
		
        $this->getServer()->getCommandMap()->register(Commands::class, new Commands($this));
        $this->getLogger()->notice(TF::GREEN . "Enabled!");
    }
    
    public function onDisable()
    {
        if ($this->dataProvider !== null)
        {
            $this->dataProvider->close();
        }
    }
}
