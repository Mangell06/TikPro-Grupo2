<?php
require __DIR__ . '/vendor/autoload.php';

use FFMpeg\FFMpeg;
use FFMpeg\Format\Video\X264;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// 1. Configurar un Logger para ver el error REAL de FFmpeg
$logger = new Logger('ffmpeg_debug');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));

$preuploadsDir = __DIR__.'/preuploads';
$uploadsDir = __DIR__.'/uploads/videos';

if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0777, true);
}

$videoExtensions = ['mp4', 'mov', 'avi', 'mkv'];

// Rutas dinámicas
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    $ffmpegPath = 'C:/ffmpeg/bin/ffmpeg.exe';
    $ffprobePath = 'C:/ffmpeg/bin/ffprobe.exe';
} else {
    // IMPORTANTE: Verifica estas rutas con 'which ffmpeg' y 'which ffprobe'
    $ffmpegPath = '/usr/bin/ffmpeg'; 
    $ffprobePath = '/usr/bin/ffprobe';
}

// 2. Pasar el logger a la creación para capturar errores internos
$ffmpeg = FFMpeg::create([
    'ffmpeg.binaries'  => $ffmpegPath,
    'ffprobe.binaries' => $ffprobePath,
    'timeout'          => 3600,
    'ffmpeg.threads'   => 4,
], $logger);

$files = scandir($preuploadsDir);

foreach ($files as $file) {
    $filePath = $preuploadsDir . '/' . $file;
    if (!is_file($filePath)) continue;

    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    if (!in_array($ext, $videoExtensions)) continue;

    $cleanName = preg_replace('/[^A-Za-z0-9_\.-]/', '_', $file);
    $outputPath = $uploadsDir . '/' . $cleanName;

    echo "\n--- Procesando: $file ---\n";

    try {
        // Verificar si el archivo es legible
        if (!is_readable($filePath)) {
            throw new \Exception("El archivo original no tiene permisos de lectura.");
        }

        $video = $ffmpeg->open($filePath);

        $format = new X264('aac', 'libx264');
        
        // Ajuste de parámetros para máxima compatibilidad
        $format->setAdditionalParameters([
            '-crf', '28',
            '-preset', 'fast',
            '-pix_fmt', 'yuv420p' // Añadido para asegurar que el video se vea en navegadores
        ]);

        $video->save($format, $outputPath);

        echo "✅ Comprimido: $cleanName\n";

        if (file_exists($outputPath)) {
            unlink($filePath);
            echo "🗑️ Eliminado de preuploads: $file\n";
        }

    } catch (\Exception $e) {
        echo "❌ ERROR en $file: " . $e->getMessage() . "\n";
        // Si el error persiste, esta línea imprimirá el rastro completo
        // echo $e->getTraceAsString(); 
    }
}

echo "\nProceso finalizado.\n";