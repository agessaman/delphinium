<?php namespace Delphinium\Greenhouse\Console;
//namespace System\Console;

use Illuminate\Console\Command;
use System\Classes\UpdateManager;
use System\Classes\PluginManager;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Delphinium\Greenhouse\Templates\Plugin;

class DelphiniumPlugin extends Command
{

    protected $fileMap = [];
    /**
     * The console command name.
     * @var string
     */
    protected $name = 'delphinium:init';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Initializes a delphinium-esque plugin';

    /**
     * Create a new command instance.
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     * @return void
     */
    public function fire()
    {
        $name = explode(".", $this->argument('name'));
//        $this->output->writeln(sprintf('<info>Argument given: %s</info>', $name[0]));
//        $this->output->writeln(sprintf('<info>Argument given: %s</info>', $name[1]));
        //to call another command from this command:
//        $this->call("create:plugin");
//        $this->call("create:plugin", ['pluginCode' => $this->argument('name')]);//here we create a new plugin.
        $parts = explode('.', $this->argument('name'));

        if (count($parts) != 2) {
            $this->error('Invalid plugin name, either too many dots or not enough.');
            $this->error('Example name: AuthorName.PluginName');
            return;
        }


        $pluginName = array_pop($parts);
        $authorName = array_pop($parts);
        $vars = [
            'name'   => $pluginName,
            'author' => $authorName,
        ];
        $destinationPath = base_path() . '/plugins';
        Plugin::make($destinationPath, $vars);

        //now let's try to create our own stub of what a Delphinium plugin should look like:


        //PluginManager $this->loadPlugin($namespace, $path);
        //UpdateManager->updatePlugin($name)
//        $pluginName = $this->argument('name');
//        $pluginName = PluginManager::instance()->normalizeIdentifier($pluginName);
//        if (!PluginManager::instance()->exists($pluginName)) {
//            throw new \InvalidArgumentException(sprintf('Plugin "%s" not found.', $pluginName));
//        }
//
//        $manager = UpdateManager::instance()->resetNotes();
//
//        $manager->rollbackPlugin($pluginName);
//        foreach ($manager->getNotes() as $note) {
//            $this->output->writeln($note);
//        }
//
//        $manager->resetNotes();
//        $this->output->writeln('<info>Reinstalling plugin...</info>');
//        $manager->updatePlugin($pluginName);
//
//        foreach ($manager->getNotes() as $note) {
//            $this->output->writeln($note);
//        }
    }

    /**
     * Get the console command arguments.
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the plugin. E.g., Author.Plugin'],
        ];
    }

    /**
     * Get the console command options.
     * @return array
     */
    protected function getOptions()
    {
        return [];
    }

    private function delphiniumMakeAll()
    {
        $stubs = array_keys($this->fileMap);
        foreach ($stubs as $stub) {
            $this->makeStub($stub);
        }
    }

    private function makeStub($stubName)
    {
        if (!isset($this->fileMap[$stubName]))
            return;

        $sourceFile = __DIR__ . '/Templates/' . $stubName;
        $destinationFile = $this->targetPath . '/' . $this->fileMap[$stubName];
        $destinationContent = $this->files->get($sourceFile);

        /*
         * Parse each variable in to the desintation content and path
         */
        foreach ($this->vars as $key => $var) {
            $destinationContent = str_replace('{{'.$key.'}}', $var, $destinationContent);
            $destinationFile = str_replace('{{'.$key.'}}', $var, $destinationFile);
        }

        /*
         * Destination directory must exist
         */
        $destinationDirectory = dirname($destinationFile);
        if (!$this->files->exists($destinationDirectory))
            $this->files->makeDirectory($destinationDirectory, 0777, true); // @todo 777 not supported everywhere

        /*
         * Make sure this file does not already exist
         */
        if ($this->files->exists($destinationFile) && !$this->overwriteFiles)
            throw new \Exception('Stop everything!!! This file already exists: ' . $destinationFile);

        $this->files->put($destinationFile, $destinationContent);
    }
}
