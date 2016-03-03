<?php namespace Jigsaw\Jigsaw\Console;

use Jigsaw\Jigsaw\Jigsaw;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BuildCommand extends Command
{
    private $sourcePath;
    private $buildPath;
    private $jigsaw;

    public function __construct($jigsaw, $sourcePath, $buildPath)
    {
        $this->sourcePath = $sourcePath;
        $this->buildPath = $buildPath;
        $this->jigsaw = $jigsaw;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('build')
            ->setDescription('Build your site.')
            ->addOption('env', null, InputOption::VALUE_REQUIRED, "What environment should we use to build?", 'local')
            ->addOption('pretty', null, InputOption::VALUE_REQUIRED, "Should the site use pretty URLs?", 'true');
    }

    protected function fire()
    {
        $this->buildPath .= '_' . $this->input->getOption('env');

        if ($this->input->getOption('pretty') === 'false') {
            $this->jigsaw->setOption('pretty', false);
        }

        $env = $this->input->getOption('env');

        $this->jigsaw->build($this->sourcePath, $this->buildPath, $env);
        $this->info('Site built successfully!');
    }
}
