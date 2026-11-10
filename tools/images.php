<?php
// Draws the icons and share images for genes.one and the corporate example.
// Run from the repository root: php tools/images.php
// Needs PHP GD with FreeType and the fonts below (Windows); pass other TTF paths if needed:
// php tools/images.php /path/to/Bold.ttf /path/to/Regular.ttf
declare(strict_types=1);

$root = dirname(__DIR__);
$bold = $argv[1] ?? 'C:/Windows/Fonts/segoeuib.ttf';
$regular = $argv[2] ?? 'C:/Windows/Fonts/segoeui.ttf';

$brands = [
    'website' => [
        'bg' => '#101413', 'bg2' => '#18231f', 'mark' => '#d4ef72', 'on_mark' => '#101413',
        'text' => '#f5f3eb', 'muted' => '#aab6af', 'accent' => '#d4ef72',
        'letter' => 'g', 'name' => 'genes',
        'lines' => [['Give an AI one file.', 'text'], ['Get a real website.', 'accent']],
        'footer' => 'genes.md  ·  dependency-free PHP  ·  $5 shared hosting',
        'domain' => 'genes.one',
    ],
    'examples/corporate' => [
        'bg' => '#fbfaf7', 'bg2' => '#f1efe9', 'mark' => '#1d5c4b', 'on_mark' => '#ffffff',
        'text' => '#1b1d1c', 'muted' => '#595c58', 'accent' => '#1d5c4b',
        'letter' => 'A', 'name' => 'Arkka',
        'lines' => [['Buildings that last', 'text'], ['longer and use less.', 'accent']],
        'footer' => 'Structural & energy engineering  ·  Helsinki  ·  Istanbul',
        'domain' => 'A Genes example',
    ],
];

function color($image, string $hex) {
    [$r, $g, $b] = sscanf($hex, '#%02x%02x%02x');
    return imagecolorallocate($image, $r, $g, $b);
}

function rounded_rect($image, int $x, int $y, int $size, int $radius, $fill): void {
    imagefilledrectangle($image, $x + $radius, $y, $x + $size - $radius, $y + $size, $fill);
    imagefilledrectangle($image, $x, $y + $radius, $x + $size, $y + $size - $radius, $fill);
    foreach ([[$x + $radius, $y + $radius], [$x + $size - $radius, $y + $radius], [$x + $radius, $y + $size - $radius], [$x + $size - $radius, $y + $size - $radius]] as [$cx, $cy]) {
        imagefilledellipse($image, $cx, $cy, $radius * 2, $radius * 2, $fill);
    }
}

// Draws the letter optically centered inside a square of the given size.
function centered_letter($image, string $letter, string $font, int $x, int $y, int $size, $fill, float $scale): void {
    $points = $size * $scale;
    $box = imagettfbbox($points, 0, $font, $letter);
    $width = $box[2] - $box[0];
    $height = $box[1] - $box[7];
    $left = $x + ($size - $width) / 2 - $box[0];
    $base = $y + ($size - $height) / 2 - $box[7];
    imagettftext($image, $points, 0, (int) round($left), (int) round($base), $fill, $font, $letter);
}

function mark(array $brand, string $font, int $size) {
    $scale = 4;
    $big = imagecreatetruecolor($size * $scale, $size * $scale);
    imagefill($big, 0, 0, color($big, $brand['mark']));
    centered_letter($big, $brand['letter'], $font, 0, 0, $size * $scale, color($big, $brand['on_mark']), 0.62);
    $image = imagecreatetruecolor($size, $size);
    imagecopyresampled($image, $big, 0, 0, 0, 0, $size, $size, $size * $scale, $size * $scale);
    return $image;
}

function png_bytes($image): string {
    ob_start();
    imagepng($image, null, 9);
    return (string) ob_get_clean();
}

function write_ico(string $file, array $images): void {
    $count = count($images);
    $header = pack('vvv', 0, 1, $count);
    $entries = '';
    $data = '';
    $offset = 6 + 16 * $count;
    foreach ($images as $image) {
        $png = png_bytes($image);
        $size = imagesx($image);
        $entries .= pack('CCCCvvVV', $size >= 256 ? 0 : $size, $size >= 256 ? 0 : $size, 0, 0, 1, 32, strlen($png), $offset);
        $data .= $png;
        $offset += strlen($png);
    }
    file_put_contents($file, $header . $entries . $data);
}

function write_svg(string $file, array $brand): void {
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">'
        . '<rect width="64" height="64" rx="14" fill="' . $brand['mark'] . '"/>'
        . '<text x="32" y="' . ($brand['letter'] === 'g' ? 40 : 45) . '" text-anchor="middle" font-family="Segoe UI, system-ui, -apple-system, Roboto, sans-serif"'
        . ' font-size="44" font-weight="700" fill="' . $brand['on_mark'] . '">' . $brand['letter'] . '</text></svg>' . "\n";
    file_put_contents($file, $svg);
}

function share(array $brand, string $bold, string $regular) {
    $width = 1200;
    $height = 630;
    $image = imagecreatetruecolor($width, $height);
    [$r1, $g1, $b1] = sscanf($brand['bg'], '#%02x%02x%02x');
    [$r2, $g2, $b2] = sscanf($brand['bg2'], '#%02x%02x%02x');
    for ($x = 0; $x < $width; $x++) {
        $t = $x / ($width - 1);
        $line = imagecolorallocate($image, (int) ($r2 + ($r1 - $r2) * $t), (int) ($g2 + ($g1 - $g2) * $t), (int) ($b2 + ($b1 - $b2) * $t));
        imageline($image, $x, 0, $x, $height, $line);
    }
    $margin = 72;

    $logo = mark($brand, $bold, 64);
    imagecopy($image, $logo, $margin, $margin, 0, 0, 64, 64);
    imagettftext($image, 34, 0, $margin + 88, $margin + 46, color($image, $brand['text']), $bold, $brand['name']);

    $y = 300;
    foreach ($brand['lines'] as [$text, $tone]) {
        imagettftext($image, 64, 0, $margin - 4, $y, color($image, $brand[$tone]), $bold, $text);
        $y += 96;
    }

    imagefilledrectangle($image, $margin, $height - 132, $width - $margin, $height - 131, color($image, $brand['muted']));
    imagettftext($image, 24, 0, $margin, $height - 76, color($image, $brand['muted']), $regular, $brand['footer']);
    $box = imagettfbbox(24, 0, $bold, $brand['domain']);
    imagettftext($image, 24, 0, $width - $margin - ($box[2] - $box[0]), $height - 76, color($image, $brand['accent']), $bold, $brand['domain']);
    return $image;
}

foreach ($brands as $folder => $brand) {
    $base = $root . '/' . $folder;
    @mkdir($base . '/assets/icons', 0755, true);
    @mkdir($base . '/assets/media', 0755, true);
    write_svg($base . '/assets/icons/favicon.svg', $brand);
    write_ico($base . '/favicon.ico', [mark($brand, $bold, 32), mark($brand, $bold, 16)]);
    imagepng(mark($brand, $bold, 180), $base . '/assets/icons/apple-touch-icon.png', 9);
    imagepng(share($brand, $bold, $regular), $base . '/assets/media/share.png', 9);
    foreach (['favicon.ico', 'assets/icons/favicon.svg', 'assets/icons/apple-touch-icon.png', 'assets/media/share.png'] as $file) {
        echo $folder . '/' . $file . ' ' . round(filesize($base . '/' . $file) / 1024, 1) . " KB\n";
    }
}
