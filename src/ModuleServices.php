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

declare(strict_types=1);

namespace PrestaShopHeaven\Runtime\Cqrs;

use Psr\Container\ContainerExceptionInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Safe access to the module's container services from hooks, widgets and legacy controllers (HK-07; was a copy in each
 * module: src/Module/Helper/ModuleServices.php): `$this->getCommandBus()` in the main class. A missing or stale
 * container gives a log line and null, never a broken page (HK-04). Symfony code gets the services by constructor
 * injection instead.
 */
trait ModuleServices
{
    /**
     * The command bus of this module (service prestashopheaven.<module>.command_bus), or null when it is not available.
     */
    public function getCommandBus(): ?ModuleCommandBus
    {
        $bus = $this->findModuleService(ModuleCommandBus::serviceId((string) $this->name), ModuleCommandBus::class);

        return $bus instanceof ModuleCommandBus ? $bus : null;
    }

    /**
     * A service of the container when it is an instance of $class, else null; a container that does not know the id
     * (stale cache) is logged as a warning.
     *
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T|null
     */
    public function findModuleService(string $serviceId, string $class): ?object
    {
        try {
            $service = $this->get($serviceId);
        } catch (ContainerExceptionInterface $e) {
            \PrestaShopLogger::addLog(
                sprintf('[%s] Service "%s" is not available (clear the cache): %s', $this->name, $serviceId, $e->getMessage()),
                2
            );

            return null;
        }

        return $service instanceof $class ? $service : null;
    }
}
