<?php

namespace Kaareln\SVGPathData\Attributes\PathData;

class RelativeLine extends RelativeMove
{
    public static function getNames(): array
    {
        return ['l'];
    }

    public function getName(): string
    {
        return 'l';
    }
}
