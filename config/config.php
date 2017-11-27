<?php
/*
 * Set specific configuration variables here
 */
return [

    /*
    |--------------------------------------------------------------------------
    | Image Driver
    |--------------------------------------------------------------------------
    | Avatar use Intervention Image library to process image.
    | Meanwhile, Intervention Image supports "GD Library" and "Imagick" to process images
    | internally. You may choose one of them according to your PHP
    | configuration. By default PHP's "GD Library" implementation is used.
    |
    | Supported: "gd", "imagick"
    |
    */
    'driver'    => 'gd',

    // Initial generator class
    'generator' => \Chelout\Avatar\Generator\DefaultGenerator::class,

    // Image width, in pixel
    'width'    => 100,

    // Image height, in pixel
    'height'   => 100,

    // Number of characters used as initials. If name consists of single word, the first N character will be used
    'chars'    => 2,

    // font size
    'fontSize' => 30,

    // convert initial letter in uppercase
    'uppercase' => true,

    // Fonts used to render text.
    // If contains more than one fonts, randomly selected based on name supplied
    // 'fonts' => [
    //     __DIR__ . '/../fonts/OpenSans-Bold.ttf',
    //     __DIR__ . '/../fonts/rockwell.ttf',
    //     __DIR__ . '/../HelveticaNeueCyr-Medium.otf',
    //     __DIR__ . '/../HelveticaNeueCyr-Light.otf'
    // ],

    'font' => resource_path('fonts/OpenSans-Bold.ttf'),

    // List of colors
    'colors' => [
        [
            'background' => '#5fa015',
            'shadow' => '#589824',
            'foreground' => '#ffffff',
        ],
        [
            'background' => '#8fb718',
            'shadow' => '#85AE2A',
            'foreground' => '#ffffff',
        ],
        [
            'background' => '#0b94a9',
            'shadow' => '#188D9F',
            'foreground' => '#ffffff',
        ],
        [
            'background' => '#23abd3',
            'shadow' => '#2DA3C7',
            'foreground' => '#ffffff',
        ],
        [
            'background' => '#ff9800',
            'shadow' => '#F08F19',
            'foreground' => '#ffffff',
        ],
        [
            'background' => '#ffc035',
            'shadow' => '#F0B63E',
            'foreground' => '#ffffff',
        ],
        [
            'background' => '#c23b3b',
            'shadow' => '#B13236',
            'foreground' => '#ffffff',
        ],
        [
            'background' => '#b74178',
            'shadow' => '#A7376D',
            'foreground' => '#ffffff',
        ],
    ],
];
