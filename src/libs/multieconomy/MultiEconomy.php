<?php

declare(strict_types=1);

namespace xeonch\ClaimAndProtect\libs\multieconomy;

use xeonch\ClaimAndProtect\libs\multieconomy\exceptions\MissingProviderDependencyException;
use xeonch\ClaimAndProtect\libs\multieconomy\exceptions\UnknownProviderException;
use xeonch\ClaimAndProtect\libs\multieconomy\providers\BedrockEconomyProvider;
use xeonch\ClaimAndProtect\libs\multieconomy\providers\EconomyProvider;
use xeonch\ClaimAndProtect\libs\multieconomy\providers\EconomySProvider;
use xeonch\ClaimAndProtect\libs\multieconomy\providers\XPProvider;

class MultiEconomy
{
    public static bool $hasInitiated = false;

    /**
     * @var string[] $economyProviders
     * @phpstan-var array<class-string<EconomyProvider>>
     */
    public static array $economyProviders;

    public static function init(): void
    {
        if (!self::$hasInitiated) {
            self::$hasInitiated = true;

            self::registerProvider(["economys", "economyapi"], EconomySProvider::class);
            self::registerProvider(["bedrockeconomy"], BedrockEconomyProvider::class);
            self::registerProvider(["xp", "exp", "experience"], XPProvider::class);
        }
    }

    /**
     * @phpstan-param class-string<EconomyProvider> $economyProvider
     */
    public static function registerProvider(array $providerNames, string $economyProvider): void
    {
        foreach ($providerNames as $providerName) {
            if (isset(self::$economyProviders[strtolower($providerName)])) continue;
            self::$economyProviders[strtolower($providerName)] = $economyProvider;
        }
    }

    /**
     * @throws UnknownProviderException
     * @throws MissingProviderDependencyException
     */
    public static function getProvider(array $providerInformation): EconomyProvider
    {
        if (!isset(self::$economyProviders[strtolower($providerInformation["provider"])])) {
            throw new UnknownProviderException("Provider " . $providerInformation["provider"] . " not found.");
        }
        $provider = self::$economyProviders[strtolower($providerInformation["provider"])];
        if (!$provider::checkDependencies()) {
            throw new MissingProviderDependencyException("Dependencies for provider " . $providerInformation["provider"] . " not found.");
        }
        return new $provider($providerInformation);
    }
}