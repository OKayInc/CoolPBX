<?php

namespace App\Http\Controllers;

use App\Models\Voicemail;
use App\Models\VoicemailGreeting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VoicemailGreetingController extends Controller
{
    /**
     * Get the file path for a voicemail greeting
     */
    private function getGreetingFilePath(VoicemailGreeting $greeting)
    {
        // TODO: get directory from settings service
        // $voicemail_dir = "/var/lib/freeswitch/storage/voicemail";
        $voicemail_dir = storage_path("app/public/voicemail");

        $base_path = $voicemail_dir . '/' .
            'default' . '/' .
            $greeting->domain->domain_name . '/' .
            $greeting->voicemail->voicemail_id . '/' .
            $greeting->greeting_filename;

        return $base_path;
    }

    /**
     * Play a voicemail greeting
     */
    public function play($voicemailId, $greetingUuid)
    {
        $greeting = VoicemailGreeting::where('voicemail_greeting_uuid', $greetingUuid)
            ->where('voicemail_id', $voicemailId)
            ->where('domain_uuid', auth()->user()->domain_uuid)
            ->firstOrFail();

        // If base64 storage, decode and create temp file
        if (session('voicemail.storage_type.text') == 'base64' && $greeting->greeting_base64) {
            $decoded = base64_decode($greeting->greeting_base64);
            $tempPath = storage_path('app/temp/' . $greetingUuid . '.' . pathinfo($greeting->greeting_filename, PATHINFO_EXTENSION));
            
            if (!file_exists(dirname($tempPath))) {
                mkdir(dirname($tempPath), 0755, true);
            }
            
            file_put_contents($tempPath, $decoded);
            $filePath = $tempPath;
        } else {
            $filePath = $this->getGreetingFilePath($greeting);
        }

        if (!file_exists($filePath)) {
            abort(404, 'Greeting file not found');
        }

        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        $mimeTypes = [
            'wav' => 'audio/x-wav',
            'mp3' => 'audio/mpeg',
            'ogg' => 'audio/ogg',
        ];

        $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';

        $response = response()->file($filePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="greeting_' . $greeting->greeting_id . '.' . $extension . '"',
            'Cache-Control' => 'no-cache, must-revalidate',
            'Expires' => 'Sat, 26 Jul 1997 05:00:00 GMT',
            'Accept-Ranges' => 'bytes',
        ]);

        // Clean up temp file if base64
        if (isset($tempPath) && file_exists($tempPath)) {
            register_shutdown_function(function() use ($tempPath) {
                @unlink($tempPath);
            });
        }

        return $response;
    }

    /**
     * Download a voicemail greeting
     */
    public function download($voicemailId, $greetingUuid)
    {
        $greeting = VoicemailGreeting::where('voicemail_greeting_uuid', $greetingUuid)
            ->where('voicemail_id', $voicemailId)
            ->where('domain_uuid', auth()->user()->domain_uuid)
            ->firstOrFail();

        if (session('voicemail.storage_type.text') == 'base64' && $greeting->greeting_base64) {
            $decoded = base64_decode($greeting->greeting_base64);
            $tempPath = storage_path('app/temp/' . $greetingUuid . '.' . pathinfo($greeting->greeting_filename, PATHINFO_EXTENSION));
            
            if (!file_exists(dirname($tempPath))) {
                mkdir(dirname($tempPath), 0755, true);
            }
            
            file_put_contents($tempPath, $decoded);
            $filePath = $tempPath;
        } else {
            $filePath = $this->getGreetingFilePath($greeting);
        }

        if (!file_exists($filePath)) {
            abort(404, 'Greeting file not found');
        }

        $fileName = $greeting->greeting_filename;

        $response = response()->download($filePath, $fileName, [
            'Content-Type' => 'application/force-download',
            'Content-Description' => 'File Transfer',
            'Cache-Control' => 'no-cache, must-revalidate',
            'Expires' => 'Sat, 26 Jul 1997 05:00:00 GMT',
        ]);

        if (isset($tempPath) && file_exists($tempPath)) {
            register_shutdown_function(function() use ($tempPath) {
                @unlink($tempPath);
            });
        }

        return $response;
    }

    /**
     * Upload a new greeting
     */
    public function upload(Request $request, $voicemailId)
    {
        $request->validate([
            'file' => 'required|file|mimes:wav,mp3,ogg|max:10240', // 10MB max
        ]);

        $voicemail = Voicemail::where('voicemail_id', $voicemailId)
            ->where('domain_uuid', auth()->user()->domain_uuid)
            ->firstOrFail();

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();

        $existingGreetingIds = VoicemailGreeting::where('voicemail_id', $voicemailId)
            ->pluck('greeting_id')
            ->toArray();

        $greetingId = null;
        for ($i = 1; $i <= 9; $i++) {
            if (!in_array($i, $existingGreetingIds)) {
                $greetingId = $i;
                break;
            }
        }

        if ($greetingId === null) {
            return back()->with('error', 'Maximum number of greetings (9) reached');
        }

        $fileName = 'greeting_' . $greetingId . '.' . $extension;
        
        $greetingDir = storage_path("app/public/voicemail") . '/' .
            'default' . '/' .
            $voicemail->domain->domain_name . '/' .
            $voicemail->voicemail_id;

        if (!file_exists($greetingDir)) {
            mkdir($greetingDir, 0770, true);
        }

        $file->move($greetingDir, $fileName);

        $greeting = new VoicemailGreeting();
        $greeting->voicemail_greeting_uuid = Str::uuid();
        $greeting->domain_uuid = auth()->user()->domain_uuid;
        $greeting->voicemail_uuid = $voicemail->voicemail_uuid;
        $greeting->voicemail_id = $voicemail->voicemail_id;
        $greeting->greeting_id = $greetingId;
        $greeting->greeting_name = 'Greeting ' . $greetingId;
        $greeting->greeting_filename = $fileName;
        $greeting->greeting_description = $file->getClientOriginalName();

        if (session('voicemail.storage_type.text') == 'base64') {
            $greeting->greeting_base64 = base64_encode(file_get_contents($greetingDir . '/' . $fileName));
        }

        $greeting->save();

        $voicemail->greeting_id = $greetingId;
        $voicemail->save();

        return back()->with('message', 'Greeting uploaded successfully');
    }

    public function create(string $voicemailId)
    {
        return view('pages.voicemails.greetings-form', compact('voicemailId'));
    }

    /**
     * Set a greeting as active
     */
    public function setActive($voicemailId, $greetingUuid)
    {
        $greeting = VoicemailGreeting::where('voicemail_greeting_uuid', $greetingUuid)
            ->where('voicemail_id', $voicemailId)
            ->where('domain_uuid', auth()->user()->domain_uuid)
            ->firstOrFail();

        $voicemail = Voicemail::where('voicemail_id', $voicemailId)
            ->where('domain_uuid', auth()->user()->domain_uuid)
            ->firstOrFail();

        $voicemail->greeting_id = $greeting->greeting_id;
        $voicemail->save();

        return response()->json([
            'success' => true,
            'message' => 'Greeting set as active'
        ]);
    }
}