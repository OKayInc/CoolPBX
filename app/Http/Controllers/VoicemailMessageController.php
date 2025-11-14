<?php

namespace App\Http\Controllers;

use App\Models\VoicemailMessage;
use Illuminate\Http\Request;

class VoicemailMessageController extends Controller
{
    private function getVoicemailFilePath(VoicemailMessage $message): string
    {
        $storageType = session('voicemail.storage_type.text');

        if ($storageType === 'base64') {
            return $this->prepareBase64File($message);
        }

        return $this->getFileSystemPath($message);
    }

    private function getFileSystemPath(VoicemailMessage $message): string
    {
        $voicemailDir = config('services.freeswitch.voicemail_dir', '/var/lib/freeswitch/storage/voicemail');

        $basePath = sprintf(
            '%s/default/%s/%s/msg_%s',
            $voicemailDir,
            $message->domain->domain_name,
            $message->voicemail->voicemail_id,
            $message->voicemail_message_uuid
        );


        $extensions = ['wav', 'mp3', 'ogg'];

        foreach ($extensions as $ext) {
            $filePath = $basePath . '.' . $ext;
            if (file_exists($filePath)) {
                return $filePath;
            }
        }

        throw new \Exception("Voicemail file not found at: {$basePath}.[wav|mp3|ogg]");
    }

    private function prepareBase64File(VoicemailMessage $message): string
    {
        if (empty($message->message_base64)) {
            throw new \Exception('No base64 content found for voicemail message: ' . $message->voicemail_message_uuid);
        }

        $decodedContent = base64_decode($message->message_base64);

        if ($decodedContent === false) {
            throw new \Exception('Failed to decode base64 content');
        }

        $tempDir = storage_path('app/temp/voicemail');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $tempFile = $tempDir . '/msg_' . $message->voicemail_message_uuid . '.tmp';
        file_put_contents($tempFile, $decodedContent);

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $tempFile);
        finfo_close($finfo);

        $extension = match ($mimeType) {
            'audio/x-wav', 'audio/wav' => 'wav',
            'audio/mpeg', 'audio/mp3' => 'mp3',
            'audio/ogg' => 'ogg',
            default => 'wav'
        };

        $finalPath = $tempDir . '/msg_' . $message->voicemail_message_uuid . '.' . $extension;
        rename($tempFile, $finalPath);

        register_shutdown_function(function () use ($finalPath) {
            if (file_exists($finalPath)) {
                @unlink($finalPath);
            }
        });

        return $finalPath;
    }


    public function play($voicemailMessageUuid)
    {

        $message = VoicemailMessage::where('voicemail_message_uuid', $voicemailMessageUuid)
            ->where('domain_uuid', auth()->user()->domain_uuid)
            ->firstOrFail();

        $filePath = $this->getVoicemailFilePath($message);


        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        $mimeTypes = [
            'wav' => 'audio/x-wav',
            'mp3' => 'audio/mpeg',
            'ogg' => 'audio/ogg',
        ];

        $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';

        return response()->file($filePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="voicemail_' . $message->voicemail_message_uuid . '.' . $extension . '"',
            'Cache-Control' => 'no-cache, must-revalidate',
            'Expires' => 'Sat, 26 Jul 1997 05:00:00 GMT',
            'Accept-Ranges' => 'bytes',
        ]);
    }

    public function download($voicemailMessageUuid)
    {
        $message = VoicemailMessage::where('voicemail_message_uuid', $voicemailMessageUuid)
            ->where('domain_uuid', auth()->user()->domain_uuid)
            ->firstOrFail();

        $filePath = $this->getVoicemailFilePath($message);

        if (!file_exists($filePath)) {
            abort(404, 'Voicemail file not found');
        }

        $fileName = 'voicemail_' .
            $message->caller_id_number . '_' .
            date('Y-m-d_H-i-s', $message->created_epoch) .
            '.' . pathinfo($filePath, PATHINFO_EXTENSION);

        return $this->rangeDownload($filePath, $fileName);
    }


    public function markAsRead($voicemailMessageUuid)
    {

        $message = VoicemailMessage::where('voicemail_message_uuid', $voicemailMessageUuid)
            ->where('domain_uuid', auth()->user()->domain_uuid)
            ->firstOrFail();

        $message->update(['message_status' => 'saved']);

        return response()->json(['success' => true]);
    }

    private function rangeDownload($filePath, $fileName)
    {
        $fp = @fopen($filePath, 'rb');

        if (!$fp) {
            abort(500, 'Cannot open file');
        }

        $size = filesize($filePath);
        $length = $size;
        $start = 0;
        $end = $size - 1;

        header("Accept-Ranges: 0-$length");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Description: File Transfer");
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

        if (isset($_SERVER['HTTP_RANGE'])) {
            $c_start = $start;
            $c_end = $end;

            list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);

            if (strpos($range, ',') !== false) {
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                header("Content-Range: bytes $start-$end/$size");
                exit;
            }

            if ($range == '-') {
                $c_start = $size - substr($range, 1);
            } else {
                $range = explode('-', $range);
                $c_start = $range[0];
                $c_end = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $size;
            }

            $c_end = ($c_end > $end) ? $end : $c_end;

            if ($c_start > $c_end || $c_start > $size - 1 || $c_end >= $size) {
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                header("Content-Range: bytes $start-$end/$size");
                exit;
            }

            $start = $c_start;
            $end = $c_end;
            $length = $end - $start + 1;
            fseek($fp, $start);
            header('HTTP/1.1 206 Partial Content');
        }

        header("Content-Range: bytes $start-$end/$size");
        header("Content-Length: $length");

        $buffer = 1024 * 8;
        while (!feof($fp) && ($p = ftell($fp)) <= $end) {
            if ($p + $buffer > $end) {
                $buffer = $end - $p + 1;
            }
            set_time_limit(0);
            echo fread($fp, $buffer);
            flush();
        }

        fclose($fp);
        exit;
    }
}
