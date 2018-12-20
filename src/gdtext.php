<?php
/**
 * align your text on the image
 *
 * @param resource $gdtext is the gdtext
 * @param int $x is the left-most point of the text box on the image
 * @param int $y is the upper-most point of the text box on the image
 * @return resource the gdtext
 */
function gdtext_align($gdtext, $x, $y) {
    $gdtext->setTextAlign($x, $y);

    return $gdtext;
}

/**
 * initialize a text box
 *
 * @param resource $gdtext
 * @param int $x is the left-most point of the text on the image
 * @param int $y is tge upper-most point fo the text on the image
 * @param int $w is the width of the text
 * @param int $h is the height odf the text
 * @return resource the gdtext
 */
function gdtext_box($gdtext, $x = 0, $y = 0, $w = null, $h = null) {
    $gdtext->setBox($x, $y, is_null($w) ? imagesx($gdtext->getImage()) : $w, is_null($h) ? imagesy($gdtext->getImage()) : $y);

    return $gdtext;
}

/**
 * sets the color of the text
 *
 * @param resource $gdtext
 * @param array $rgb the RGB values, ranging from 0-255 for each value
 * @return resource the gdtext
 */
function gdtext_color($gdtext, $rgb) {
    $rgb = array_values($rgb);

    $gdtext->setFontColor(new Color($rgb[0], $rgb[1], $rgb[2]));

    return $gdtext;
}

/**
 * draw the text onto the image
 *
 * @param resource $gdtext
 * @return resource the gdtext
 */
function gdtext_draw($gdtext) {
    $gdtext->draw($gdtext->get('text'));

    return $gdtext;
}

/**
 * sets the font
 *
 * @param resource $gdtext
 * @param string $font
 * @return resource gdtext
 */
function gdtext_font($gdtext, $font) {
    $gdtext->setFontFace($font);

    return $gdtext;
}

/**
 * gets the gdtext image
 *
 * @param resource $gdtext
 * @return mixed
 */
function gdtext_image($gdtext) {
    return $gdtext->getImage();
}

/**
 * opens gdtext resource
 *
 * @param resource $image image resource
 * @return resource gdtext resource
 */
function gdtext_open($image) {
    gdtext_load();

    class mygdtextbox extends GDTextBox {
        public function getImage() {
            return $this->get('im');
        }

        public function get($property) {
            return $this->$property;
        }
    }

    return new mygdtextbox($image);
}

/**
 * sets shadow on the text
 *
 * @param resource $gdtext
 * @param array $rgb the RGB values, between 0-255 each
 * @param int $opacity between 0-100
 * @param int $xoff the x-axis offset
 * @param int $yoff y-axis offset
 * @return resource the gdtext
 */
function gdtext_shadow($gdtext, $rgb, $opacity = 50, $xoff = 1, $yoff = 1) {
    $rgb = array_values($rgb);

    $gdtext->setTextShadow(new Color($rgb[0], $rgb[1], $rgb[2], $opacity), $xoff, $yoff);

    return $gdtext;
}

/**
 * sets the text size
 *
 * @param resource $gdtext
 * @param int $size for size in pixels
 * @return resource the gdtext resource
 */
function gdtext_size($gdtext, $size) {
    $gdtext->setFontSize($size);

    return $gdtext;
}

/**
 * sets the text
 *
 * @param resource $gdtext
 * @param string $text
 * @return resource
 */
function gdtext_text($gdtext, $text) {
    $gdtext->text = $text;

    return $gdtext;
}

/**
 * loads the gdtext classes
 */
function gdtext_load() {
    if (class_exists('GDTextBox')) {
        return;
    }

    class GDTextBox
    {
        /**
         * @var resource
         */
        protected $im;

        /**
         * @var int
         */
        protected $fontSize = 12;

        /**
         * @var Color
         */
        protected $fontColor;

        /**
         * @var string
         */
        protected $alignX = 'left';

        /**
         * @var string
         */
        protected $alignY = 'top';

        /**
         * @var float
         */
        protected $lineHeight = 1.25;

        /**
         * @var float
         */
        protected $baseline = 0.2;

        /**
         * @var string
         */
        protected $fontFace = null;

        /**
         * @var bool
         */
        protected $debug = false;

        /**
         * @var bool|array
         */
        protected $textShadow = false;

        /**
         * @var array
         */
        protected $box = array(
            'x' => 0,
            'y' => 0,
            'width' => 100,
            'height' => 100
        );

        public function __construct(&$image)
        {
            $this->im = $image;
            $this->fontColor = new Color(0, 0, 0);
        }

        /**
         * @param Color $color Font color
         */
        public function setFontColor(Color $color)
        {
            $this->fontColor = $color;
        }

        /**
         * @param string $path Path to the font file
         */
        public function setFontFace($path)
        {
            $this->fontFace = $path;
        }

        /**
         * @param int $v Font size in *pixels*
         */
        public function setFontSize($v)
        {
            $this->fontSize = $v;
        }

        /**
         * @param Color $color Shadow color
         * @param int $xShift Relative shadow position in pixels. Positive values move shadow to right, negative to left.
         * @param int $yShift Relative shadow position in pixels. Positive values move shadow to bottom, negative to up.
         */
        public function setTextShadow(Color $color, $xShift, $yShift)
        {
            $this->textShadow = array(
                'color' => $color,
                'x' => $xShift,
                'y' => $yShift
            );
        }

        /**
         * Allows to customize spacing between lines.
         * @param float $v Height of the single text line, in percents, proportionally to font size
         */
        public function setLineHeight($v)
        {
            $this->lineHeight = $v;
        }

        /**
         * @param float $v Position of baseline, in percents, proportionally to line height measuring from the bottom.
         */
        public function setBaseline($v)
        {
            $this->baseline = $v;
        }

        /**
         * Sets text alignment inside textbox
         * @param string $x Horizontal alignment. Allowed values are: left, center, right.
         * @param string $y Vertical alignment. Allowed values are: top, center, bottom.
         */
        public function setTextAlign($x = 'left', $y = 'top')
        {
            $xAllowed = array('left', 'right', 'center');
            $yAllowed = array('top', 'bottom', 'center');

            if (!in_array($x, $xAllowed)) {
                throw new \InvalidArgumentException('Invalid horizontal alignement value was specified.');
            }

            if (!in_array($y, $yAllowed)) {
                throw new \InvalidArgumentException('Invalid vertical alignement value was specified.');
            }

            $this->alignX = $x;
            $this->alignY = $y;
        }

        /**
         * Sets textbox position and dimensions
         * @param int $x Distance in pixels from left edge of image.
         * @param int $y Distance in pixels from top edge of image.
         * @param int $width Width of texbox in pixels.
         * @param int $height Height of textbox in pixels.
         */
        public function setBox($x, $y, $width, $height)
        {
            $this->box['x'] = $x;
            $this->box['y'] = $y;
            $this->box['width'] = $width;
            $this->box['height'] = $height;
        }

        /**
         * Enables debug mode. Whole textbox and individual lines will be filled with random colors.
         */
        public function enableDebug()
        {
            $this->debug = true;
        }

        /**
         * Draws the text on the picture.
         * @param string $text Text to draw. May contain newline characters.
         */
        public function draw($text)
        {
            if (!isset($this->fontFace)) {
                throw new \InvalidArgumentException('No path to font file has been specified.');
            }

            $lines = array();
            // Split text explicitly into lines by \n, \r\n and \r
            $explicitLines = preg_split('/\n|\r\n?/', $text);
            foreach ($explicitLines as $line) {
                // Check every line if it needs to be wrapped
                $words = explode(" ", $line);
                $line = $words[0];
                for ($i = 1; $i < count($words); $i++) {
                    $box = $this->calculateBox($line." ".$words[$i]);
                    if (($box[4]-$box[6]) >= $this->box['width']) {
                        $lines[] = $line;
                        $line = $words[$i];
                    } else {
                        $line .= " ".$words[$i];
                    }
                }
                $lines[] = $line;
            }

            if ($this->debug) {
                // Marks whole texbox area with color
                $this->drawFilledRectangle(
                    $this->box['x'],
                    $this->box['y'],
                    $this->box['width'],
                    $this->box['height'],
                    new Color(rand(180, 255), rand(180, 255), rand(180, 255), 80)
                );
            }

            $lineHeightPx = $this->lineHeight * $this->fontSize;
            $textHeight = count($lines) * $lineHeightPx;

            switch ($this->alignY) {
                case 'center':
                    $yAlign = ($this->box['height'] / 2) - ($textHeight / 2);
                    break;
                case 'bottom':
                    $yAlign = $this->box['height'] - $textHeight;
                    break;
                case 'top':
                default:
                    $yAlign = 0;
            }

            $n = 0;
            foreach ($lines as $line) {
                $box = $this->calculateBox($line);
                $boxWidth = $box[2] - $box[0];
                switch ($this->alignX) {
                    case 'center':
                        $xAlign = ($this->box['width'] - $boxWidth) / 2;
                        break;
                    case 'right':
                        $xAlign = ($this->box['width'] - $boxWidth);
                        break;
                    case 'left':
                    default:
                        $xAlign = 0;
                }
                $yShift = $lineHeightPx * (1 - $this->baseline);

                // current line X and Y position
                $xMOD = $this->box['x'] + $xAlign;
                $yMOD = $this->box['y'] + $yAlign + $yShift + ($n * $lineHeightPx);

                if ($this->debug) {
                    // Marks current line with color
                    $this->drawFilledRectangle(
                        $xMOD,
                        $this->box['y'] + $yAlign + ($n * $lineHeightPx),
                        $boxWidth,
                        $lineHeightPx,
                        new Color(rand(1, 180), rand(1, 180), rand(1, 180))
                    );
                }

                if ($this->textShadow !== false) {
                    $this->drawInternal(
                        $xMOD + $this->textShadow['x'],
                        $yMOD + $this->textShadow['y'],
                        $this->textShadow['color'],
                        $line
                    );
                }

                $this->drawInternal(
                    $xMOD,
                    $yMOD,
                    $this->fontColor,
                    $line
                );

                $n++;
            }
        }

        protected function getFontSizeInPoints()
        {
            return 0.75 * $this->fontSize;
        }

        protected function drawFilledRectangle($x, $y, $width, $height, Color $color)
        {
            imagefilledrectangle($this->im, $x, $y, $x + $width, $y + $height,
                $color->getIndex($this->im)
            );
        }

        protected function calculateBox($text)
        {
            return imageftbbox($this->getFontSizeInPoints(), 0, $this->fontFace, $text);
        }

        protected function drawInternal($x, $y, Color $color, $text)
        {
            imagefttext(
                $this->im,
                $this->getFontSizeInPoints(),
                0, // no rotation
                $x,
                $y,
                $color->getIndex($this->im),
                $this->fontFace,
                $text
            );
        }
    }

    if (class_exists('GDTextColor')) {
        return;
    }

    class GDTextColor
    {
        /**
         * @var int
         */
        protected $red;

        /**
         * @var int
         */
        protected $green;

        /**
         * @var int
         */
        protected $blue;

        /**
         * @var int|null
         */
        protected $alpha;

        /**
         * @param int $red Value of red component 0-255
         * @param int $green Value of green component 0-255
         * @param int $blue Value of blue component 0-255
         * @param int $alpha A value between 0 and 127. 0 indicates completely opaque while 127 indicates completely transparent.
         */
        public function __construct($red = 0, $green = 0, $blue = 0, $alpha = null)
        {
            $this->red = $red;
            $this->green = $green;
            $this->blue = $blue;
            $this->alpha = $alpha;
        }

        /**
         * @param resource $image GD image resource
         * @return int Returns the index of the specified color+alpha in the palette of the image,
         *             or -1 if the color does not exist in the image's palette.
         */
        public function getIndex($image)
        {
            if ($this->hasAlphaChannel()) {
                return imagecolorexactalpha(
                    $image,
                    $this->red,
                    $this->green,
                    $this->blue,
                    $this->alpha
                );
            } else {
                return imagecolorexact(
                    $image,
                    $this->red,
                    $this->green,
                    $this->blue
                );
            }
        }

        /**
         * @return bool TRUE when alpha channel is specified, FALSE otherwise
         */
        public function hasAlphaChannel()
        {
            return $this->alpha !== null;
        }
    }
}