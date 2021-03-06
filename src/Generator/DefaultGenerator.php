<?php

namespace Chelout\Avatar\Generator;

use Illuminate\Support\Collection;
use Stringy\Stringy;

class DefaultGenerator implements GeneratorInterface
{
    public function make($name, $length = 2, $uppercase = false)
    {
        $this->setName($name);

        $words = new Collection(explode(' ', $this->name));

        // if name contains single word, use first N character
        if ($words->count() === 1) {
            $initial = (string) $words->first();

            if ($this->name->length() >= $length) {
                $initial = (string) $this->name->substr(0, $length);
            }
        } else {
            // otherwise, use initial char from each word
            $initials = new Collection();
            $words->each(function ($word) use ($initials) {
                $initials->push(Stringy::create($word)->substr(0, 1));
            });

            $initial = $initials->slice(0, $length)->implode('');
        }

        if ($uppercase) {
            $initial = mb_strtoupper($initial);
        }

        return $initial;
    }

    private function setName($name)
    {
        if (is_array($name)) {
            throw new \InvalidArgumentException(
                'Passed value cannot be an array'
            );
        } elseif (is_object($name) && !method_exists($name, '__toString')) {
            throw new \InvalidArgumentException(
                'Passed object must have a __toString method'
            );
        }

        $name = Stringy::create($name)->collapseWhitespace();

        if (filter_var($name, FILTER_VALIDATE_EMAIL)) {
            // turn email into name
            $name = current($name->split('@', 1))->replace('.', ' ');
        }

        $this->name = $name;
    }
}
