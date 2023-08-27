<?php

declare(strict_types=1);

namespace EnjoysCMS\Articles;

final class Assets
{
    public static function install(): void
    {
        passthru(sprintf('cd %s && yarn install', realpath(__DIR__.'/..')));
    }

}