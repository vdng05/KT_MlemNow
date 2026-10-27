<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class AccountProfileAvatarTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_upload_an_avatar_to_local_public_storage(): void
    {
        $existingAvatars = glob(public_path('images/avatars/*')) ?: [];
        $user = User::factory()->create([
            'name' => 'Nguyen Van An',
            'email' => 'avatar@example.com',
            'phone' => '0912345678',
        ]);

        try {
            $response = $this->actingAs($user)->put(route('account.profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'avatar' => UploadedFile::fake()->image('avatar.png'),
            ]);

            $response->assertRedirect();
            $user->refresh();
            $this->assertStringStartsWith('images/avatars/', $user->avatar_path);
            $this->assertFileExists(public_path($user->avatar_path));
        } finally {
            $this->removeNewAvatars($existingAvatars);
        }
    }

    public function test_user_can_remove_an_avatar_and_return_to_the_default(): void
    {
        File::ensureDirectoryExists(public_path('images/avatars'));
        $filename = Str::uuid().'.png';
        $path = 'images/avatars/'.$filename;
        UploadedFile::fake()->image('avatar.png')->move(public_path('images/avatars'), $filename);
        $user = User::factory()->create([
            'name' => 'Nguyen Van An',
            'email' => 'remove-avatar@example.com',
            'phone' => '0912345678',
            'avatar_path' => $path,
        ]);

        $response = $this->actingAs($user)->put(route('account.profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'remove_avatar' => '1',
        ]);

        $response->assertRedirect();
        $this->assertNull($user->fresh()->avatar_path);
        $this->assertFileDoesNotExist(public_path($path));
    }

    private function removeNewAvatars(array $existingAvatars): void
    {
        $currentAvatars = glob(public_path('images/avatars/*')) ?: [];
        foreach (array_diff($currentAvatars, $existingAvatars) as $avatar) {
            File::delete($avatar);
        }
    }
}