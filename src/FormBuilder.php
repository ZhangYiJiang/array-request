<?php

namespace ZhangYiJiang\ArrayRequest;

use Collective\Html\FormBuilder as LaravelFormBuilder;

class FormBuilder extends LaravelFormBuilder
{
    /**
     * Keep track of how many times a model attribute has been accessed
     *
     * @var array
     */
    protected $modelAttributeAccess = [];

    protected function getModelValueAttribute($name)
    {
        if (str_contains($name, '[]')) {
            $name = $this->rewriteModelAttributeName($name);
        }

        return parent::getModelValueAttribute($name);
    }

    protected function rewriteModelAttributeName($name)
    {
        $count = $this->incrementModelAttributeAccess($name);
        return str_replace('[]', "[$count]", $name);
    }

    protected function incrementModelAttributeAccess($key)
    {
        if (!key_exists($key, $this->modelAttributeAccess)) {
            $this->modelAttributeAccess[$key] = 0;
        }

        return $this->modelAttributeAccess[$key]++;
    }
}