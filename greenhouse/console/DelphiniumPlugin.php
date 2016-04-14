<?php namespace Delphinium\Greenhouse\Console;

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
    protected $name = 'delphinium:plugin';

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

        $this->info(sprintf('Successfully generated Plugin named "%s"', $this->argument('name')));
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
