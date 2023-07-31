<?php

namespace library;

class Url
{
    /**
     * 是否为 http(s) 链接
     *
     * @param string $url
     * @return boolean
     */
    public static function isHttp(string $url)
    {
        if (strpos($url, 'http://') != -1) {
            return true;
        }

        if (strpos($url, 'https://') != -1) {
            return true;
        }

        return false;
    }
}
