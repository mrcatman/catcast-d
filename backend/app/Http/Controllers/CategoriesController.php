<?php

namespace App\Http\Controllers;

use App\Helpers\FiltersHelper;
use App\Models\Category;

class CategoriesController extends Controller{

    public function index() {
        $categories = Category::query();
        return FiltersHelper::applyFromRequest($categories, Category::class);
    }

    public function show($id) {
        return Category::findOrFail($id);
    }

}
