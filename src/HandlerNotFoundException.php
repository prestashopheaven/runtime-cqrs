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

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Thrown by ModuleCommandBus when no handler is tagged for a command or query class.
 */
final class HandlerNotFoundException extends \LogicException
{
}
