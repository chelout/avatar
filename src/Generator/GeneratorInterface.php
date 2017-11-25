<?php

namespace Chelout\Avatar\Generator;

interface GeneratorInterface
{
    public function make($name, $length, $uppercase);
}
