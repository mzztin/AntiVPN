<?php

namespace Martin\AntiVPN;

use Martin\AntiVPN\task\CheckAsyncTask;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class AntiVPN extends PluginBase implements Listener {
    /** @var AntiVPN */
    private static $instance;
    /** @var Config */
    private $cfg;

    public function onEnable()
    {
        self::$instance = $this;

        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        @mkdir($this->getDataFolder());
        if (!file_exists($this->getDataFolder() . "config.yml")) {
            $this->saveResource("config.yml");
        }

        $this->cfg = new Config($this->getDataFolder() . "config.yml");

        $this->getLogger()->notice("AntiVPN registered!");
    }

    /** @return Config */
    public function getCfg(): Config {
        return $this->cfg;
    }

    /** @return AntiVPN */
    public static function getInstance(): self {
        return self::$instance;
    }

    public function onJoin(PlayerJoinEvent $event): void {
        if (!$event->getPlayer()->hasPermission("antivpn.bypass")) {
            $this->getServer()->getAsyncPool()->submitTask(new CheckAsyncTask($event->getPlayer()->getName(), $event->getPlayer()->getAddress()));
        }
    }
}