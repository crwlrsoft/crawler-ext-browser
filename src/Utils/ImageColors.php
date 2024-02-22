<?php

namespace Crwlr\CrawlerExtBrowser\Utils;

use Crwlr\CrawlerExtBrowser\Exceptions\UnknownImageFileTypeException;
use Exception;
use GdImage;

class ImageColors
{
    private int $width = 0;

    private int $height = 0;

    public function __construct(
        private readonly string $imagePath,
        private readonly ?float $onlyAbovePercentageOfImage = null,
    ) {}

    /**
     * @return array<int, array{ red: int, green: int, blue: int, rgb: string, percentage: float }>
     * @throws UnknownImageFileTypeException|Exception
     */
    public static function getFrom(string $imagePath, ?float $onlyAbovePercentageOfImage = null): array
    {
        return (new self($imagePath, $onlyAbovePercentageOfImage))->getColors();
    }

    /**
     * @return array<int, array{ red: int, green: int, blue: int, rgb: string, percentage: float }>
     * @throws UnknownImageFileTypeException|Exception
     */
    public function getColors(): array
    {
        $allColors = $this->getAllColors();

        $totalPixels = $this->width * $this->height;

        $colors = [];

        foreach ($allColors as $rgb => $pixelCount) {
            [$red, $green, $blue] = explode(',', $rgb);

            $percentageOfImage = round(($pixelCount / $totalPixels) * 100, 1);

            if ($this->onlyAbovePercentageOfImage === null || $percentageOfImage >= $this->onlyAbovePercentageOfImage) {
                $colors[] = [
                    'red' => (int) $red,
                    'green' => (int) $green,
                    'blue' => (int) $blue,
                    'rgb' => '(' . $rgb . ')',
                    'percentage' => $percentageOfImage,
                ];
            }
        }

        return $colors;
    }

    /**
     * @return array<string, int>
     * @throws UnknownImageFileTypeException|Exception
     */
    protected function getAllColors(): array
    {
        $image = $this->getImage();

        $this->width = imagesx($image);

        $this->height = imagesy($image);

        $colors = [];

        for ($pixelH = 0; $pixelH < $this->height; $pixelH++) {
            for ($pixelW = 0; $pixelW < $this->width; $pixelW++) {
                $rgb = imagecolorat($image, $pixelW, $pixelH);

                $red = ($rgb >> 16) & 0xFF;

                $green = ($rgb >> 8) & 0xFF;

                $blue = $rgb & 0xFF;

                $rgbString = $red . ',' . $green . ',' . $blue;

                if (isset($colors[$rgbString])) {
                    $colors[$rgbString] += 1;
                } else {
                    $colors[$rgbString] = 1;
                }
            }
        }

        arsort($colors);

        return $colors;
    }

    /**
     * @throws UnknownImageFileTypeException|Exception
     */
    protected function getImage(): GdImage
    {
        $fileType = ImageFileType::fromFilePath($this->imagePath);

        if (!$fileType) {
            throw new UnknownImageFileTypeException('Can\'t guess image file type from file ending.');
        }

        $image = false;

        if ($fileType === ImageFileType::Png) {
            $image = imagecreatefrompng($this->imagePath);
        } elseif ($fileType === ImageFileType::Jpeg) {
            $image = imagecreatefromjpeg($this->imagePath);
        } elseif ($fileType === ImageFileType::Gif) {
            $image = imagecreatefromgif($this->imagePath);
        } elseif ($fileType === ImageFileType::Webp) {
            $image = imagecreatefromwebp($this->imagePath);
        }

        if ($image !== false) {
            return $image;
        }

        throw new Exception('Can\'t read image file');
    }
}
