<?php declare(strict_types=1);

namespace NbGroup\Symfony\DependencyInjection;

use NbGroup\Symfony\ArrayCastEnvVarProcessor;
use NbGroup\Symfony\CsvEnvVarProcessor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Extension\Extension;

/**
 * @internal
 * @final
 */
class NbgroupEnvExtension extends Extension
{
    private const NB_REPLACEMENT_MARK = '/* NB */';

    /** @phpstan-ignore-next-line */
    public function getConfiguration(array $config, ContainerBuilder $container): Configuration
    {
        return new Configuration();
    }

    /** @phpstan-ignore-next-line */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        if ($config['array_cast']['enabled']) {
            $this->addArrayCastProcessor($container);
        }

        if ($config['csv']['enabled']) {
            $this->addCsvProcessor($config['csv'], $container);
        }
    }

    private function addArrayCastProcessor(ContainerBuilder $container): void
    {
        $definition = new Definition(ArrayCastEnvVarProcessor::class);
        $definition->addTag('container.env_var_processor');
        $container->setDefinition(ArrayCastEnvVarProcessor::class, $definition);
    }

    /**
     * @param array{delimiters: array<string, string>} $config
     */
    private function addCsvProcessor(array $config, ContainerBuilder $container): void
    {
        $types = [];
        $delimiterMap = [];

        foreach ($config['delimiters'] as $name => $delimiter) {
            $name = "csv-{$name}";
            $types[] = "'{$name}' => 'array'";
            $delimiterMap[$name] = $delimiter;
        }

        $classSource = file_get_contents(\dirname(__DIR__).'/CsvEnvVarProcessor.php');
        if ($classSource === false) {
            throw new LogicException('Cannot get CsvEnvVarProcessor source.');
        }

        $classSource = strstr($classSource, 'namespace');
        if ($classSource === false) {
            throw new LogicException('CsvEnvVarProcessor source does not contain "namespace" keyword.');
        }

        $start = strpos($classSource, self::NB_REPLACEMENT_MARK);
        if ($start === false) {
            throw new LogicException('Opening NB replacement mark not found');
        }

        $end = strpos($classSource, self::NB_REPLACEMENT_MARK, $start + 1);
        if ($end === false) {
            throw new LogicException('Closing NB replacement mark not found');
        }

        eval(substr_replace(
            $classSource,
            'return ['.implode(', ', $types).'];',
            $start,
            $end - $start + 8
        ));

        // touch CsvEnvVarProcessor source file
        include_once \dirname(__DIR__).'/CsvEnvVarProcessor.php';

        $definition = new Definition(CsvEnvVarProcessor::class, [$delimiterMap]);
        $definition->addTag('container.env_var_processor');
        $container->setDefinition(CsvEnvVarProcessor::class, $definition);
    }
}
