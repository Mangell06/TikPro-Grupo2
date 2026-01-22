<?php
require __DIR__ . '/vendor/autoload.php';

use FFMpeg\FFMpeg;
use FFMpeg\Format\Video\X264;

// Directorios
$preuploadsDir = __DIR__.'/preuploads';
$uploadsDir = __DIR__.'/uploads/videos';

if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0777, true);
}

// Extensiones válidas
$videoExtensions = ['mp4', 'mov', 'avi', 'mkv'];

// Detectar sistema operativo
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    // WINDOWS
    $ffmpegPath = 'C:/ffmpeg/bin/ffmpeg.exe';
    $ffprobePath = 'C:/ffmpeg/bin/ffprobe.exe';
} else {
    // UBUNTU / LINUX
    $ffmpegPath = '/usr/bin/ffmpeg';
    $ffprobePath = '/usr/bin/ffprobe';
}

$ffmpeg = FFMpeg::create([
    'ffmpeg.binaries'  => $ffmpegPath,
    'ffprobe.binaries' => $ffprobePath,
    'timeout' => 3600,
    'ffmpeg.threads' => 4,
]);

$files = scandir($preuploadsDir);

foreach ($files as $file) {

    $filePath = $preuploadsDir . '/' . $file;
    if (!is_file($filePath)) continue;

    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    if (!in_array($ext, $videoExtensions)) continue;

    $cleanName = preg_replace('/[^A-Za-z0-9_\.-]/', '_', $file);
    $outputPath = $uploadsDir . '/' . $cleanName;

    echo "Procesando: $file\n";

    try {
        $video = $ffmpeg->open($filePath);

        $format = new X264('aac', 'libx264');
        $format->setAdditionalParameters([
            '-crf', '28',
            '-preset', 'fast'
        ]);

        $video->save($format, $outputPath);

        echo "Comprimido: $cleanName\n";

        // BORRAR ARCHIVO ORIGINAL SOLO SI TODO FUE BIEN
        unlink($filePath);
        echo "Eliminado de preuploads: $file\n";

    } catch (\Exception $e) {
        echo "Error comprimiendo $file: " . $e->getMessage() . "\n";
    }
}

echo "Todos los videos procesados.\n";
