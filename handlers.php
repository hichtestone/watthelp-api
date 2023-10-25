<?php

declare(strict_types=1);

if (!function_exists('transInfo')) {
    function transInfo(string $key, array $params = []): \App\Model\TranslationInfo
    {
        return new \App\Model\TranslationInfo($key, $params);
    }
}