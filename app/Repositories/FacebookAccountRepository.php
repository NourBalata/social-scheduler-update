<?php
namespace App\Repositories;

use App\Models\FacebookAccount;

class FacebookAccountRepository extends BaseRepository
{
    public function __construct(FacebookAccount $model)
    {
        parent::__construct($model);
    }

    /**
     * تحديث أو إنشاء حساب فيسبوك وربطه باليوزر
     */
    public function updateOrCreateAccount(int $userId, array $data)
    {
        return $this->model->updateOrCreate(
            [
                'user_id' => $userId, 
                'facebook_id' => $data['facebook_id']
            ],
            $data
        );
    }
}