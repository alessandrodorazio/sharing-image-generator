<?php

namespace App\Models;

use Intervention\Image\Facades\Image;

/**
 *
 */
class ImageGenerator extends Image {

	/**
	 * @var array
	 */
	//protected array $defaultImageOptions = ['bg_color' => '#1e2565', 'margin_x' => 200];
	const DEFAULT_IMAGE_OPTIONS = ['bg_color' => '#1e2565', 'margin_x' => 200];
	/**
	 * @var int|mixed
	 */
	protected int $imageWidthWithoutMargin = 0;
	/**
	 * @var \Intervention\Image\Image
	 */
	protected \Intervention\Image\Image $image;

	/**
	 * @param  int    $width
	 * @param  int    $height
	 * @param  array  $options
	 * @param  array  $items
	 */
	public function __construct(
		protected int   $width = 1200,
		protected int   $height = 675,
		protected array $options = [],
		protected array $items = []
	) {
		$this->options = array_merge(self::DEFAULT_IMAGE_OPTIONS, $this->options);
		$this->image = Image::canvas($this->width, $this->height, $this->options['bg_color']);
		$this->imageWidthWithoutMargin = $this->width > $this->options['margin_x'] ? $this->width - $this->options['margin_x'] : 0;
		array_map(fn($item) => $this->addItem($item), $items);
	}

	/**
	 * @param  array  $item
	 * @return void
	 */
	protected function addItem(array $item): void {
		match ($item['type']) {
			ItemsType::TEXT->value      => $this->addText($item),
			ItemsType::RECTANGLE->value => $this->addRectangle($item),
			ItemsType::IMAGE->value     => $this->addImage($item),
			default                     => null,
		};
	}

	/**
	 * @param  array  $item
	 * @return void
	 */
	protected function addText(array $item): void {
		match ($item['multiline'] ?? false) {
			true  => $this->addMultilineText($item),
			false => $this->addSinglelineText($item),
		};
	}

	/**
	 * @param  array  $item
	 * @return void
	 */
	protected function addMultilineText(array $item): void {
		$item = array_merge(ItemsType::MULTILINE_TEXT->defaultOptions(), $item);
		$lines = $this->splitTextInLines($item['text'], $item['per_line_chars']);
		$currentY = $item['y'];
		$lineHeight = $item['size'] + $item['space_between_lines'];
		foreach ($lines as $line) {
			$this->writeText(array_merge($item, ['text' => $line, 'y' => $currentY]));
			$currentY += $lineHeight;
		}
	}

	protected function splitTextInLines(string $text, int $perLineChars): array {
		return explode("\n", wordwrap($text, $perLineChars));
	}

	/**
	 * @param  array  $item
	 * @return void
	 */
	protected function addSinglelineText(array $item): void {
		$item = array_merge(ItemsType::TEXT->defaultOptions(), $item);
		$this->writeText($item);
	}

	protected function writeText(array $item = []) {
		$this->image->text($item['text'], $item['x'], $item['y'], function ($font) use ($item) {
			$font->file(base_path($item['font']));
			$font->size($item['size']);
			$font->color($item['color']);
			$font->valign($item['valign']);
			$font->align($item['align']);
		});
	}

	/**
	 * @param  array  $item
	 * @return void
	 */
	protected function addRectangle(array $item = []): void {
		$item = array_merge(ItemsType::RECTANGLE->defaultOptions(), $item);
		$this->image->rectangle($item['x1'], $item['y1'], $item['x2'], $item['y2'], function ($draw) use ($item) {
			$draw->background($item['bg_color']);
		});
	}

	/**
	 * @param  array  $item
	 * @return void
	 */
	protected function addImage(array $item = []): void {
		$item = array_merge(ItemsType::IMAGE->defaultOptions(), $item);
		$subImage = $this->createSubImage($item['base64'], $item['widen'] ?? null);
		$this->image->insert($subImage, $item['position'], $item['x'], $item['y']);
	}

	protected function createSubImage(string $base64 = '', $widen = null): \Intervention\Image\Image {
		$subImage = Image::make($base64);
		if ($widen) $subImage->widen($widen);
		return $subImage;
	}

	/**
	 * @param  string  $format
	 * @return \Intervention\Image\Image
	 */
	public function getImageEncoded(string $format = 'data-url'): \Intervention\Image\Image {
		return $this->image->encode($format);
	}
}