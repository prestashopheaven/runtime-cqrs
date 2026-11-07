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
use PrestaShopHeaven\Runtime\Cqrs\ModuleCommandBus;
use PrestaShopHeaven\Runtime\Cqrs\ModuleServices;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

/**
 * ModuleServices (trait of the main class): getCommandBus() gives the bus when the container has it under
 * prestashopheaven.<module>.command_bus, and null (never an exception) when there is no container, the container does
 * not know the service, or something else is under the id, so hooks and controllers never break the page (HK-04).
 * The real container in a shop is covered by the fixture module's integration test.
 */
final class ModuleServicesTest extends TestCase
{
    protected function setUp(): void
    {
        \PrestaShopLogger::$logs = [];
    }

    public function testGetCommandBusReturnsTheBusUnderTheServiceIdOfTheModule(): void
    {
        $bus = new ModuleCommandBus($this->createMock(ContainerInterface::class));
        $asked = [];
        $host = $this->host(static function (string $id) use ($bus, &$asked): mixed {
            $asked[] = $id;

            return $bus;
        });

        self::assertSame($bus, $host->getCommandBus());
        self::assertSame(['prestashopheaven.heavenfoo.command_bus'], $asked);
    }

    /**
     * @return array<string, array{\Closure(string): mixed}>
     */
    public static function unusableContainers(): array
    {
        return [
            // PS 8: Module::get() returns false without a container.
            'no container (PS 8)' => [static fn (string $id): mixed => false],
            // PS 9: Module::get() returns null without a container.
            'no container (PS 9)' => [static fn (string $id): mixed => null],
            // Something else under the id.
            'wrong service' => [static fn (string $id): mixed => new \stdClass()],
        ];
    }

    /**
     * @param \Closure(string): mixed $get what Module::get() does
     */
    #[DataProvider('unusableContainers')]
    public function testGetCommandBusReturnsNullWhenTheBusIsNotAvailable(\Closure $get): void
    {
        self::assertNull($this->host($get)->getCommandBus());
        self::assertSame([], \PrestaShopLogger::$logs, 'only a container error is logged');
    }

    public function testAStaleContainerIsLoggedAndGivesNull(): void
    {
        $host = $this->host(static function (string $id): mixed {
            throw new class('Unknown service.') extends \RuntimeException implements ContainerExceptionInterface {
            };
        });

        self::assertNull($host->getCommandBus());
        self::assertCount(1, \PrestaShopLogger::$logs);
        self::assertSame('[heavenfoo] Service "prestashopheaven.heavenfoo.command_bus" is not available (clear the cache): Unknown service.', \PrestaShopLogger::$logs[0][0]);
        self::assertSame(2, \PrestaShopLogger::$logs[0][1]);
    }

    public function testFindModuleServiceChecksTheClass(): void
    {
        $date = new \DateTimeImmutable();
        $host = $this->host(static fn (string $id): mixed => $date);

        self::assertSame($date, $host->findModuleService('any.id', \DateTimeInterface::class));
        self::assertNull($host->findModuleService('any.id', \ArrayObject::class));
    }

    /**
     * A stand-in for the main class whose get() behaves like the given closure.
     *
     * @param \Closure(string): mixed $get
     */
    private function host(\Closure $get): object
    {
        return new class($get) {
            use ModuleServices;

            /** Technical name, used in the service id and the log line. */
            public string $name = 'heavenfoo';

            /**
             * Holds the behaviour of get().
             *
             * @param \Closure(string): mixed $get
             */
            public function __construct(private readonly \Closure $get)
            {
            }

            /**
             * Module::get() stand-in.
             */
            public function get(string $serviceId): mixed
            {
                return ($this->get)($serviceId);
            }
        };
    }
}
