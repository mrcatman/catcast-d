<?php

namespace App\Http\Controllers;

class SiteController extends Controller {

    public function getConfig() {  
		// TODO: this response is assembled by hand - give it a resource of its own
		return array_merge(config('site'), ['urls' => config('urls')]);
    }

}
