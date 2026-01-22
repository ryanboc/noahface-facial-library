<?php


namespace App\Services;

use Illuminate\Support\Facades\Http;

class NoahFaceService
{
    protected $url;

    public function __construct()
    {
        $this->url = config('services.noahface.url');
    }

    public function getUsers()
    {
        return Http::withBasicAuth(
            config('services.noahface.username'),
            config('services.noahface.password')
        )->get("{$this->url}/users")->json();
    }

    public function getUserByGuid($guid)
    {
        return Http::withBasicAuth(
            config('services.noahface.username'),
            config('services.noahface.password')
        )->get("{$this->url}/users", ['syncguid' => $guid])->json();
    }
}
