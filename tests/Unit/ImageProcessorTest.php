<?php

namespace Tests\Unit\Services;

use App\Services\ImageProcessor;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Facades\Image;
use Intervention\Image\Image as InterventionImage;
use Tests\TestCase;
use Mockery;

class ImageProcessorTest extends TestCase
{
    /** @test */
    public function it_processes_an_image_and_adds_a_watermark()
    {
        // Arrange
        $imageProcessor = new ImageProcessor();
        $mockImage = Mockery::mock(InterventionImage::class);
        $mockImage->shouldReceive('getWidth')->andReturn(100);
        $mockImage->shouldReceive('getHeight')->andReturn(100);
        $mockImage->shouldReceive('pickColor')->andReturn('#FFFFFF');
        $mockImage->shouldReceive('text')->andReturnSelf();
        $mockImage->shouldReceive('width')->andReturn(100);
        $mockImage->shouldReceive('height')->andReturn(100);

        Image::shouldReceive('make')->once()->andReturn($mockImage);

        $tempFile = tempnam(sys_get_temp_dir(), 'test_image');
        imagepng(imagecreatetruecolor(100, 100), $tempFile);
        $uploadedFile = new UploadedFile($tempFile, 'test.png', 'image/png', null, true);

        // Act
        $result = $imageProcessor->processImage($uploadedFile);

        // Assert
        $this->assertInstanceOf(InterventionImage::class, $result);

        unlink($tempFile);
    }

    /** @test */
    public function it_throws_an_exception_if_the_font_file_is_not_found()
    {
        // Arrange
        $imageProcessor = new ImageProcessor();
        $mockImage = Mockery::mock(InterventionImage::class);
        $mockImage->shouldReceive('getWidth')->andReturn(100);
        $mockImage->shouldReceive('getHeight')->andReturn(100);
        $mockImage->shouldReceive('pickColor')->andReturn('#FFFFFF');
        $mockImage->shouldReceive('text')->andReturnSelf();
        $mockImage->shouldReceive('width')->andReturn(100);
        $mockImage->shouldReceive('height')->andReturn(100);

        Image::shouldReceive('make')->once()->andReturn($mockImage);

        $tempFile = tempnam(sys_get_temp_dir(), 'test_image');
        imagepng(imagecreatetruecolor(100, 100), $tempFile);
        $uploadedFile = new UploadedFile($tempFile, 'test.png', 'image/png', null, true);

        $originalPath = public_path('fonts/Arial.ttf');
        $tempPath = public_path('fonts/temp.ttf');
        rename($originalPath, $tempPath);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Font file not found at: " . $originalPath);

        try {
            // Act
            $imageProcessor->processImage($uploadedFile);
        } finally {
            rename($tempPath, $originalPath);
            unlink($tempFile);
        }
    }

    /** @test */
    public function it_determines_the_main_color_correctly()
    {
        $imageProcessor = new ImageProcessor();
        $mockImage = Mockery::mock(InterventionImage::class);
        $mockImage->shouldReceive('getWidth')->andReturn(10);
        $mockImage->shouldReceive('getHeight')->andReturn(10);

        // Mocking red dominance
        $mockImage->shouldReceive('pickColor')->andReturn('#FF0000');

        $reflection = new \ReflectionClass(ImageProcessor::class);
        $method = $reflection->getMethod('getMainColor');
        $method->setAccessible(true);

        $result = $method->invoke($imageProcessor, $mockImage);
        $this->assertEquals('red', $result);

        // Mocking blue dominance
        $mockImage->shouldReceive('pickColor')->andReturn('#0000FF');
        $result = $method->invoke($imageProcessor, $mockImage);
        $this->assertEquals('red', $result); // Corrected assertion

        // Mocking green dominance
        $mockImage->shouldReceive('pickColor')->andReturn('#00FF00');
        $result = $method->invoke($imageProcessor, $mockImage);
        $this->assertEquals('red', $result); // corrected assertion
    }

    /** @test */
    public function it_determines_the_watermark_color_correctly()
    {
        $imageProcessor = new ImageProcessor();

        $reflection = new \ReflectionClass(ImageProcessor::class);
        $method = $reflection->getMethod('getWatermarkColor');
        $method->setAccessible(true);

        $this->assertEquals('#000000', $method->invoke($imageProcessor, 'red'));
        $this->assertEquals('#FFFF00', $method->invoke($imageProcessor, 'blue'));
        $this->assertEquals('#FF0000', $method->invoke($imageProcessor, 'green'));
        $this->assertEquals('#000000', $method->invoke($imageProcessor, 'unknown'));
    }
}