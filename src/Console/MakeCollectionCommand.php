<?php namespace Jigsaw\Jigsaw\Console;

use Jigsaw\Jigsaw\Filesystem;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MakeCollectionCommand extends Command
{
    private $files;
    private $base;

    public function __construct(Filesystem $files)
    {
        $this->files = $files;
        $this->base = getcwd();
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('make:collection')
            ->setDescription('Generate boilerplate for a new collection.')
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'What is the name of the collection?'
            );
    }

    protected function fire()
    {
        $name = $this->input->getArgument('name');

        $config = include $this->base . '/config.php';
        $config['collections'][] = $name;
        $config['collections'] = array_unique($config['collections']);
        $this->updateConfigFile($config);
        $this->info('Added ' . $name . ' to the collection array in your config.php');

        // create _collections folder
        $collectionsDirectory = '/source/_collections/';
        $this->makeDirectory($collectionsDirectory);

        // create $name-single.blade.php
        $singleTemplateLocation = $collectionsDirectory . 'single-' . $name . '.blade.php';
        $this->makeFile($singleTemplateLocation, $this->singleTemplate($name));

        // create $name collection folder
        $collectionItemDirectory = '/source/' . $name . '/';
        $this->makeDirectory($collectionItemDirectory);

        // create example collection item
        $exampleLocation = $collectionItemDirectory . 'example-' . str_singular($name) . '.md';
        $this->makeFile($exampleLocation, $this->collectionItemExample($name));

        // create collection index page
        $collectionIndex = '/source/' . $name . '.blade.php';
        $this->makeFile($collectionIndex, $name . ' collection index page.');
    }

    private function updateConfigFile($config)
    {
        /**
         * @todo there might be a package that does a better job of this!
         */
        $output = json_decode(str_replace(array('(', ')'), array('&#40', '&#41'), json_encode($config)), true);
        $output = var_export($output, true);
        $output = str_replace(array('array (', ')', '&#40', '&#41'), array('[', ']', '(', ')'), $output);
        $output = preg_replace("/\d => /", "", $output);

        $configFile = <<<PHP
<?php

return $output;
PHP;
        $this->files->put($this->base . '/config.php', $configFile);
    }

    private function singleTemplate($name)
    {
        $single = str_singular($name);

        return <<<PHP
<h1>{{ \$title or 'This $single doesn\'t have a title!' }}</h1>

@yield('content')
PHP;

    }

    private function collectionItemExample($name)
    {
        return <<<MD
---
title: This is an optional title!
---
Well there you have it. This is the first of many items in your $name collection.

Meta data like the title above will be injected into single-$name.blade.php.
MD;

    }

    private function makeDirectory($path)
    {
        if (!$this->files->exists($this->base . $path)) {
            $this->files->makeDirectory($this->base . $path);
        }
    }

    private function makeFile($filePath, $content)
    {
        if (!$this->files->exists($this->base . $filePath)) {
            $this->files->put($this->base . $filePath, $content);
        }
        $this->info('Created: ' . ltrim($filePath, '/'));
    }
}
