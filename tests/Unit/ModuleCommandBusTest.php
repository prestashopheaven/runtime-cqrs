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

namespace PrestaShopHeaven\Runtime\Cqrs\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use PrestaShopHeaven\Runtime\Cqrs\HandlerNotFoundException;
use PrestaShopHeaven\Runtime\Cqrs\ModuleCommandBus;
use Psr\Container\ContainerInterface;

/**
 * The module bus passes a message to the handler registered for its class (ARCH-08).
 */
final class ModuleCommandBusTest extends TestCase
{
    public function testPassesTheMessageToItsHandlerAndReturnsTheResult(): void
    {
        $message = new \ArrayObject(['id' => 7]);
        $handler = new class {
            /**
             * Returns the id of the message, to prove the bus called it.
             *
             * @param \ArrayObject<string, int> $message
             */
            public function handle(\ArrayObject $message): int
            {
                return (int) $message['id'];
            }
        };

        self::assertSame(7, (new ModuleCommandBus($this->locator([\ArrayObject::class => $handler])))->handle($message));
    }

    /**
     * Handlers the bus must refuse: none, something without handle(), a non-object.
     *
     * @return iterable<string, array{0: array<string, mixed>}>
     */
    public static function missingHandlers(): iterable
    {
        yield 'nothing registered' => [[]];
        yield 'no handle() method' => [[\stdClass::class => new \ArrayObject()]];
        yield 'not an object' => [[\stdClass::class => 'handler']];
    }

    /**
     * @param array<string, mixed> $handlers
     */
    #[DataProvider('missingHandlers')]
    public function testThrowsWhenNoUsableHandlerIsRegistered(array $handlers): void
    {
        $this->expectException(HandlerNotFoundException::class);
        $this->expectExceptionMessage('No handler registered for "stdClass".');

        (new ModuleCommandBus($this->locator($handlers)))->handle(new \stdClass());
    }

    public function testTheErrorNamesTheHandlerTagWhenTheBusKnowsIt(): void
    {
        $this->expectException(HandlerNotFoundException::class);
        $this->expectExceptionMessage('No handler registered for "stdClass" (tag prestashopheaven.heavenfoo.handler in config/common.yml).');

        (new ModuleCommandBus($this->locator([]), ModuleCommandBus::handlerTag('heavenfoo')))->handle(new \stdClass());
    }

    public function testTheHandlerExceptionReachesTheCaller(): void
    {
        $handler = new class {
            /**
             * Fails like a domain rule would.
             */
            public function handle(\stdClass $message): void
            {
                throw new \DomainException('Refused by the domain.');
            }
        };

        $this->expectException(\DomainException::class);
        (new ModuleCommandBus($this->locator([\stdClass::class => $handler])))->handle(new \stdClass());
    }

    public function testServiceIdAndHandlerTagFollowTheModuleName(): void
    {
        self::assertSame('prestashopheaven.heavenfoo.command_bus', ModuleCommandBus::serviceId('heavenfoo'));
        self::assertSame('prestashopheaven.heavenfoo.handler', ModuleCommandBus::handlerTag('heavenfoo'));
    }

    /**
     * A service locator with the given handlers, keyed by message class.
     *
     * @param array<string, mixed> $handlers
     */
    private function locator(array $handlers): ContainerInterface
    {
        $locator = $this->createMock(ContainerInterface::class);
        $locator->method('has')->willReturnCallback(static fn (string $id): bool => array_key_exists($id, $handlers));
        $locator->method('get')->willReturnCallback(static fn (string $id): mixed => $handlers[$id] ?? null);

        return $locator;
    }
}
