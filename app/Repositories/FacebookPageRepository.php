<?php
namespace App\Repositories;

use App\Models\FacebookPage;

class FacebookPageRepository extends BaseRepository
{
    public function __construct(FacebookPage $model)
    {
        parent::__construct($model);
    }

    // تحديث أو إنشاء صفحة مع ربطها بالحساب الشخصي
    public function syncPage(int $userId, int $accountId, array $fbPageData)
    {
        return $this->model->updateOrCreate(
            [
                'user_id' => $userId,
                'page_id' => $fbPageData['id'],
            ],
            [
                'facebook_account_id' => $accountId,
                'page_name' => $fbPageData['name'],
                'access_token' => $fbPageData['access_token'],
                // توكن الصفحة عادةً بكون ممتد المفعول
                'token_expires_at' => now()->addMonths(2), 
                'is_active' => true,
            ]
        );
    }
}