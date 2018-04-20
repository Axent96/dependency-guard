<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\DependencyGuard;

use ArrayIterator;
use IteratorIterator;

class ViolationIterator extends IteratorIterator implements ViolationIteratorInterface
{
    /** @var int */
    private $numViolations;

    /**
     * Constructor.
     *
     * @param ViolationInterface ...$violations
     */
    public function __construct(ViolationInterface ...$violations)
    {
        $this->numViolations = count($violations);
        parent::__construct(
            new ArrayIterator($violations)
        );
    }

    /**
     * Get the current violation.
     *
     * @return ViolationInterface
     */
    public function current(): ViolationInterface
    {
        return parent::current();
    }

    /**
     * Get the number of violations.
     *
     * @return int
     */
    public function count(): int
    {
        return $this->numViolations;
    }
}
