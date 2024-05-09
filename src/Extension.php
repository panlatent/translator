<?php

namespace panlatent\translator;

use yii\base\BootstrapInterface;
use yii\console\Application as ConsoleApp;

class Extension implements BootstrapInterface
{
    public function bootstrap($app)
    {
        if ($app instanceof ConsoleApp) {
            $app->controllerMap['translator'] = Translator::class;
        }
    }
}
