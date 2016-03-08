<?php namespace Jigsaw\Jigsaw\Handlers;

use Illuminate\Contracts\View\Factory;
use Jigsaw\Jigsaw\Jigsaw;
use Jigsaw\Jigsaw\ProcessedFile;

class CollectionIndexHandler extends BladeHandler
{
    /**
     * @var Jigsaw
     */
    protected $jigsaw;

    public function __construct(Factory $viewFactory, Jigsaw $jigsaw)
    {
        parent::__construct($viewFactory);
        $this->jigsaw = $jigsaw;
    }

    public function canHandle($file, $config)
    {
        return isset($config['collections'])
            && starts_with($file->getFileName(), $config['collections'])
            && parent::canHandle($file, $config);
    }

    public function handle($file, $data)
    {
        $filename = $file->getBasename('.blade.php') . '.html';
        return new ProcessedFile($filename, $file->getRelativePath(), $this->render($file, $data));
    }

    public function render($file, $data)
    {
        return $this->viewFactory->file($file->getRealPath(), $data)->render();
    }
}
