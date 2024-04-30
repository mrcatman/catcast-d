<?php
namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Models\MediaUploadKey;

class TusController extends Controller {

    public function handleWebhooks() {
        file_put_contents(storage_path('log.txt'), json_encode(request()->all()));
        $upload_key = MediaUploadKey::where(['key' => request()->input('Upload.MetaData.upload_key'), 'media_id' => request()->input('Upload.MetaData.id')])->firstOrFail();
        return '';
    }

}
