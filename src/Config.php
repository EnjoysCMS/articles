<?php

declare(strict_types=1);

namespace EnjoysCMS\Articles;

use DI\DependencyException;
use DI\FactoryInterface;
use DI\NotFoundException;
use EnjoysCMS\Core\Components\Modules\ModuleConfig;

final class Config
{
    private ModuleConfig $moduleConfig;

    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function __construct(FactoryInterface $factory)
    {
        $this->moduleConfig = $factory->make(ModuleConfig::class, ['moduleName' => 'enjoyscms/articles']);
    }


    public function getModuleConfig(): ModuleConfig
    {
        return $this->moduleConfig;
    }

    public function getEditorConfig(string $namespace = null)
    {
        if ($namespace === null){
            return $this->moduleConfig->get('editor');
        }
        return $this->moduleConfig->get('editor')[$namespace] ?? null;
    }


}
