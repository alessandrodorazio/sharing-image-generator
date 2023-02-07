<?php

namespace App\Models;

enum ItemsType: string {
	case TEXT           = 'text';
	case MULTILINE_TEXT = 'multiline_text';
	case RECTANGLE      = 'rectangle';
	case IMAGE          = 'image';


	function defaultOptions(): array {
		return match ($this) {
			self::TEXT           => [
				'text'  => '', 'x' => 0, 'y' => 0, 'font' => '/public/Lato-Bold.ttf', 'size' => 12,
				'color' => '#000000', 'align' => 'center', 'valign' => 'top',
			],
			self::MULTILINE_TEXT => array_merge(self::TEXT->defaultOptions(), [
				'space_between_lines' => 15, 'per_line_chars' => 30,
			]),
			self::RECTANGLE      => ['x1' => 0, 'y1' => 0, 'x2' => 10, 'y2' => 10, 'bg_color' => '#000000'],
			self::IMAGE          => ['base64' => '', 'position' => 'top-right', 'x' => 0, 'y' => 0],
		};
	}
}