<?php

namespace Kaareln\SVGPathData\Attributes;

use Kaareln\SVGPathData\Attributes\PathData\ArcCurve;
use Kaareln\SVGPathData\Attributes\PathData\BezierCurve;
use Kaareln\SVGPathData\Attributes\PathData\ClosePath;
use Kaareln\SVGPathData\Attributes\PathData\HorizontalLine;
use Kaareln\SVGPathData\Attributes\PathData\Line;
use Kaareln\SVGPathData\Attributes\PathData\Move;
use Kaareln\SVGPathData\Attributes\PathData\PathDataCommandInterface;
use Kaareln\SVGPathData\Attributes\PathData\QuadraticCurve;
use Kaareln\SVGPathData\Attributes\PathData\RelativeArcCurve;
use Kaareln\SVGPathData\Attributes\PathData\RelativeBezierCurve;
use Kaareln\SVGPathData\Attributes\PathData\RelativeHorizontalLine;
use Kaareln\SVGPathData\Attributes\PathData\RelativeLine;
use Kaareln\SVGPathData\Attributes\PathData\RelativeMove;
use Kaareln\SVGPathData\Attributes\PathData\RelativeQuadraticCurve;
use Kaareln\SVGPathData\Attributes\PathData\RelativeVerticalLine;
use Kaareln\SVGPathData\Attributes\PathData\VerticalLine;

class SVGPathData implements SVGAttributeInterface, \Iterator, \Countable
{
    public const ATTRIBUTE_NAME = 'd';

    /**
     * @var class-string<PathDataCommandInterface>[]
     */
    public static array $commands = [
        ClosePath::class,
        Move::class,
        RelativeMove::class,
        Line::class,
        RelativeLine::class,
        BezierCurve::class,
        RelativeBezierCurve::class,
        ArcCurve::class,
        RelativeArcCurve::class,
        QuadraticCurve::class,
        RelativeQuadraticCurve::class,
        HorizontalLine::class,
        RelativeHorizontalLine::class,
        VerticalLine::class,
        RelativeVerticalLine::class,
    ];

    private ?PathDataCommandInterface $head = null;

    private ?PathDataCommandInterface $iteratorCurrent = null;

    public static function fromString(string $pathDataString): SVGPathData
    {
        $pathData = new static();

        $cmdStrings = [];
        preg_match_all("/\s*([a-z])\s*([^a-z]+)*/i", $pathDataString, $cmdStrings);

        foreach ($cmdStrings[0] as $cmdString) {
            $cmdParts = explode(" ", $cmdString);
            $cmdParts = array_map(fn ($cmdPart) => trim($cmdPart), $cmdParts);
            $cmdName = array_shift($cmdParts);

            $cmdClass = null;

            foreach (self::$commands as $cmdClassCandidate) {
                if (in_array($cmdName, $cmdClassCandidate::getNames())) {
                    $cmdClass = $cmdClassCandidate;
                    break;
                }
            }

            if (!$cmdClass) {
                throw new \RuntimeException(sprintf("Couldn't find class for '%s' SVG path part", $cmdString));
            }

            $cmd = new $cmdClass(...$cmdParts);
            $pathData->addCommand($cmd);
        }

        return $pathData;
    }

    public function getName(): string
    {
        return self::ATTRIBUTE_NAME;
    }

    public function __toString(): string
    {
        $d = [];

        $command = $this->head;

        while ($command) {
            array_unshift($d, $command->__toString());
            $command = $command->getPrevious();
        }

        return join(' ', $d);
    }

    public function addCommand(PathDataCommandInterface $command): self
    {
        if ($this->head) {
            $command->setPrevious($this->head);
        }
        $this->head = $command;

        return $this;
    }

    /**
     * This is similar to Array map functions. Each command will be run through provided $transformator function
     * that can either returned same instance (modified or not) or another one that will replace the existing command
     *
     * @param callable(PathDataCommandInterface $command): PathDataCommandInterface $transformator
     */
    public function transform(callable $transformator): void
    {
        $command = $this->head;
        // command following the currently looped one
        // initially it's empty, as we loop from end, and last command is not followed by anything
        $nextCmd = null;

        while ($command) {
            $transformedCommand = $transformator($command);

            if ($transformedCommand !== $command) {
                // if command returned a different instance it needs to be replaced in a chaing
                $transformedCommand->setPrevious($command->getPrevious());
                $nextCmd->setPrevious($transformedCommand);
            }

            $nextCmd = $command;
            $command = $command->getPrevious();
        }
    }

    public function rewind(): void
    {
        $this->iteratorCurrent = $this->head;
    }

    public function key(): int
    {
        $command = $this->head;
        $index = 0;

        while ($command) {
            $index--;
            $command = $command->getPrevious();
        }

        return $index;
    }

    public function current(): ?PathDataCommandInterface
    {
        return $this->iteratorCurrent ?? $this->head;
    }

    public function next(): void
    {
        $this->iteratorCurrent = $this->current()?->getPrevious();
    }

    public function valid(): bool
    {
        return $this->iteratorCurrent !== null;
    }

    public function count(): int
    {
        $count = 0;

        $command = $this->head;

        while ($command) {
            $count++;
            $command = $command->getPrevious();
        }

        return $count;
    }
}
