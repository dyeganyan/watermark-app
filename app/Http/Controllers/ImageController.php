<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImageUploadRequest;
use App\Services\ImageProcessor;

class ImageController extends Controller
{
    protected $imageProcessor;

    public function __construct(ImageProcessor $imageProcessor)
    {
        $this->imageProcessor = $imageProcessor;
    }

    public function index()
    {
        return view('watermark');
    }

    public function watermark(ImageUploadRequest $request)
    {
        $image = $this->imageProcessor->processImage($request->file('image'));

        return $image->response('jpg');
    }
}