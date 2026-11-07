<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/MIT
 *
 * @author    prestahaven.com
 * @copyright Since 2026 George van Engers
 * @license   https://opensource.org/licenses/MIT MIT License
 */

/*
 * Unit tests of runtime-cqrs run without PrestaShop: the _PS_VERSION_ guard, and a PrestaShopLogger that records
 * what ModuleServices logs. Only defined when the real class is not loaded.
 */

if (!defined('_PS_VERSION_')) {
    define('_PS_VERSION_', '0.0.0-unit');
}

if (!class_exists('PrestaShopLogger', false)) {
    /**
     * Records addLog() calls for the assertions.
     */
    class PrestaShopLogger
    {
        /** @var list<array{0: string, 1: int}> */
        public static array $logs = [];

        /**
         * Records the message and severity.
         */
        public static function addLog(string $message, int $severity = 1): bool
        {
            self::$logs[] = [$message, $severity];

            return true;
        }
    }
}
