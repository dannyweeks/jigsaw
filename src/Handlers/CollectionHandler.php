<?php namespace Jigsaw\Jigsaw\Handlers;

use Jigsaw\Jigsaw\ProcessedFile;

class CollectionHandler extends MarkdownHandler
{
    protected $handlingCollection;

    public function canHandle($file, $config)
    {
        return isset($config['collections'])
            && starts_with($file->getRelativePath(), $config['collections'])
            && parent::canHandle($file, $config);
    }

    public function handle($file, $data)
    {
        $filename = $file->getBasename($this->getFileExtension($file)) . '.html';

        return new ProcessedFile($filename, 'blog', $this->render($file, $data));
    }

    public function render($file, $data)
    {
        $document = $this->parseFile($file);
        $this->handlingCollection = $this->getCollectionName($file, $data);
        $bladeContent = $this->compileToBlade($document);

        $documentMetaData = $document->getYAML() ?: [];

        $data = array_merge($data, $documentMetaData);

        return $this->temporaryFilesystem->put($bladeContent, function ($path) use ($data) {
            return $this->viewFactory->file($path, $data)->render();
        }, '.blade.php');
    }

    protected function getCollectionName($file, $config)
    {
        $path = $file->getRelativePath();

        if (!in_array($path, $config['collections'])) {
            /**
             * @todo Either make more attempts to guess the repo name or just throw a better exception.
             */
            throw new \Exception('Trying to create collection but cant find it.');
        }

        return $path;
    }

    protected function getCollectionView($collectionName)
    {
        return 'single-' . $collectionName;
    }

    protected function compileToBlade($document)
    {
        return collect([
            sprintf("@extends('%s.%s')", '_collections.', $this->getCollectionView($this->handlingCollection)),
            sprintf("@section('%s')", 'content'),
            $document->getContent(),
            '@endsection',
        ])->implode("\n");
    }

}
