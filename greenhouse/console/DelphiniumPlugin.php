<?php namespace Delphinium\Greenhouse\Console;
//namespace System\Console;

use Illuminate\Console\Command;
use System\Classes\UpdateManager;
use System\Classes\PluginManager;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class DelphiniumPlugin extends Command
{

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
//        $this->output->writeln('Hello world!');
//        $this->info('Display this on the screen');
//        $this->output->writeln(sprintf('<info>Unpacking plugin: %s</info>', $code));
        $name = explode(".", $this->argument('name'));
//        $this->output->writeln(sprintf('<info>Argument given: %s</info>', $name[0]));
//        $this->output->writeln(sprintf('<info>Argument given: %s</info>', $name[1]));
        //to call another command from this command:
//        $this->call("create:plugin");
        $this->call("create:plugin", ['pluginCode' => $this->argument('name')]);//here we create a new plugin.

        //now let's try to create our own stub of what a Delphinium plugin should look like:
        protected $fileMap = [
        'plugin/plugin.stub'  => '{{lower_author}}/{{lower_name}}/Plugin.php',
        'plugin/version.stub' => '{{lower_author}}/{{lower_name}}/updates/version.yaml',
    ];

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
}
