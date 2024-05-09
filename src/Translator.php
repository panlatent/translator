<?php

namespace panlatent\translator;

use panlatent\translator\formatters\GettextPo;
use Symfony\Component\Finder\Finder;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

class Translator extends Controller
{
    /**
     * @inheritdoc
     */
    public $defaultAction = 'extract';

    /**
     * @var string|null
     */
    public ?string $base = null;

    /**
     * @var string|null
     */
    public ?string $output = null;

    /**
     * @var string
     */
    public string $default = 'site';

    /**
     * @var array|string[]
     */
    public array $keywords = ['Yii::t', 'Craft::t'];

    /**
     * @var array|string[]
     */
    public array $ignore = ['yii', 'app'];

    /**
     * @inheritdoc
     */
    public function options($actionID): array
    {
        $options = parent::options($actionID);
        if ($actionID == 'extract') {
            $options = array_merge($options, ['base', 'output', 'default']);
        } elseif ($actionID == 'make') {
            $options = array_merge($options, ['output']);
        }
        return $options;
    }

    /**
     * Extract translations from PHP and Twig files
     * @param string ...$files
     * @return int
     */
    public function actionExtract(string ...$files): int
    {
        $extractor = new Extractor([
            'basePath' => $this->base,
            'keywords' => $this->keywords,
            'defaultCategory' => $this->default,
            'ignoreCategories' => $this->ignore,
        ]);
        $formatter = new GettextPo();

        foreach ($files as $file) {
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            if ($ext === 'twig' || $ext === 'html') {
                $extractor->fromTwigFile($file);
            } elseif ($ext === 'php') {
                $extractor->fromPhpFile($file);
            }
        }

        if (!empty($this->output)) {
            file_put_contents($this->output, $formatter->format($extractor->extract()));
        } else {
            echo $formatter->format($extractor->extract());
        }

        return ExitCode::OK;
    }

    /**
     * Make all translatable twig templates from basic HTML files.
     *
     * @param string $path
     * @param string $dist
     * @return int
     */
    public function actionMakeAll(string $path, string $dist): int
    {
        $src = Yii::getAlias($path);
        $dist = Yii::getAlias($dist);

        $fs = new Finder();
        foreach ($fs->in($src)->name(['*.html', '*.twig'])->files() as $file) {
            echo ' > ' . $file->getPathname() . PHP_EOL;
            $path = substr($file->getPathname(), strlen($src));
            $contents = $this->makeTemplateContent($file->getPathname());
            file_put_contents(rtrim($dist, '/') . '/' . ltrim($path, '/'), $contents);
        }

        return ExitCode::OK;
    }

    /**
     * Make a translatable twig template from basic HTML file.
     *
     * @param string $path Template path
     * @return int
     */
    public function actionMake(string $path): int
    {
        $contents = $this->makeTemplateContent($path);
        if ($this->output === null) {
            echo $contents;
        } else {
            file_put_contents($this->output, $contents);
        }

        return ExitCode::OK;
    }

    /**
     * @param string $path
     * @return string
     */
    private function makeTemplateContent(string $path): string
    {
        return (new TemplateTransform())->transform(file_get_contents($path));
    }
}