<?php
namespace App\Contracts;

interface SocialMediaProvider
{
    public function getAuthUrl(): string;
    public function getAccessToken(string $code): array;
    public function post(string $token, string $pageId, array $data): string;
}