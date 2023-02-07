<?php

namespace App\Http\Controllers;

use App\Models\ImageGenerator;

class GenerateImageController extends Controller {
	public function __invoke() {
		$options = request()->post('options', []);
		$items = request()->post('items', []);
		$imageGenerator = new ImageGenerator(options: $options, items: $items);
		return response($imageGenerator->getImageEncoded())
			->header('Content-Type', 'image/png');
	}

}