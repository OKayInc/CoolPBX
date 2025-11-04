<?php

namespace App\Http\Controllers;

use App\Models\VoicemailMessage;
use Illuminate\Http\Request;

class VoicemailMessageController extends Controller
{
    private function getVoicemailFilePath(VoicemailMessage $message)
    {
        // TODO: get directory from settings service
        // $voicemail_dir = "/var/lib/freeswitch/storage/voicemail";
        $voicemail_dir = storage_path("app/public/voicemail");

        $base_path = $voicemail_dir . '/' .
            'default' . '/' .
            $message->domain->domain_name . '/' .
            $message->voicemail->voicemail_id . '/' .
            'msg_' . $message->voicemail_message_uuid;

        if (file_exists($base_path . '.wav')) {
            return $base_path . '.wav';
        }

        if (file_exists($base_path . '.mp3')) {
            return $base_path . '.mp3';
        }

        return $base_path;
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

        dd('no hay');

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
