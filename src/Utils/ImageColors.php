<?php

namespace Crwlr\CrawlerExtBrowser\Utils;

use Crwlr\CrawlerExtBrowser\Exceptions\UnknownImageFileTypeException;
use Exception;
use GdImage;

class ImageColors
{
    private int $width = 0;

    private int $height = 0;

    public function __construct(private readonly string $imagePath) {}

    /**
     * @return array<int, array{ red: int, green: int, blue: int, rgb: string, percentage: float }>
     * @throws UnknownImageFileTypeException|Exception
     */
    public static function getFrom(string $imagePath): array
    {
        return (new self($imagePath))->getColors();
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

        foreach ($allColors as $colorData) {
            $percentageOfImage = round(($colorData['count'] / $totalPixels) * 100, 1);

            if ($percentageOfImage >= 0.5) {
                $colors[] = [
                    'red' => $colorData['red'],
                    'green' => $colorData['green'],
                    'blue' => $colorData['blue'],
                    'rgb' => $colorData['rgb'],
                    'percentage' => $percentageOfImage,
                ];
            }
        }

        return $colors;
    }

    /**
     * @return array<string, array{ red: int, green: int, blue: int, rgb: string, count: int }>
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

                $rgbString = '(' . $red . ',' . $green . ',' . $blue . ')';

                if (isset($colors[$rgbString])) {
                    $colors[$rgbString]['count'] += 1;
                } else {
                    $colors[$rgbString] = [
                        'red' => $red,
                        'green' => $green,
                        'blue' => $blue,
                        'rgb' => $rgbString,
                        'count' => 1,
                    ];
                }
            }
        }

        return $this->sortColorsByCount($colors);
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

    /**
     * @param array<string, array{ red: int, green: int, blue: int, rgb: string, count: int }> $colors
     * @return array<string, array{ red: int, green: int, blue: int, rgb: string, count: int }>
     */
    private function sortColorsByCount(array $colors): array
    {
        uasort($colors, function ($a, $b) {
            if ($a['count'] > $b['count']) {
                return -1;
            } elseif ($a['count'] === $b['count']) {
                return 0;
            }

            return 1;
        });

        return $colors;
    }
}
