<?php

namespace App\Models\Concerns;

use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

/**
 * Gives a model a single Cloudinary image stored in `image_public_id`.
 *
 * Adds: ->image_url (auto-included in Inertia/JSON), ->syncImage($request),
 * ->replaceImage($file), ->removeImage(), and cleans up Cloudinary when the
 * model is deleted.
 */
trait HasCloudinaryImage
{
    public function initializeHasCloudinaryImage(): void
    {
        $this->mergeFillable(['image_public_id']);
        $this->append('image_url');
    }

    public static function bootHasCloudinaryImage(): void
    {
        static::deleted(function ($model) {
            // Soft-deleting models keep their image so a restore is lossless.
            if (method_exists($model, 'isForceDeleting') && ! $model->isForceDeleting()) {
                return;
            }

            $model->destroyImageFile($model->image_public_id);
        });
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_public_id
            ? app(CloudinaryService::class)->url($this->image_public_id)
            : null;
    }

    /**
     * One call for controllers: upload a new image if one was sent,
     * or remove the current one if `remove_image` was ticked.
     * Returns false if the Cloudinary upload failed.
     */
    public function syncImage(Request $request): bool
    {
        try {
            if ($request->hasFile('image')) {
                $this->replaceImage($request->file('image'));
            } elseif ($request->boolean('remove_image')) {
                $this->removeImage();
            }
        } catch (\Throwable $e) {
            report($e);

            return false;
        }

        return true;
    }

    public function replaceImage(UploadedFile $file): void
    {
        $old = $this->image_public_id;

        // Upload first: if it fails, the old image is untouched.
        $newId = app(CloudinaryService::class)->upload($file->getRealPath());

        $this->update(['image_public_id' => $newId]);

        $this->destroyImageFile($old);
    }

    public function removeImage(): void
    {
        $old = $this->image_public_id;

        if ($old) {
            $this->update(['image_public_id' => null]);
            $this->destroyImageFile($old);
        }
    }

    protected function destroyImageFile(?string $publicId): void
    {
        if (! $publicId) {
            return;
        }

        try {
            // Adjust this one line if your CloudinaryService names it differently.
            app(CloudinaryService::class)->destroy($publicId);
        } catch (\Throwable $e) {
            report($e); // never block the user because Cloudinary cleanup failed
        }
    }
}
