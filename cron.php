<?php
require __DIR__ . '/vendor/autoload.php';

use FFMpeg\FFMpeg;
use FFMpeg\Format\Video\X264;

$preuploadsDir = __DIR__.'/preuploads';
$uploadsDir = __DIR__.'/uploads/videos';

if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0777, true);
}

$videoExtensions = ['mp4', 'mov', 'avi', 'mkv'];

// Rutas binarias
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    $ffmpegPath = 'C:/ffmpeg/bin/ffmpeg.exe';
    $ffprobePath = 'C:/ffmpeg/bin/ffprobe.exe';
} else {
    $ffmpegPath = '/usr/bin/ffmpeg'; 
    $ffprobePath = '/usr/bin/ffprobe';
}

// Configuración básica
$ffmpeg = FFMpeg::create([
    'ffmpeg.binaries'  => $ffmpegPath,
    'ffprobe.binaries' => $ffprobePath,
    'timeout'          => 3600,
    'ffmpeg.threads'   => 4,
]);

$files = scandir($preuploadsDir);

foreach ($files as $file) {
    $filePath = $preuploadsDir . '/' . $file;
    if (!is_file($filePath)) continue;

    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    if (!in_array($ext, $videoExtensions)) continue;

    $cleanName = preg_replace('/[^A-Za-z0-9_\.-]/', '_', $file);
    $outputPath = $uploadsDir . '/' . $cleanName;

    echo "\nProcesando: $file\n";

    try {
        $video = $ffmpeg->open($filePath);
        $format = new X264('aac', 'libx264');
        
        $format->setAdditionalParameters([
            '-crf', '28',
            '-preset', 'fast',
            '-pix_fmt', 'yuv420p'
        ]);

        $video->save($format, $outputPath);
        echo "✅ OK: $cleanName\n";
        unlink($filePath);

    } catch (\Exception $e) {
        echo "❌ ERROR: " . $e->getMessage() . "\n";
        echo "💡 Intenta ejecutar esto manualmente para ver el error real:\n";
        echo "$ffmpegPath -i $filePath -vcodec libx264 -crf 28 -preset fast -pix_fmt yuv420p $outputPath 2>&1\n";
    }
}