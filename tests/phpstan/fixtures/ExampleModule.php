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

namespace PrestaShopHeaven\Runtime\Cqrs\Tests\PhpStan;

use PrestaShopHeaven\Runtime\Cqrs\ModuleServices;

/**
 * Example module that uses ModuleServices, so PHPStan analyses the trait in the context of a real Module subclass
 * (Module::get(), $name). Not used at runtime.
 */
final class ExampleModule extends \Module
{
    use ModuleServices;
}
