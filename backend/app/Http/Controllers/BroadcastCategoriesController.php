<?php

namespace App\Http\Controllers;

use App\Helpers\FiltersHelper;
use App\Models\BroadcastCategory;

class BroadcastCategoriesController extends Controller{

    public function index() {
        $categories = BroadcastCategory::query();
        return FiltersHelper::applyFromRequest($categories, BroadcastCategory::class);
    }

    public function show($id) {
        return BroadcastCategory::findOrFail($id);
    }

}
