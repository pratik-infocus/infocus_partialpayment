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
 * Class Flags
 * @package Infocus\PartialPayments\Model\Flags
 */
class Flag implements FlagInterface
{
    /**
     * @var array
     */
    protected $flags;

    /**
     * @var array
     */
    protected $activeFlags;

    /**
     * Flag constructor.
     *
     * @param mixed[] $flags
     */
    public function __construct(
        array $flags = []
    ) {
        $this->parseFlags($flags);
    }

    /**
     * {@inheritdoc}
     */
    public function set($flag, $value = true)
    {
        $this->activeFlags[$flag] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function get($flag)
    {
        return $this->activeFlags[$flag] ?? false;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($flag)
    {
        if (isset($this->activeFlags[$flag])) {
            unset($this->activeFlags[$flag]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hasFlag($flag)
    {
        if (!isset($this->flags[$flag]) || empty($this->activeFlags)) {
            return false;
        }

        return !empty(array_intersect($this->flags[$flag], array_keys($this->activeFlags)));
    }

    /**
     * {@inheritdoc}
     */
    public function hasActiveFlag($flag)
    {
        return isset($this->activeFlags[$flag]);
    }

    /**
     * Initial parsing of flags
     *
     * @param mixed[] $flags
     * @return mixed[]
     */
    private function parseFlags(array $flags)
    {
        if (null === $this->flags) {
            foreach ($flags as $flag) {
                if (empty($flag[self::FLAGS_KEY_FLAG])
                    || empty($flag[self::FLAGS_KEY_ACTION])
                    || !is_string($flag[self::FLAGS_KEY_ACTION])
                ) {
                    continue;
                }

                $this->flags[$flag[self::FLAGS_KEY_FLAG]][] = $flag[self::FLAGS_KEY_ACTION];
            }
        }

        return $this->flags;
    }
}
