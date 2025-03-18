<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Intervention\Image\Facades\Image;
use Intervention\Image\Image as InterventionImage;

class ImageProcessor
{
    public function processImage(UploadedFile $imageFile): InterventionImage
    {
        $image = Image::make($imageFile);

        $mainColor = $this->getMainColor($image);
        $watermarkColor = $this->getWatermarkColor($mainColor);

        
        $fontPath = public_path('fonts/Arial.ttf'); 

        if (!file_exists($fontPath)) {
            throw new \Exception("Font file not found at: {$fontPath}");
        }

        $image->text('Watermark', $image->width() / 2, $image->height() / 2, function ($font) use ($watermarkColor, $fontPath) {
            $font->file($fontPath); 
            $font->size(48);
            $font->color($watermarkColor);
            $font->align('center');
            $font->valign('middle');
        });

        return $image;
    }

    private function getMainColor(InterventionImage $image): string
    {
        $colors = [];
        for ($x = 0; $x < $image->getWidth(); $x += 10) {
            for ($y = 0; $y < $image->getHeight(); $y += 10) {
                $pixelColor = $image->pickColor($x, $y, 'hex');
                if (isset($colors[$pixelColor])) {
                    $colors[$pixelColor]++;
                } else {
                    $colors[$pixelColor] = 1;
                }
            }
        }
        arsort($colors);
        $mainColorHex = array_key_first($colors);

        $red = hexdec(substr($mainColorHex, 1, 2));
        $green = hexdec(substr($mainColorHex, 3, 2));
        $blue = hexdec(substr($mainColorHex, 5, 2));

        if ($red > $green && $red > $blue) {
            return 'red';
        } elseif ($blue > $red && $blue > $green) {
            return 'blue';
        } else {
            return 'green';
        }
    }



    private function getWatermarkColor(string $mainColor): string
    {
        switch ($mainColor) {
            case 'red':
                return '#000000'; // black
            case 'blue':
                return '#FFFF00'; // yellow
            case 'green':
                return '#FF0000'; // red
            default:
                return '#000000'; // black
        }
    }
}
