<?php

/*
 *  PLUGIN BY:
 *   _    __                  _                                     _
 *  | |  / /                 | |                                   | |
 *  | | / /                  | |                                   | |
 *  | |/ / _   _  ____   ____| | ______ ____   _____ ______   ____ | | __
 *  | |\ \| | | |/ __ \ / __ \ |/ /  __/ __ \ / __  | _  _ \ / __ \| |/ /
 *  | | \ \ \_| | <__> |  ___/   <| / | <__> | <__| | |\ |\ | <__> |   <
 *  |_|  \_\__  |\___  |\____|_|\_\_|  \____^_\___  |_||_||_|\____^_\|\_\
 *            | |    | |                          | |
 *         ___/ | ___/ |                          | |
 *        |____/ |____/                           |_|
 *
 * Change your PocketMine-MP internal server error kick message!
 * Copyright (C) 2020 Kygekraqmak
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 */

declare(strict_types=1);

namespace Kygekraqmak\CustomISE;

use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\protocol\DisconnectPacket;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

class CustomISE extends PluginBase implements Listener {

    /** @var array */
    private $config;

    public function onEnable() {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->saveDefaultConfig();
        $this->config = $this->getConfig()->getAll();
        $this->checkConfigVersion();
    }

    public function onDataPacketSend(DataPacketSendEvent $event) {
        $pk = $event->getPacket();
        // Returns void if $pk is not an instance of DisconnectPacket
        if (!$pk instanceof DisconnectPacket) return;
        if ($this->configIsMissing()) return;
        if ($pk->message === "Internal server error" and $this->config["ise-message"] != null) {
            $pk->message = $this->replace($event->getPlayer(), (string) $this->config["ise-message"]);
        }
        // TODO: Change the internal server error message in log
    }

    private function checkConfigVersion() : void {
        if ($this->config["config-version"] !== "1.0") {
            $this->getLogger()->notice("Your configuration file is outdated, updating the config.yml...");
            $this->getLogger()->notice("The old configuration file can be found at config_old.yml");
            rename($this->getDataFolder()."config.yml", $this->getDataFolder()."config_old.yml");
            $this->saveDefaultConfig();
        }
    }

    public function configIsMissing() : bool {
        if (!file_exists($this->getDataFolder()."config.yml")) {
            $this->getLogger()->notice("Configuration file is missing, please restart the server!");
            return true;
        }
        return false;
    }

    public function replace(Player $player, string $location) : string {
       $from = ["{player}", "&"];
       $to = [$player->getName(), "§"];
       return str_replace($from, $to, $location);
   }

}
