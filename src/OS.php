<?php

namespace phparsenal\fastforward;

class OS
{
    const LINUX = 1;
    const WINDOWS = 2;

    /**
     * Returns true when the constant matches the running operating system
     *
     * @param int $const
     *
     * @return bool
     */
    public static function isType($const)
    {
        return self::getType() === $const;
    }

    /**
     * Returns the constant of the running operating system
     *
     * @return int
     *
     * @throws \Exception
     */
    public static function getType()
    {
        $os = php_uname('s');
        if ($os === 'Linux') {
            return self::LINUX;
        } elseif (strpos($os, 'Windows') === 0) {
            return self::WINDOWS;
        } else {
            throw new \Exception($os . ' is currently not supported.');
        }
    }
}
