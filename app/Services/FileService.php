<?php

namespace App\Services;


use App\Enums\BadgeType;
use App\Enums\IntegrationType;
use App\Models\Chest;
use App\Models\Key;
use App\Models\Trophy;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class FileService
{
    public function setAvatar($uuid, $image)
    {
        $path = "app/public/users/$uuid/avatar";
        try {
            $file = Image::make($image);
            $filename = Str::random(10);

            if (!File::exists(storage_path($path))) {
                File::makeDirectory(storage_path($path), 0777, true, true);
            } else {
                $this->deleteAvatarDirectory($uuid);
            }

            $file->save(storage_path($path . '/' . $filename . config('app.default_avatar_suffix')));

            $file->resize(256, 256, function ($constraint) {
                $constraint->aspectRatio();
            })->save(storage_path($path . '/' . $filename . config('app.medium_avatar_suffix')));

            $file->resize(64, 64, function ($constraint) {
                $constraint->aspectRatio();
            })->save(storage_path($path . '/' . $filename . config('app.small_avatar_suffix')));

        } catch (\Exception $e) {
            Log::error('FileService@setAvatar: ' . $e->getMessage());
            return null;
        }
        return $filename;
    }

    public function setBackground($uuid, $image)
    {
        $path = "app/public/users/$uuid/background";
        try {
            $file = Image::make($image);
            $filename = Str::random(10) . '.jpg';

            if (!File::exists(storage_path($path))) {
                File::makeDirectory(storage_path($path), 0777, true, true);
            } else {
                $this->deleteBackgroundDirectory($uuid);
            }
            $file->save(storage_path($path . '/' . $filename));
        } catch (\Exception $e) {
            Log::error('FileService@setBackground: ' . $e->getMessage());
            return null;
        }
        return $filename;
    }

    public function saveTrophyImage($image)
    {
        try {
            if (!File::exists(storage_path(Trophy::FILE_PATH))) {
                File::makeDirectory(storage_path(Trophy::FILE_PATH), 0777, true, true);
            }
            $file = Image::make($image);
            $filename = Str::random(10) . '.png';

            $file->save(storage_path(Trophy::FILE_PATH . '/' . $filename));
        } catch (\Exception $e) {
            Log::error('FileService@saveTrophyImage: ' . $e->getMessage());
            return null;
        }
        return $filename;
    }

    public function saveChestImage($image)
    {
        try {
            if (!File::exists(storage_path(Chest::FILE_PATH))) {
                File::makeDirectory(storage_path(Chest::FILE_PATH), 0777, true, true);
            }
            $file = Image::make($image);
            $filename = Str::random(10) . '.png';

            $file->save(storage_path(Chest::FILE_PATH . '/' . $filename));
        } catch (\Exception $e) {
            Log::error('FileService@saveTrophyImage: ' . $e->getMessage());
            return null;
        }
        return $filename;
    }

    public function saveKeyImage($image)
    {
        try {
            if (!File::exists(storage_path(Key::FILE_PATH))) {
                File::makeDirectory(storage_path(Key::FILE_PATH), 0777, true, true);
            }
            $file = Image::make($image);
            $filename = Str::random(10) . '.png';

            $file->save(storage_path(Key::FILE_PATH . '/' . $filename));
        } catch (\Exception $e) {
            Log::error('FileService@saveTrophyImage: ' . $e->getMessage());
            return null;
        }
        return $filename;
    }

    public function saveAchievementImage($image){
        $path = 'app/public/achievements';
        try {

            if (!File::exists(storage_path($path))) {
                File::makeDirectory(storage_path($path), 0777, true, true);
            }
            $file = Image::make($image);
            $filename = Str::random(10). '.jpg';

            $file->save(storage_path($path .'/'. $filename));
        }
        catch (\Exception $e){
            Log::error('FileService@saveAchievementImage: ' . $e->getMessage());
            return null;
        }
        return $filename;
    }


    public function saveIntegrationImage($integration, $image, $type)
    {
        $url = config('integrations.image.github') . $image;
        $path = 'app/public/integrations/' . $integration;
        try {
            if (!File::exists(storage_path($path))) {
                File::makeDirectory(storage_path($path), 0777, true, true);
            }
            switch ($integration) {
                case IntegrationType::Steam:
                case IntegrationType::Riot:
                case IntegrationType::Strava:
                    $url = $image;
                    $image = Str::random(10) . '.png';
                    break;
                case IntegrationType::Discord:
                    File::copy(resource_path('assets/images/badges/discord/' . $image),
                        storage_path($path . '/' . $image));
                    $filename = $image;
                default:
                    break;

            }

            if ($integration !== IntegrationType::Discord) {
                $filename = $this->saveFormUrl($url, $path, $image);
            }

        } catch (\Exception $e) {
            Log::error('FileService@saveIntegrationImage: ' . $e->getMessage());
            return null;
        }
        return $filename;
    }

    public function saveDiscordRoleImage($url, $filename)
    {
        $path = 'app/public/integrations/discord';
        try {
            if (!File::exists(storage_path($path))) {
                File::makeDirectory(storage_path($path), 0777, true, true);
            }
            return $this->saveFormUrl($url, $path, $filename . '.webp');
        } catch (\Exception $e) {
            Log::error('FileService@saveDiscordRoleImage: ' . $e->getMessage());
            return null;
        }
    }

    public function saveFormUrl($url, $path, $filename)
    {
        try {
            $imageContents = file_get_contents($url);
            if ($imageContents !== false) {
                $file = Image::make($imageContents);

                $file->save(storage_path($path . '/' . $filename));

                return $filename;
            }
            return null;
        }catch (\Exception $e){
            Log::error('FileService@saveFormUrl: ' . $e->getMessage());
            return null;
        }
    }

    public function getDiscordBotImage($url)
    {
        $path = 'app/public/integrations/discord';
        $filename = $this->getFilenamesFromDiscrodImageLinks($url);
        if ($filename != null){
            return $this->saveFormUrl($url, $path, $filename);
        }
        return $filename;
    }

    public function getFilenamesFromDiscrodImageLinks($link) {
        try {
            $path = parse_url($link, PHP_URL_PATH);
            $path = trim($path, '/');
            $filename = basename($path);
            return strtok($filename, '?');
        }catch (\Exception $e){
            Log::error('FileService@getFilenamesFromDiscrodImageLinks: ' . $e->getMessage());
            return null;
        }

    }

    public function deleteAvatarDirectory($uuid)
    {
        File::deleteDirectory("/public/users/$uuid/avatar", true);

    }

    public function deleteBackgroundDirectory($uuid)
    {
        File::deleteDirectory("/public/users/$uuid/background", true);
    }

    public function deleteDirectory($id)
    {
        File::deleteDirectory("/public/users/" . $id, true);

    }

}
