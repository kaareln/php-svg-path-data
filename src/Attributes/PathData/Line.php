<?php

namespace Kaareln\SVGPathData\Attributes\PathData;

class Line extends Move
{
    public static function getNames(): array
    {
        return ['L'];
    }

    public function getName(): string
    {
        return 'L';
    }
}
