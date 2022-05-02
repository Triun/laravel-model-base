<?php

namespace Triun\ModelBase\Lib;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Console\OutputStyle;
use ReflectionClass;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;
use Triun\Diff\Diff;
use Triun\ModelBase\Definitions\Skeleton;
use Triun\ModelBase\ModelBaseConfig;
use Triun\ModelBase\Util;

abstract class BuilderUtilBase extends UtilBase
{
    const TAB = '    ';

    protected Command $command;

    public function __construct(ModelBaseConfig $config, Command $command)
    {
        parent::__construct($config);

        $this->command = $command;
    }

    public function getStub(string $file = 'class.stub'): string
    {
        return __DIR__ . '/../../resources/stubs/' . $file;
    }

    /**
     * Get the destination class path for the model.
     *
     * @throws Exception
     */
    protected function getClassNamePath(string $className): string
    {
        if (empty($className)) {
            throw new Exception('Class name is empty');
        }

        $name = str_replace(app()->getNamespace(), '', $className);

        // TODO: Composer loader compatibility.
        return app()->path() . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $name) . '.php';
    }

    /**
     * @return bool|int The method returns the number of bytes that were written to the file, or false on failure.
     * @throws Exception
     */
    protected function save(string $path, string $name, string $content, bool $override, Skeleton $skeleton): bool|int
    {
        $exists = file_exists($path);

        if ($exists) {
            // Verify changes
            $actual = file_get_contents($path);
            if ($actual === $content) {
                $this->muted("{$name} identical");

                return Diff::UNMODIFIED;
            }

            $this->command->line($path . ' updates:', null, OutputInterface::VERBOSITY_VERBOSE);
            $compare = $this->compare($actual, $content);
            if ($this->command->getOutput()->isVerbose()) {
                $this->command->getOutput()->newLine();
            }

            // Override permissions
            if (!$this->safe($path, $override)) {
                if ($compare & Diff::INSERTED) {
                    $this->warning("{$name} cancelled. There are pending updates to be implemented...");
                } else {
                    $this->info("{$name} difiers, but not update required");
                }
                $this->verifyExtension($skeleton);

                return false;
            }
        }

        $this->makeDirectory($path);

        $size = file_put_contents($path, $content);

        if (!($size > 0)) {
            throw new Exception("{$name} save error: Returned $size bytes");
        }

        $this->success($name . ' ' . ($exists ? 'updated' : 'created'));

        return $size;
    }

    protected function safe(string $path, bool|string $override = Util::CONFIRM): bool
    {
        if (!file_exists($path)) {
            return true;
        }

        if ($override === Util::CONFIRM) {
            return $this->command->confirm("The file $path already exists. Do you want to override it?");
        }

        if (!is_bool($override)) {
            throw new RuntimeException('Only boolean or the string confirm are allowed as override options');
        }

        return $override;
    }

    protected function verifyExtension(Skeleton $skeleton): void
    {
        $reflection = new ReflectionClass($skeleton->className);

        $extensionName = $reflection->getParentClass()->getName();

        if ($extensionName === $skeleton->extends) {
            $this->trace("{$skeleton->className} extends {$skeleton->extends}.");
        } else {
            $this->error("{$skeleton->className} DOES NOT extend {$skeleton->extends}, but $extensionName.");
        }
    }

    protected function compare(string $string1, string $string2): int
    {
        $result = Diff::UNMODIFIED;

        if ($string1 === $string2) {
            return $result;
        }

        $diff = Diff::compare($string1, $string2);
        //$this->command->line(Diff::toString($diff));

        foreach ($diff as $line) {
            switch ($line[1]) {
                case Diff::INSERTED:
                    $this->command->info('<fg=green>+ ' . $line[0] . '</>', OutputStyle::VERBOSITY_VERBOSE);
                    $result |= Diff::INSERTED;
                    break;
                case Diff::DELETED:
                    $this->command->line('<fg=red>- ' . $line[0] . '</>', null, OutputStyle::VERBOSITY_VERY_VERBOSE);
                    $result |= Diff::DELETED;
                    break;
                case Diff::UNMODIFIED:
                default:
                    $this->command->line('  ' . $line[0], null, OutputStyle::VERBOSITY_DEBUG);
            }
        }

        return $result;
    }

    /**
     * Build the directory for the class if necessary.
     */
    protected function makeDirectory(string $path): void
    {
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }
    }

    protected function getPHPDoc(Skeleton $skeleton): array
    {
        $stub = " * @dummy_tag dummy_type dummy_name dummy_description";

        // Order by tags and tabulate.
        $tags = [];
        foreach ($skeleton->phpDocTags() as $name => $item) {
            if (!isset($tags[$item->tag])) {
                $tags[$item->tag] = (object)[
                    'max_type_length' => 0,
                    'max_name_length' => 0,
                    'items'           => [],
                ];
            }
            $tags[$item->tag]->max_type_length = max($tags[$item->tag]->max_type_length, strlen($item->type));
            $tags[$item->tag]->max_name_length = max($tags[$item->tag]->max_name_length, strlen($name));
            $tags[$item->tag]->items[$name]    = $item;
        }

        // Order by key
        ksort($tags);

        // Order by a defined tag order
        $push = ['method', 'property-write', 'property-read', 'property'];
        uksort($tags, function ($a, $b) use ($push) {
            $aIndex = array_search($a, $push);

            // if is in push, move to the bottom
            if ($aIndex !== false) {
                $bIndex = array_search($b, $push);

                return $bIndex === false || $aIndex < $bIndex ? +1 : -1;
            }

            // Don't do anything
            return 0;
        });

        $result = [];
        foreach ($tags as $tag => $info) {
            $result[] = ' *';
            /**
             * @var string                                 $name
             * @var \Triun\ModelBase\Definitions\PhpDocTag $item
             */
            foreach ($info->items as $name => $item) {
                $replace  = [
                    'dummy_tag'         => $tag,
                    'dummy_type'        => str_pad($item->type, $info->max_type_length),
                    'dummy_name'        => $item->hasName() ? str_pad($item->getName(), $info->max_name_length) : '',
                    'dummy_description' => $item->description,
                ];
                $result[] = rtrim(str_replace(array_keys($replace), array_values($replace), $stub));
            }
        }

        return $result;
    }

    /**
     * Write a string as success output.
     */
    public function success(string $string, int $verbosity = OutputInterface::OUTPUT_NORMAL): void
    {
        /** @var \Illuminate\Console\OutputStyle $output */
        $output = $this->command->getOutput();
        if ($output->isVerbose() && $output->getVerbosity() >= $verbosity) {
            $output->success($string);
        } else {
            $this->command->info($string, $verbosity);
        }
    }

    /**
     * Write a string as error output.
     */
    public function error(string $string, int $verbosity = OutputInterface::OUTPUT_NORMAL): void
    {
        $output = $this->command->getOutput();
        if ($output->isVerbose() && $output->getVerbosity() >= $verbosity) {
            $output->error($string);
        } else {
            $this->command->error($string, $verbosity);
        }
    }

    /**
     * Write a string as trace output.
     */
    public function trace(string $string, int $verbosity = OutputInterface::OUTPUT_NORMAL): void
    {
        if ($this->command->getOutput()->isVerbose()) {
            $this->muted($string, $verbosity);
        }
    }

    /**
     * Write a string as warning output.
     */
    public function warning(string $string, int $verbosity = OutputInterface::OUTPUT_NORMAL): void
    {
        $output = $this->command->getOutput();
        if ($output->isVerbose() && $output->getVerbosity() >= $verbosity) {
            $output->warning($string);
        } else {
            $this->command->warn($string, $verbosity);
        }
        // $this->command->getOutput()->writeln('<fg=yellow;options=bold>'.$string.'</>'); // black bg
    }

    /**
     * Write a string as info output.
     *
     * @link https://symfony.com/doc/current/console/coloring.html
     */
    public function info(string $string, int $verbosity = OutputInterface::OUTPUT_NORMAL): void
    {
        if ($this->command->getOutput()->getVerbosity() >= $verbosity) {
            $this->command->getOutput()->writeln('<fg=cyan;options=bold>' . $string . '</>'); // black bg
        }
    }

    /**
     * Write a string as muted output.
     *
     * @link https://symfony.com/doc/current/console/coloring.html
     */
    public function muted(string $string, int $verbosity = OutputInterface::OUTPUT_NORMAL): void
    {
        $this->command->line($string, null, $verbosity);
        // $this->command->getOutput()->writeln('<fg=white;options=bold>'.$string.'</>'); // white bg
        // $this->command->getOutput()->writeln('<fg=black;options=bold>'.$string.'</>'); // black bg
    }
}
