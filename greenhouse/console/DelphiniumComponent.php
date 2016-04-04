<?php namespace Delphinium\Greenhouse\Console;
//namespace System\Console;

use Illuminate\Console\Command;
use System\Classes\UpdateManager;
use System\Classes\PluginManager;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Delphinium\Greenhouse\Templates\Component;

class DelphiniumComponent extends Command
{

    protected $fileMap = [];
    /**
     * The console command name.
     * @var string
     */
    protected $name = 'delphinium:component';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Creates a delphinium-esque component';

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
        $pluginCode = $this->argument('pluginCode');

        $parts = explode('.', $pluginCode);
        $pluginName = array_pop($parts);
        $authorName = array_pop($parts);

        $destinationPath = base_path() . '/plugins/' . strtolower($authorName) . '/' . strtolower($pluginName);
        $componentName = $this->argument('componentName');

        $vars = [
            'name' => $componentName,
            'author' => $authorName,
            'plugin' => $pluginName
        ];

        Component::make($destinationPath, $vars, $this->option('force'));

        $this->info(sprintf('Successfully generated Component for "%s"', $componentName));
    }

    /**
     * Get the console command arguments.
     */
    protected function getArguments()
    {
        return [
            ['pluginCode', InputArgument::REQUIRED, 'The name of the plugin to create. Eg: RainLab.Blog'],
            ['componentName', InputArgument::REQUIRED, 'The name of the component. Eg: Posts'],
        ];
    }

    /**
     * Get the console command options.
     */
    protected function getOptions()
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Overwrite existing files with generated ones.']
        ];
    }
}
