<?php


namespace Triun\ModelBase\Lib;


use App;
use File;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Console\OutputStyle;
use ReflectionClass;
use Triun\ModelBase\ModelBaseConfig;
use Triun\ModelBase\Definitions\Skeleton;
use Triun\ModelBase\Util;
use Triun\ModelBase\Utils\Diff;

abstract class BuilderUtilBase extends UtilBase
{
    const TAB = '    ';

    /**
     * @var \Illuminate\Console\Command
     */
    protected $command;

    /**
     * ModelBaseUtil constructor.
     *
     * @param \Triun\ModelBase\ModelBaseConfig $config
     * @param \Illuminate\Console\Command      $command
     */
    public function __construct(ModelBaseConfig $config, Command $command)
    {
        parent::__construct($config);

        $this->command = $command;
    }

    /**
     * Get stub file location.
     *
     * @param string $file
     *
     * @return string
     */
    public function getStub($file = 'class.stub')
    {
        return __DIR__ . '/../../resources/stubs/' . $file;
    }

    /**
     * Get the destination class path for the model.
     *
     * @param string $className
     *
     * @return string
     * @throws Exception
     */
    protected function getClassNamePath($className)
    {
        if (empty($className)) {
            throw new Exception('Class name is empty');
        }

        $name = str_replace(App::getNamespace(), '', $className);

        // TODO: Composer loader compatibility.
        return App::path().'/'.str_replace('\\', '/', $name).'.php';
    }

    /**
     * Get the full namespace name for a given class.
     *
     * @param  string  $name
     * @return string
     */
    protected function getNamespace($name)
    {
        return trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');
    }

    /**
     * @param string $path
     * @param string $name
     * @param string $content
     * @param bool   $override
     * @param \Triun\ModelBase\Definitions\Skeleton $skeleton
     *
     * @return bool|int The method returns the number of bytes that were written to the file, or false on failure.
     * @throws Exception
     */
    protected function save($path, $name, $content, $override = false, $skeleton)
    {
        $exists = File::exists($path);

        if ($exists) {
            // Verify changes
            $actual = File::get($path);
            if ($actual === $content) {
                $this->muted("{$name} identical");
                return 0;
            }

            if ($this->command->getOutput()->isVerbose()) {
                $this->command->line($path.' updates:');
                $this->compare($actual, $content);
                $this->command->getOutput()->newLine();
            }

            // Override permissions
            if ( !$this->safe($path, $override)) {
                $this->muted("{$name} cancelled");
                $this->verifyExtension($skeleton);
                return false;
            }
        }

        $this->makeDirectory($path);

        $size = File::put($path, $content);

        if (!($size > 0)) {
            throw new Exception("{$name} save error: Returned $size bytes");
        }

        $this->success($name.' '.($exists ? 'updated' : 'created'));

        return $size;
    }

    /**
     * @param string $path
     * @param bool|string $override
     *
     * @return bool
     */
    protected function safe($path, $override = Util::CONFIRM)
    {
        if ( !File::exists($path) ) {
            return true;
        }

        if ($override === Util::CONFIRM) {
            return $this->command->confirm("The file $path already exists. Do you want to override it?");
        }

        return $override;
    }

    /**
     * @param \Triun\ModelBase\Definitions\Skeleton $skeleton
     */
    protected function verifyExtension($skeleton)
    {
        $reflection = new ReflectionClass($skeleton->className);

        $extensionName = $reflection->getParentClass()->getName();

        if ($extensionName === $skeleton->extends) {
            $this->trace("{$skeleton->className} extends {$skeleton->extends}.");
        }
        else {
            $this->error("{$skeleton->className} DOES NOT extend {$skeleton->extends}, but $extensionName.");
        }
    }

    /**
     * @param $string1
     * @param $string2
     */
    protected function compare($string1, $string2)
    {
        if ($string1 === $string2) {
            return;
        }

        $diff = Diff::compare($string1, $string2);
        //$this->command->line(Diff::toString($diff));

        foreach ($diff as $line) {
            switch ($line[1]){
                case Diff::DELETED:
                    $this->command->line('<fg=red>- ' . $line[0].'</>');
                    break;
                case Diff::INSERTED:
                    $this->command->info('<fg=green>+ ' . $line[0].'</>');
                    break;
                case Diff::UNMODIFIED:
                default:
                    $this->command->line('  ' . $line[0], null, OutputStyle::VERBOSITY_VERY_VERBOSE);
            }
        }
    }

    /**
     * Build the directory for the class if necessary.
     *
     * @param  string  $path
     * @return string
     */
    protected function makeDirectory($path)
    {
        if (! File::isDirectory(dirname($path))) {
            File::makeDirectory(dirname($path), 0777, true, true);
        }
    }

    /**
     * @param \Triun\ModelBase\Definitions\Skeleton $skeleton
     *
     * @return array
     */
    protected function getPHPDoc(Skeleton $skeleton)
    {
        $stub = " * @dummy_tag dummy_type dummy_name dummy_description";

        // Order by tags and tabulate.
        $tags = [];
        foreach ($skeleton->phpDocTags() as $name => $item) {
            if (!isset($tags[$item->tag])) {
                $tags[$item->tag] = (object) [
                    'max_type_length' => 0,
                    'max_name_length' => 0,
                    'items' => [],
                ];
            }
            $tags[$item->tag]->max_type_length = max($tags[$item->tag]->max_type_length, strlen($item->type));
            $tags[$item->tag]->max_name_length = max($tags[$item->tag]->max_name_length, strlen($name));
            $tags[$item->tag]->items[$name] = $item;
        }

        $result = [];
        foreach ($tags as $tag => $info) {
            foreach ($info->items as $name => $item) {
                $replace = [
                    'dummy_tag'     => $tag,
                    'dummy_type'    => str_pad($item->type, $info->max_type_length),
                    'dummy_name'    => str_pad($name, $info->max_name_length),
                    'dummy_description' => $item->description,
                ];
                $result[] = str_replace(array_keys($replace), array_values($replace), $stub);
            }
        }
        return $result;
    }

    /**
     * Write a string as success output.
     *
     * @param  string  $string
     * @return void
     */
    public function success($string)
    {
        /** @var \Illuminate\Console\OutputStyle $output */
        $output = $this->command->getOutput();
        if ($output->isVerbose()) {
            $output->success($string);
        }
        else {
            $this->command->info($string);
        }
    }

    /**
     * Write a string as error output.
     *
     * @param  string  $string
     * @return void
     */
    public function error($string)
    {
        /** @var \Illuminate\Console\OutputStyle $output */
        $output = $this->command->getOutput();
        if ($output->isVerbose()) {
            $output->error($string);
        }
        else {
            $this->command->error($string);
        }
    }

    /**
     * Write a string as trace output.
     *
     * @param  string  $string
     * @return void
     */
    public function trace($string)
    {
        /** @var \Illuminate\Console\OutputStyle $output */
        $output = $this->command->getOutput();
        if ($output->isVerbose()) {
            $this->muted($string);
        }
    }

    /**
     * Write a string as muted output.
     *
     * @param  string  $string
     * @return void
     * @link https://symfony.com/doc/current/console/coloring.html
     */
    public function muted($string)
    {
        $this->command->getOutput()->writeln('<fg=white;options=bold>'.$string.'</>');
    }
}