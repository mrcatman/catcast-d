<?php

namespace App\Http\Controllers;

class SiteController extends Controller {

    public function getConfig() {  
		return array_merge(config('site'), ['urls' => config('urls')]);
    }

}
