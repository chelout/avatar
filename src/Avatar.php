<?php

namespace Chelout\Avatar;

use Illuminate\Cache\ArrayStore;
use Illuminate\Contracts\Cache\Repository;
use Intervention\Image\AbstractFont;
use Intervention\Image\AbstractShape;
use Intervention\Image\ImageManager;
use Chelout\Avatar\Generator\DefaultGenerator;
use Chelout\Avatar\Generator\GeneratorInterface;

class Avatar
{
    protected $name;

    protected $chars;
    // protected $shape;
    protected $width;
    protected $height;
    // protected $availableBackgrounds;
    // protected $availableForegrounds;
    protected $availableColors;
    // protected $fonts;
    // protected $font;
    protected $fontSize;
    // protected $borderSize = 0;
    // protected $borderColor;
    // protected $ascii = false;
    protected $uppercase = true;

    /**
     * @var \Intervention\Image\Image
     */
    protected $image;
    protected $font = null;
    protected $background = '#eeeeee';
    protected $shadow = '#cccccc';
    protected $foreground = '#ffffff';
    protected $initials = '';

    protected $cache;
    protected $driver;

    protected $initialGenerator;

    // protected $defaultFont = __DIR__.'/../fonts/HelveticaNeueCyr-Medium.otf';

    /**
     * Avatar constructor.
     *
     * @param array      $config
     * @param Repository $cache
     */
    public function __construct(array $config = [], Repository $cache = null)
    {
        $default = [
            'driver'      => 'gd',
            // 'shape'       => 'circle',
            'chars'       => 2,
            // 'backgrounds' => [$this->background],
            // 'foregrounds' => [$this->foreground],
            'colors' => [
                [
                    'background' => $this->background,
                    'shadow' => $this->shadow,
                    'foreground' => $this->foreground,
                ]
            ],
            // 'fonts'       => [$this->defaultFont],
            'font' => __DIR__.'/../fonts/HelveticaNeueCyr-Medium.otf',
            'fontSize'    => 48,
            'width'       => 100,
            'height'      => 100,
            // 'ascii'       => false,
            'uppercase'   => false,
            // 'border'      => [
            //     'size'  => 1,
            //     'color' => 'foreground',
            // ],
        ];

        $config += $default;
        // dd($config, $default);

        $this->driver = $config['driver'];
        // $this->shape = $config['shape'];
        $this->chars = $config['chars'];
        // $this->availableBackgrounds = $config['backgrounds'];
        // $this->availableForegrounds = $config['foregrounds'];
        $this->availableColors = $config['colors'];
        // $this->fonts = $config['fonts'];
        $this->font = $config['font'];
        $this->fontSize = $config['fontSize'];
        $this->width = $config['width'];
        $this->height = $config['height'];
        // $this->ascii = $config['ascii'];
        $this->uppercase = $config['uppercase'];
        // $this->borderSize = $config['border']['size'];
        // $this->borderColor = $config['border']['color'];

        if (\is_null($cache)) {
            $cache = new ArrayStore();
        }

        $this->cache = $cache;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->toBase64();
    }

    public function setGenerator(GeneratorInterface $generator)
    {
        $this->initialGenerator = $generator;
    }

    public function create($name)
    {
        $this->name = $name;

        $this->setColors($this->getRandomColor());

        // $this->setForeground($this->getRandomForeground());
        // $this->setBackground($this->getRandomBackground());

        return $this;
    }

    // public function setFont($font)
    // {
    //     if (is_file($font)) {
    //         $this->font = $font;
    //     }

    //     return $this;
    // }

    public function toBase64()
    {
        $key = $this->cacheKey();
        if ($base64 = $this->cache->get($key)) {
            return $base64;
        }

        $this->buildAvatar();

        $base64 = $this->image->encode('data-url');

        $this->cache->put($key, $base64, 0);

        return $base64;
    }

    public function save($path, $quality = 90)
    {
        $this->buildAvatar();

        return $this->image->save($path, $quality);
    }

    public function setColors(array $color)
    {
        // $this->background = $color['background'];
        // $this->shadow = $color['shadow'];
        // $this->foreground = $color['foreground'];

        list('background' => $this->background, 'shadow' => $this->shadow, 'foreground' => $this->foreground) = $color;

        return $this;
    }

    // public function setBackground($hex)
    // {
    //     $this->background = $hex;

    //     return $this;
    // }

    // public function setForeground($hex)
    // {
    //     $this->foreground = $hex;

    //     return $this;
    // }

    public function setDimension($width, $height = null)
    {
        if (!$height) {
            $height = $width;
        }
        $this->width = $width;
        $this->height = $height;

        return $this;
    }

    public function setFontSize($size)
    {
        $this->fontSize = $size;

        return $this;
    }

    // public function setBorder($size, $color)
    // {
    //     $this->borderSize = $size;
    //     $this->borderColor = $color;

    //     return $this;
    // }

    public function getInitial()
    {
        return $this->initials;
    }

    public function getImageObject()
    {
        $this->buildAvatar();

        return $this->image;
    }

    protected function getRandomColor()
    {
        return collect($this->availableColors)->random();
    }

    // protected function getRandomBackground()
    // {
    //     return $this->getRandomElement($this->availableBackgrounds, $this->background);
    // }

    // protected function getRandomForeground()
    // {
    //     return $this->getRandomElement($this->availableForegrounds, $this->foreground);
    // }

    // protected function setRandomFont()
    // {
    //     $randomFont = $this->getRandomElement($this->fonts, $this->defaultFont);

    //     $this->setFont($randomFont);
    // }

    // protected function getBorderColor()
    // {
    //     if ($this->borderColor == 'foreground') {
    //         return $this->foreground;
    //     }
    //     if ($this->borderColor == 'background') {
    //         return $this->background;
    //     }

    //     return $this->borderColor;
    // }

    public function buildAvatar()
    {
        $this->buildInitial();

        $manager = new ImageManager(['driver' => $this->driver]);
        $this->image = $manager->canvas($this->width, $this->height, array(0, 0, 0, 0));

        $this->createSquareShape();

        $this->createTextShadow();

        $this->createText();

        return $this;
    }

    protected function createSquareShape()
    {
        $this->image->rectangle(
            0,
            0,
            $this->width,
            $this->height,
            function (AbstractShape $draw) {
                $draw->background($this->background);
                // $draw->border($this->borderSize, $this->getBorderColor());
            }
        );
    }

    protected function createTextShadow()
    {
        $shadowSize = ($this->width > $this->height ? $this->width : $this->height) / 2 + $this->fontSize / 2;

        for ($i = 0; $i <= $shadowSize; $i++) {
            $this->image->text($this->initials, $this->width / 2 + $i, $this->height / 2 + $i, function($font) {
                $font->file($this->font);
                $font->size($this->fontSize);
                $font->color($this->shadow);
                // $font->color(array(0, 0, 0, 0.1));
                $font->align('center');
                $font->valign('middle');
            });
        }
    }

    protected function createText()
    {
        $this->image->text($this->initials, $this->width / 2, $this->height / 2, function($font) {
            $font->file($this->font);
            $font->size($this->fontSize);
            $font->color($this->foreground);
            $font->align('center');
            $font->valign('middle');
        });
    }

    protected function cacheKey()
    {
        $keys = [];
        $attributes = [
            'name',
            'initials',
            // 'shape',
            'chars',
            'font',
            'fontSize',
            'width',
            'height',
            // 'borderSize',
            // 'borderColor',
        ];
        foreach ($attributes as $attr) {
            $keys[] = $this->$attr;
        }

        return md5(implode('-', $keys));
    }

    // protected function getRandomElement($array, $default)
    // {
    //     if (strlen($this->name) == 0 || count($array) == 0) {
    //         return $default;
    //     }

    //     $number = ord($this->name[0]);
    //     $i = 1;
    //     $charLength = strlen($this->name);
    //     while ($i < $charLength) {
    //         $number += ord($this->name[$i]);
    //         $i++;
    //     }

    //     return $array[$number % count($array)];
    // }

    protected function buildInitial()
    {
        // fallback to default
        if (!$this->initialGenerator) {
            $this->initialGenerator = new DefaultGenerator();
        }

        $this->initials = $this->initialGenerator->make($this->name, $this->chars, $this->uppercase);
    }
}
