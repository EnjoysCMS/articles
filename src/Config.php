<?php

declare(strict_types=1);

namespace EnjoysCMS\Articles;

use EnjoysCMS\Core\Modules\AbstractModuleConfig;

final class Config extends AbstractModuleConfig
{

    public function getModulePackageName(): string
    {
        return 'enjoyscms/articles';
    }

    public function getEditorConfig(string $namespace = null)
    {
        if ($namespace === null){
            return $this->get('editor');
        }
        return $this->get('editor')[$namespace] ?? null;
    }


}
