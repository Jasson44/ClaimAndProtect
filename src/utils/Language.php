<?php

declare(strict_types=1);

namespace xeonch\ClaimAndProtect\utils;

use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use shock95x\auctionhouse\AuctionHouse;
use UnexpectedValueException;
use xeonch\ClaimAndProtect\Main;

class Language
{

    public static array $translation;
    /** @var string[]  */
    private static array $supported = ["en_US"];

    public static function init(Main $plugin)
    {
        foreach (self::$supported as $locale) {
            $config = new Config($plugin->getDataFolder() . "language/{$locale}.yml", Config::YAML);
            Utils::checkConfig($plugin, $config, "lang-version", 4);
        }
        self::loadLanguages($plugin->getDataFolder());
        if (empty(self::$translation)) {
            throw new UnexpectedValueException("No language file has been found....");
        }
        if (!isset(self::$translation[strtolower(Configuration::getDefaultLang())])) {
            throw new UnexpectedValueException("Default language file could not be found, disabling plugin...");
        }
    }

    public static function loadLanguages(String $dataFolder): void
    {
        foreach (glob($dataFolder . "language/*.yml") as $file) {
            $config = new Config($file, Config::YAML);
            $localeCode = basename($file, ".yml");
            self::$translation[strtolower($localeCode)] = $config->getAll();

            array_walk_recursive(
                self::$translation[strtolower($localeCode)],
                fn(&$element) => $element = str_replace("&", "\xc2\xa7", (string) $element ??= "")
            );

            unset(self::$translation[strtolower($localeCode)]["lang-version"]);
        }
    }

    /**
     * @param Player $player
     * @param string $key
     * @param bool $prefix
     */
    public static function sendMessage(Player $player, string $key, bool $prefix = true): void
    {
        $player->sendMessage((string) self::get($player, $key, $prefix));
    }

    /**
     * @param Player $player
     * @param string $key
     * @param bool $prefix
     * @return string|string[]
     */
    public static function get(Player $player, string $key, bool $prefix = false)
    {
        $locale = Configuration::getDefaultLang();
        if (isset(self::$translation[strtolower($player->getLocale())])) {
            $locale = $player->getLocale();
        }
        if (!isset(self::$translation[strtolower($locale)][$key])) {
            Server::getInstance()->getLogger()->warning("Key '" . $key . "' could not be found in the '" . $locale . "' language file, add this key to the language file or update the file by deleting it and restarting the server.");
            return "";
        }
        return $prefix ? Utils::prefixMessage(self::$translation[strtolower($locale)][$key]) : self::$translation[strtolower($locale)][$key];
    }
}
