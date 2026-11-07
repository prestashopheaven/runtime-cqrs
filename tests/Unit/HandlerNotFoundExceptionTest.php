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

use PHPUnit\Framework\TestCase;
use PrestaShopHeaven\Runtime\Cqrs\HandlerNotFoundException;

/**
 * A missing handler is a programming error: a LogicException with the message the bus gives it.
 */
final class HandlerNotFoundExceptionTest extends TestCase
{
    public function testIsALogicExceptionWithItsMessage(): void
    {
        $exception = new HandlerNotFoundException('No handler registered for "Foo".');

        self::assertInstanceOf(\LogicException::class, $exception);
        self::assertSame('No handler registered for "Foo".', $exception->getMessage());
    }
}
