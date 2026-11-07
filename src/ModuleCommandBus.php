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

use Psr\Container\ContainerInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * The command bus of a module (ARCH-08; was a copy in each module: src/Infrastructure/Bus/ModuleCommandBus.php).
 * Each module registers it in its config/common.yml under its own service id, never under the class name (the class
 * is shared by every module that uses this package):
 *
 *     prestashopheaven.<module>.command_bus:
 *       class: PrestaShopHeaven\Runtime\Cqrs\ModuleCommandBus
 *       public: true
 *       arguments:
 *         $handlers: !tagged_locator { tag: 'prestashopheaven.<module>.handler', index_by: 'command' }
 *         $handlerTag: 'prestashopheaven.<module>.handler'
 *
 * Other services get it with '@prestashopheaven.<module>.command_bus'; hooks and legacy code with getCommandBus()
 * (trait ModuleServices). The release zip also prefixes the class per module (scoping).
 */
final class ModuleCommandBus
{
    /**
     * Receives the handlers as a service locator, keyed by command or query class (!tagged_locator).
     *
     * @param ContainerInterface $handlers the handlers of this module
     * @param string $handlerTag the tag of the handlers, for the error message
     */
    public function __construct(private readonly ContainerInterface $handlers, private readonly string $handlerTag = '')
    {
    }

    /**
     * The public service id of the bus of a module: prestashopheaven.<module>.command_bus.
     */
    public static function serviceId(string $moduleName): string
    {
        return 'prestashopheaven.' . $moduleName . '.command_bus';
    }

    /**
     * The tag of the handlers of a module: prestashopheaven.<module>.handler.
     */
    public static function handlerTag(string $moduleName): string
    {
        return 'prestashopheaven.' . $moduleName . '.handler';
    }

    /**
     * Passes a command or query to its handler and returns what the handler returns: nothing or a small result for a
     * command, the result (read model) for a query.
     *
     * @throws HandlerNotFoundException when no handler is registered for the class
     * @throws \Exception what the handler throws: a domain exception (<Context>Exception, ARCH-12) or a PrestaShop
     *                    error; declared so callers can catch a specific domain exception
     */
    public function handle(object $message): mixed
    {
        $handler = $this->handlers->has($message::class) ? $this->handlers->get($message::class) : null;
        if (!is_object($handler) || !method_exists($handler, 'handle')) {
            throw new HandlerNotFoundException(sprintf('No handler registered for "%s"%s.', $message::class, $this->handlerTag !== '' ? " (tag {$this->handlerTag} in config/common.yml)" : ''));
        }

        return $handler->handle($message);
    }
}
