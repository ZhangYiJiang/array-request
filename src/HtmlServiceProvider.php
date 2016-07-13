<?php

namespace ZhangYiJiang\ArrayRequest;

use Collective\Html\HtmlServiceProvider as CollectiveHtmlServiceProvider;

class HtmlServiceProvider extends CollectiveHtmlServiceProvider
{
    protected function registerFormBuilder()
    {
        $this->app->singleton('form', function ($app) {
            $form = new FormBuilder($app['html'], $app['url'], $app['view'], $app['session.store']->getToken());

            return $form->setSessionStore($app['session.store']);
        });
    }

    public function provides()
    {
        return array_merge(parent::provides(), [FormBuilder::class]);
    }
}