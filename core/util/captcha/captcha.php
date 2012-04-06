<?php
/**
 * Script para la generación de CAPTCHAS
 *
 * @author  Jose Rodriguez <jose.rodriguez@exec.cl>
 * @license GPLv3
 * @link    http://code.google.com/p/cool-php-captcha
 * @package captcha
 * @version 0.3
 *
 */

class Webpie_Captcha
{
    /** Width of the image */
    public $width  = 200;

    /** Height of the image */
    public $height = 70;

    /**
     * Path for resource files (fonts, words, etc.)
     *
     * "resources" by default. For security reasons, is better move this
     * directory to another location outise the web server
     *
     */
    public $resourcesPath = 'resources';

    /** Min word length (for non-dictionary random text generation) */
    public $minWordLength = 5;

    /**
     * Max word length (for non-dictionary random text generation)
     * 
     * Used for dictionary words indicating the word-length
     * for font-size modification purposes
     */
    public $maxWordLength = 8;

    /** Sessionname to store the original text */
    public $sessionVar = 'captcha';

    /** Background color in RGB-array */
    public $backgroundColor = array(255, 255, 255);

    /** Foreground colors in RGB-array */
    public $colors = array(
        array(27, 78, 181), // blue
        array(22, 163, 35), // green
        array(214, 36, 7),  // red
    );

    /** Shadow color in RGB-array or null */
    public $shadowColor = null; //array(0, 0, 0);

    /**
     * Font configuration
     *
     * - font: TTF file
     * - spacing: relative pixel space between character
     * - minSize: min font size
     * - maxSize: max font size
     */
    public $fonts = array(
        'Antykwa'  => array('spacing' => -3, 'minSize' => 27, 'maxSize' => 30, 'font' => 'AntykwaBold.ttf'),
        'Candice'  => array('spacing' =>-1.5,'minSize' => 28, 'maxSize' => 31, 'font' => 'Candice.ttf'),
        'DingDong' => array('spacing' => -2, 'minSize' => 24, 'maxSize' => 30, 'font' => 'Ding-DongDaddyO.ttf'),
        'Duality'  => array('spacing' => -2, 'minSize' => 30, 'maxSize' => 38, 'font' => 'Duality.ttf'),
        'Heineken' => array('spacing' => -2, 'minSize' => 24, 'maxSize' => 34, 'font' => 'Heineken.ttf'),
        'Jura'     => array('spacing' => -2, 'minSize' => 28, 'maxSize' => 32, 'font' => 'Jura.ttf'),
        'StayPuft' => array('spacing' =>-1.5,'minSize' => 28, 'maxSize' => 32, 'font' => 'StayPuft.ttf'),
        'Times'    => array('spacing' => -2, 'minSize' => 28, 'maxSize' => 34, 'font' => 'TimesNewRomanBold.ttf'),
        'VeraSans' => array('spacing' => -1, 'minSize' => 20, 'maxSize' => 28, 'font' => 'VeraSansBold.ttf'),
    );

    /** Wave configuracion in X and Y axes */
    public $Yperiod    = 12;
    public $Yamplitude = 14;
    public $Xperiod    = 11;
    public $Xamplitude = 5;

    /** letter rotation clockwise */
    public $maxRotation = 8;

    /**
     * Internal image size factor (for better image quality)
     * 1: low, 2: medium, 3: high
     */
    public $scale = 2;

    /** 
     * Blur effect for better image quality (but slower image processing).
     * Better image results with scale=3
     */
    public $blur = false;
    
    /** Image format: jpeg or png */
    public $imageFormat = 'jpeg';

    /** GD image */
    public $im;

    public function __construct(){}

    public function createImage()
	{
        /** Initialization */
        $this->imageAllocate();
        
        ///** Text insertion */
        $text = $this->getCaptchaText();
        $fontcfg  = $this->fonts[array_rand($this->fonts)];
        $this->writeText($text, $fontcfg);

        $_SESSION[$this->sessionVar] = $text;

        /** Transformations */
        $this->waveImage();
        if($this->blur && function_exists('imagefilter'))
            imagefilter($this->im, IMG_FILTER_GAUSSIAN_BLUR);

        $this->reduceImage();

        /** Output */
        $this->writeImage();
        $this->cleanup();
    }

    /**
     * Creates the image resources
     */
    protected function imageAllocate()
	{
        // Cleanup
        if (!empty($this->im))
            imagedestroy($this->im);

        $this->im = imagecreatetruecolor($this->width*$this->scale, $this->height*$this->scale);

        // Background color
        $this->gdBgColor = imagecolorallocate($this->im,
            $this->backgroundColor[0],
            $this->backgroundColor[1],
            $this->backgroundColor[2]
        );
        imagefilledrectangle($this->im, 0, 0, $this->width*$this->scale, $this->height*$this->scale, $this->gdBgColor);

        // Foreground color
        $color = $this->colors[mt_rand(0, sizeof($this->colors)-1)];
        $this->gdFgColor = imagecolorallocate($this->im, $color[0], $color[1], $color[2]);

        // Shadow color
        if (!empty($this->shadowColor) && 
			is_array($this->shadowColor) && 
			sizeof($this->shadowColor) >= 3)
		{
            $this->gdShadowColor = imagecolorallocate($this->im,
                $this->shadowColor[0],
                $this->shadowColor[1],
                $this->shadowColor[2]
            );
        }
    }

    /**
     * Text generation
     *
     * @return string Text
     */
    protected function getCaptchaText()
	{
		return $this->getRandomCaptchaText();
    }

    /**
     * Random text generation
     *
     * @return string Text
     */
    protected function getRandomCaptchaText($length = null)
	{
        if(empty($length))
            $length = rand($this->minWordLength, $this->maxWordLength);

        $words  = 'abcdefghijlmnopqrstvwyz';
        $vocals = 'aeiou';

        $text  = '';
        $vocal = rand(0, 1);
        for($i=0; $i<$length; $i++)
		{
            if($vocal)
                $text .= substr($vocals, mt_rand(0, 4), 1);
            else
                $text .= substr($words, mt_rand(0, 22), 1);

            $vocal = !$vocal;
        }
        return $text;
    }

    /**
     * Text insertion
     */
    protected function writeText($text, $fontcfg = array())
	{
        if(empty($fontcfg))
            // Select the font configuration
            $fontcfg  = $this->fonts[array_rand($this->fonts)];

        // Full path of font file
        $fontfile = __DIR__ . DS . $this->resourcesPath . DS . 'fonts' . DS . $fontcfg['font'];

        /** Increase font-size for shortest words: 9% for each glyp missing */
        $lettersMissing = $this->maxWordLength-strlen($text);
        $fontSizefactor = 1+($lettersMissing*0.09);

        // Text generation (char by char)
        $x = 20*$this->scale;
        $y = round(($this->height*27/40)*$this->scale);
        $length = strlen($text);
        for($i=0; $i<$length; $i++)
		{
            $degree = rand($this->maxRotation*-1, $this->maxRotation);
            $fontsize = rand($fontcfg['minSize'], $fontcfg['maxSize'])*$this->scale*$fontSizefactor;
            $letter = substr($text, $i, 1);

            if($this->shadowColor)
			{
                $coords = imagettftext($this->im, $fontsize, $degree,
                    $x+$this->scale, $y+$this->scale,
                    $this->gdShadowColor, $fontfile, $letter);
            }

            $coords = imagettftext($this->im, $fontsize, $degree,
                $x, $y, $this->gdFgColor, $fontfile, $letter);
            $x += ($coords[2]-$x) + ($fontcfg['spacing']*$this->scale);
        }
    }

    /**
     * Wave filter
     */
    protected function waveImage()
	{
        // X-axis wave generation
        $xp = $this->scale*$this->Xperiod*rand(1,3);
        $k = rand(0, 100);
        for($i = 0; $i < ($this->width*$this->scale); $i++)
		{
            imagecopy($this->im, $this->im,
                $i-1, sin($k+$i/$xp) * ($this->scale*$this->Xamplitude),
                $i, 0, 1, $this->height*$this->scale);
        }

        // Y-axis wave generation
        $k = rand(0, 100);
        $yp = $this->scale*$this->Yperiod*rand(1,2);
        for($i = 0; $i < ($this->height*$this->scale); $i++)
		{
            imagecopy($this->im, $this->im,
                sin($k+$i/$yp) * ($this->scale*$this->Yamplitude), $i-1,
                0, $i, $this->width*$this->scale, 1);
        }
    }

    /**
     * Reduce the image to the final size
     */
    protected function reduceImage()
	{
        // Reduzco el tamaño de la imagen
        $imResampled = imagecreatetruecolor($this->width, $this->height);
        imagecopyresampled($imResampled, $this->im,
            0, 0, 0, 0, $this->width, $this->height,
            $this->width*$this->scale, $this->height*$this->scale
        );
        imagedestroy($this->im);
        $this->im = $imResampled;
    }

    /**
     * File generation
     */
    protected function writeImage()
	{
        if($this->imageFormat == 'png' && function_exists('imagepng'))
		{
            header("Content-type: image/png");
            imagepng($this->im);
        }
		else
		{
            header("Content-type: image/jpeg");
            imagejpeg($this->im, null, 80);
        }
    }

    /**
     * Cleanup
     */
    protected function cleanup()
	{
        imagedestroy($this->im);
    }
}
















?>
