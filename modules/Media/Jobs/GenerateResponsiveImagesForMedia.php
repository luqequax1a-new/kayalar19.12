<?php

namespace Modules\Media\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Media\Entities\File as MediaFile;
use Modules\Media\Services\ResponsiveImageGenerator;

class GenerateResponsiveImagesForMedia implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $fileId;

    public bool $force;

    public function __construct(int $fileId, bool $force = false)
    {
        $this->fileId = $fileId;
        $this->force = $force;
    }

    public function handle(ResponsiveImageGenerator $generator): void
    {
        $file = MediaFile::find($this->fileId);
        if (!$file) return;
        $generator->generateVariants($file, $this->force);
    }
}

