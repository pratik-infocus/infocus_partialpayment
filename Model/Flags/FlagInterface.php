<?php
/**
 * Infocus
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Infocus-solution.com license that is
 * available through the world-wide-web at this URL:
 * https://infocus-solution.com/license.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @author Infocus Solutions
 * @copyright Copyright (c) 2024 Infocus (https://infocus-solution.com)
 * @package Partial Payment module for Magento 2
 */

namespace Infocus\PartialPayments\Model\Flags;

/**
 * Interface FlagInterface
 * @package Infocus\PartialPayments\Model\Flags
 */
interface FlagInterface
{
    const FLAGS_KEY_FLAG = 'flag';
    const FLAGS_KEY_ACTION = 'action';

    /**
     * Set flag
     *
     * @param string $flag
     * @param mixed|true $value
     * @return void
     */
    public function set($flag, $value = true);

    /**
     * Get flag
     *
     * @param string $flag
     * @return bool|mixed
     */
    public function get($flag);

    /**
     * Remove flag
     *
     * @param string $flag
     * @return void
     */
    public function remove($flag);

    /**
     * Check flag
     *
     * @param string $flag
     * @return bool
     */
    public function hasFlag($flag);

    /**
     * Check active flag
     *
     * @param string $flag
     * @return bool
     */
    public function hasActiveFlag($flag);
}
