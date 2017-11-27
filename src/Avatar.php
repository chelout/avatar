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
    protected $width;
    protected $height;
    protected $availableColors;
    protected $fontSize;
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
            'chars'       => 2,
            'colors' => [
                [
                    'background' => $this->background,
                    'shadow' => $this->shadow,
                    'foreground' => $this->foreground,
                ]
            ],
            'font' => resource_path('fonts/OpenSans-Bold.ttf'),
            'fontSize'    => 48,
            'width'       => 100,
            'height'      => 100,
            'uppercase'   => false,
        ];

        $config += $default;

        $this->driver = $config['driver'];
        $this->chars = $config['chars'];
        $this->availableColors = $config['colors'];
        $this->font = $config['font'];
        $this->fontSize = $config['fontSize'];
        $this->width = $config['width'];
        $this->height = $config['height'];
        $this->uppercase = $config['uppercase'];

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

        return $this;
    }

    public function setFont($font)
    {
        if (is_file($font)) {
            $this->font = $font;
        }

        return $this;
    }

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
        $this->setBackground($color['background'])
            ->setShadow($color['shadow'])
            ->setForeground($color['foreground']);

        return $this;
    }

    public function setBackground($hex)
    {
        $this->background = $hex;

        return $this;
    }

    public function setShadow($hex)
    {
        $this->shadow = $hex;

        return $this;
    }

    public function setForeground($hex)
    {
        $this->foreground = $hex;

        return $this;
    }

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
        $this->image->rectangle(0, 0, $this->width, $this->height, function (AbstractShape $draw) {
            $draw->background($this->background);
        });
    }

    protected function createTextShadow()
    {
        $shadowSize = ($this->width > $this->height ? $this->width : $this->height) / 2 + $this->fontSize / 2;

        for ($i = 0; $i <= $shadowSize; $i++) {
            $this->image->text($this->initials, $this->width / 2 + $i, $this->height / 2 + $i, function($font) {
                $font->file($this->font);
                $font->size($this->fontSize);
                $font->color($this->shadow);
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
            'chars',
            'font',
            'fontSize',
            'width',
            'height',
        ];
        foreach ($attributes as $attr) {
            $keys[] = $this->$attr;
        }

        return md5(implode('-', $keys));
    }

    protected function buildInitial()
    {
        // fallback to default
        if (!$this->initialGenerator) {
            $this->initialGenerator = new DefaultGenerator();
        }

        $this->initials = $this->initialGenerator->make($this->name, $this->chars, $this->uppercase);
    }
}
