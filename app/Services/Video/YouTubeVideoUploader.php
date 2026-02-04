<?php

namespace App\Services\Video;

use App\Contracts\VideoUploader;
use App\Models\User;
use Google\Client;
use Google\Service\YouTube;
use Google\Service\YouTube\Video;
use Google\Service\YouTube\VideoSnippet;
use Google\Service\YouTube\VideoStatus;
use Illuminate\Http\UploadedFile;

class YouTubeVideoUploader implements VideoUploader
{
    public function __construct(private Client $client) {}

    public function upload(UploadedFile $file, User $user): string
    {
        $this->client->setAccessType('offline');
        $this->client->setScopes(['https://www.googleapis.com/auth/youtube.upload']);
        $this->client->fetchAccessTokenWithRefreshToken();

        $service = new YouTube($this->client);

        $snippet = new VideoSnippet;
        $snippet->setTitle($file->getClientOriginalName());
        $snippet->setDescription("Uploaded by {$user->name} via LoomCraft.");

        $status = new VideoStatus;
        $status->setPrivacyStatus('unlisted');

        $video = new Video;
        $video->setSnippet($snippet);
        $video->setStatus($status);

        $response = $service->videos->insert(
            'snippet,status',
            $video,
            [
                'data' => file_get_contents($file->getRealPath()),
                'mimeType' => $file->getMimeType(),
                'uploadType' => 'multipart',
            ],
        );

        $videoId = $response->getId();

        if (! $videoId) {
            throw new \RuntimeException('YouTube upload failed: missing video ID.');
        }

        return "https://www.youtube.com/watch?v={$videoId}";
    }
}
