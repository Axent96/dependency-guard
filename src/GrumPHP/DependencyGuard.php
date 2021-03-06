<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard\GrumPHP;

use Composer\Composer;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\TaskInterface;
use Mediact\DependencyGuard\DependencyGuardFactoryInterface;
use Mediact\DependencyGuard\Exporter\ViolationExporterInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DependencyGuard implements TaskInterface
{
    /** @var Composer */
    private $composer;

    /** @var DependencyGuardFactoryInterface */
    private $guardFactory;

    /** @var ViolationExporterInterface */
    private $exporter;

    /** @var null|string */
    private $workingDirectory;

    /**
     * Constructor.
     *
     * @param Composer                        $composer
     * @param DependencyGuardFactoryInterface $guardFactory
     * @param ViolationExporterInterface      $exporter
     * @param string|null                     $workingDirectory
     */
    public function __construct(
        Composer $composer,
        DependencyGuardFactoryInterface $guardFactory,
        ViolationExporterInterface $exporter,
        string $workingDirectory = null
    ) {
        $this->composer         = $composer;
        $this->guardFactory     = $guardFactory;
        $this->exporter         = $exporter;
        $this->workingDirectory = $workingDirectory ?? getcwd();
    }


    /**
     * @return string
     */
    public function getName(): string
    {
        return 'dependency-guard';
    }

    /**
     * Get the configurable options for the dependency guard.
     *
     * @return OptionsResolver
     */
    public function getConfigurableOptions(): OptionsResolver
    {
        return new OptionsResolver();
    }

    /**
     * This methods specifies if a task can run in a specific context.
     *
     * @param ContextInterface $context
     *
     * @return bool
     */
    public function canRunInContext(ContextInterface $context): bool
    {
        return (
            $context instanceof GitPreCommitContext
            || $context instanceof RunContext
        );
    }

    /**
     * @param ContextInterface $context
     *
     * @return TaskResultInterface
     */
    public function run(ContextInterface $context): TaskResultInterface
    {
        foreach (['composer.lock', 'composer.json'] as $file) {
            if (!is_readable(
                $this->workingDirectory . DIRECTORY_SEPARATOR . $file
            )) {
                return TaskResult::createSkipped($this, $context);
            }
        }

        $guard      = $this->guardFactory->create();
        $violations = $guard->determineViolations($this->composer);

        if (count($violations)) {
            $this->exporter->export($violations);

            return TaskResult::createFailed(
                $this,
                $context,
                'Encountered dependency violations.'
            );
        }

        return TaskResult::createPassed($this, $context);
    }

    /**
     * Get the configuration.
     *
     * @return array
     */
    public function getConfiguration(): array
    {
        return [];
    }
}
