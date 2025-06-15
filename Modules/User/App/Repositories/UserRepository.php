<?php

namespace Modules\User\App\Repositories;

use App\Traits\HandlesUploadsTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Modules\User\App\Models\User;
use Modules\User\App\Models\UserFile;
use Prettus\Repository\Eloquent\BaseRepository;

class UserRepository extends BaseRepository
{
    use HandlesUploadsTrait;
    public function model()
    {
        return User::class;
    }
    /*****************************************Retrieving For Admins **************************************/
    public function getAll()
    {
        $this->makeDefaultSortByColumn();

        //scout full text search
        return $this->model
            ->applyCommonRelations()
            ->filter(request()->all())
            ->withoutBanned();


    }
    public function getOneByUserId(int $id)
    {
        return $this->model
            ->applyCommonRelations()
            ->where('id', $id)->first();
    }
    public function findBySlug(string $slug)
    {
        return $this->model
            ->where('slug', $slug)
            ->applyCommonRelations()
            ->first();
    }
    /*****************************************End Retrieving For Admins **************************************/
    /*****************************************Retrieving For Users **************************************/
    public function getAllActive()
    {
        $this->makeDefaultSortByColumn();
        $currentUserId = Auth::guard('user-api')->id();

        return $this->model
            ->where('id', '!=', $currentUserId)
            ->applyCommonRelations()
            ->filter(request()->all());



    }
    public function getRecommended()
    {
        //full text-index
        $currentUserId = Auth::guard('user-api')->id();
        return $this->model
            ->active()
            ->where('id', '!=', $currentUserId)
            ->applyCommonRelations()
            ->inRandomOrder()
            ->take(3);

    }
    public function findActiveBySlug(string $slug)
    {

        return $this->model
            ->where('slug', $slug)
            ->active()
            ->applyCommonRelations()
            ->first();
    }
    public function getActiveOneByUserId(int $id)
    {
        return $this->model
            ->active()
            ->applyCommonRelations()
            ->where('id', $id)->first();
    }
    /*****************************************End Retrieving For Users **************************************/







    public function createOne(array $userData)
    {

        try {
            DB::beginTransaction();

            $userData['image'] = $this->handleFileUpload('image', User::FILES_DIRECTORY);
            $userData['resume'] = $this->handleFileUpload('resume', User::FILES_DIRECTORY);
            $created = $this->create($userData);
            if (!empty($userData['phone']) && !empty($userData['phone_code'])) {
                $created->phone()->create([
                    'phone' => $userData['phone'],
                    'phone_code' => $userData['phone_code'],
                ]);
            }
            DB::commit();
            return $created;
        } catch (\Throwable $th) {

            DB::rollBack();
            return false;
        }
    }
    public function createOneByAdmin(array $userData, array $userProfileData)
    {

        try {
            DB::beginTransaction();
            $userData['image'] = $this->handleFileUpload('image', User::FILES_DIRECTORY);
            $userData['resume'] = $this->handleFileUpload('resume', User::FILES_DIRECTORY);
            $user = $this->create($userData);
            $user->profile()->create($userProfileData);

            if (!empty($userData['phone']) && !empty($userData['phone_code'])) {
                $user->phone()->create([
                    'phone' => $userData['phone'],
                    'phone_code' => $userData['phone_code'],
                ]);
            }
            if (!empty(request('interestIds'))) {
                $user->interests()->attach(request('interestIds'));
            }
            if (!empty(request('spokenLanguagesIds'))) {
                $user->spokenLanguages()->attach(request('spokenLanguagesIds'));
            }
            DB::commit();
            return $user;
        } catch (\Throwable $th) {
            dd(vars: $th->getMessage());

            DB::rollBack();
            return false;
        }
    }
    public function createQuietly(array $userData)
    {

        try {
            DB::beginTransaction();
            $userData['image'] = $this->handleFileUpload('image', User::FILES_DIRECTORY);
            $userData['resume'] = $this->handleFileUpload('resume', User::FILES_DIRECTORY);
            $user = $this->model->create($userData);
            $user->profile()->create();
            if (!empty($userData['phone']) && !empty($userData['phone_code'])) {
                $user->phone()->create([
                    'phone' => $userData['phone'],
                    'phone_code' => $userData['phone_code'],
                ]);
            }
            DB::commit();
            return $user;
        } catch (\Throwable $th) {
            dd(vars: $th->getMessage());

            DB::rollBack();
            return false;
        }
    }
    public function updateOne(array $userData, array $userProfileData, int $id)
    {
        try {
            DB::beginTransaction();
            $user = $this->model->findOrFail($id);
            // Handle country_id and city_id
            $userData['country_id'] = $userData['country_id'] ?? null;
            $userData['city_id'] = $userData['city_id'] ?? null;
            $userData['image'] = $this->handleFileUpload('image', User::FILES_DIRECTORY,$user->image);
            $userData['resume'] = $this->handleFileUpload('resume', User::FILES_DIRECTORY,$user->resume);
            $updated = $user->update($userData);
            // Handle phone and phone_code logic
            if (empty($userData['phone']) || empty($userData['phone_code'])) {
                if ($user->phone) {
                    $user->phone()->delete(); // Remove existing phone record if unset
                }
            } else {
                $existingPhone = $user->phone;
                if ($existingPhone) {
                    // Update the existing phone record
                    $existingPhone->update([
                        'phone' => $userData['phone'],
                        'phone_code' => $userData['phone_code'],
                    ]);
                } else {
                    // Create a new phone record
                    $user->phone()->create([
                        'phone' => $userData['phone'],
                        'phone_code' => $userData['phone_code'],
                    ]);
                }
            }
            if (!$updated) {
                throw new \Exception("User update failed.");
            }
            if (!empty(request('interestIds'))) {
                $user->interests()->sync(request('interestIds'));
            }
            if (!empty(request('spokenLanguagesIds'))) {
                $user->spokenLanguages()->sync(request('spokenLanguagesIds'));
            }
            $user->profile()->update($userProfileData);


            DB::commit();

            return $user->load([
                'profile',
                'phone',
                'educationalLevel',
                'professionalSpecialization',
                'interests',
                'spokenLanguages',
                'userFiles',
                'nationality' => function ($query) {
                    $query->select('id', 'code')->withTranslation();
                },
                'country' => function ($query) {
                    $query->select('id', 'code')->withTranslation();
                },
                'city' => function ($query) {
                    $query->select('cities.id', 'cities.state_id')->withTranslation();
                },
                'state' => function ($query) {
                    $query->select('states.id')->withTranslation();
                },
            ]);
        } catch (\Throwable $th) {
            dd(vars: $th->getMessage());
            DB::rollBack();
            return false;
        }
    }

    //delete by admin
    public function deleteOne(int $id)
    {
        try {
            DB::beginTransaction();
            $user = $this->model->findOrFail($id);
            $user->status = User::STATUS_INACTIVE;
            $deleted = $user->save();
            DB::commit();
            return $deleted;
        } catch (\Throwable $th) {

            DB::rollBack();
            return false;
        }
    }
    public function deletePermanently(int $id)
    {
        try {
            DB::beginTransaction();

            $user = $this->model->findOrFail($id);
            if ($user->image) {
                $this->deleteFile($user->image);
            }
            $deleted = $user->delete();
            DB::commit();
            return $deleted;
        } catch (\Throwable $th) {
            dd($th->getMessage());
            DB::rollBack();
            return false;
        }
    }

    //delete by user
    public function changeAccountActivity(int $id)
    {
        try {
            DB::beginTransaction();

            $user = $this->model->findOrFail($id);
            $user->active = $user->active ? User::INACTIVE : User::ACTIVE;
            $changed = $user->save();
            DB::commit();
            return $changed;
        } catch (\Throwable $th) {
            dd($th->getMessage());
            DB::rollBack();
            return false;
        }
    }

    public function verify($userId)
    {
        try {
            DB::beginTransaction();


            $this->model->where('id', $userId)->update([
                'verified_at' => Carbon::now()
            ]);
            DB::commit();
            return true;
        } catch (\Throwable $th) {

            DB::rollBack();
            return false;
        }
    }

    /*****************************************User Settings **************************************/

    public function updateUserProfileImage(int $id)
    {
        try {
            DB::beginTransaction();

            $user = $this->model->findOrFail($id);
            $userData['image'] = $this->handleFileUpload('image', User::FILES_DIRECTORY,$user->image);

            $user->update($userData);

            DB::commit();

            return $user->refresh();
        } catch (\Throwable $th) {

            DB::rollBack();
            return false;
        }
    }
    public function setFcmToken(string $token)
    {
        try {
            DB::beginTransaction();
            $updated = app(UserFcmTokenRepository::class)->updateOrCreate(
                ['token' => $token],
                [
                    'device_id' => null,
                    'user_id' => Auth::id(),
                ]
            );
            DB::commit();
            return $updated;
        } catch (\Throwable $th) {
            DB::rollBack();
            return false;
        }
    }
    public function deleteUserProfileImage(int $id)
    {
        try {
            DB::beginTransaction();

            $user = $this->model->findOrFail($id);
            if ($user->image) {
                $this->deleteFile($user->image);
            }
            $userData['image'] = null;
            $user->update($userData);
            DB::commit();

            return $user->refresh();
        } catch (\Throwable $th) {

            DB::rollBack();
            return false;
        }
    }
    
    public function getFileType($file)
    {
        if (in_array($file->getClientOriginalExtension(), ['jpeg', 'png', 'jpg', 'gif'])) {
            return UserFile::TYPE_IMAGE;
        } elseif (in_array($file->getClientOriginalExtension(), ['pdf'])) {
            return UserFile::TYPE_FILE_PDF;
        } elseif (in_array($file->getClientOriginalExtension(), ['doc', 'docx'])) {
            return UserFile::TYPE_FILE_DOC;
        }
    }

    public function updateGeneralPreferences(array $data, int $id)
    {
        try {
            DB::beginTransaction();

            $user = $this->model->findOrFail($id);
            $user->profile()->update($data);

            if (!empty($data['phone']) && !empty($data['phone_code'])) {
                $user->phone()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'phone' => $data['phone'],
                        'phone_code' => $data['phone_code'],
                    ]
                );
            }

            DB::commit();
            return $user->refresh();
        } catch (\Throwable $th) {
            DB::rollBack();
            return false;
        }
    }
    public function updateNotificationSettings(array $data, int $id)
    {
        try {
            DB::beginTransaction();

            $user = $this->model->findOrFail($id);
            $user->profile()->update($data);

            DB::commit();
            return $user->refresh();
        } catch (\Throwable $th) {
            DB::rollBack();
            return false;
        }
    }
    public function changePassword(string $newPassword, int $id)
    {
        try {
            DB::beginTransaction();

            $user = $this->model->findOrFail($id);
            $user->password = $newPassword;
            $user->save();
            DB::commit();
            return $user;
        } catch (\Throwable $e) {
            DB::rollBack();
            return false;
        }
    }
    /*****************************************End User Settings **************************************/


    /***************************************** User Ban **************************************/
    public function ban($user, array $data)
    {

        try {
            DB::beginTransaction();

            $banned = $user->ban($data);
            DB::commit();
            return $banned;
        } catch (\Throwable $th) {
            dd($th->getMessage());

            DB::rollBack();
            return false;
        }
    }
    public function unban($user)
    {

        try {
            DB::beginTransaction();
            $user->unban();
            DB::commit();
            return true;
        } catch (\Throwable $th) {
            dd($th->getMessage());
            DB::rollBack();
            return false;
        }
    }
    public function getOnlyBanned()
    {
        return $this->model
            ->onlyBanned()
            ->filter(request()->all())
            ->orderBy('banned_at', 'desc');
    }
    public function getWithoutBans()
    {
        return $this->model
            ->withoutBanned()
            ->filter(request()->all());
    }
    /*****************************************End User Ban **************************************/

    private function makeDefaultSortByColumn($column = 'created_at')
    {
        request()->merge([
            'sortBy' => request()->input('sortBy', $column)
        ]);
    }

}