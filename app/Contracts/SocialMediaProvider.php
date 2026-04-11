<?php

namespace App\Contracts;

interface SocialMediaProvider
{
    public function getAuthUrl(): string;
    public function getAccessToken(string $code): array;
    public function getUserPages(string $userToken): array; // جلب الصفحات
    public function post(string $token, string $pageId, array $data): string; // النشر
}