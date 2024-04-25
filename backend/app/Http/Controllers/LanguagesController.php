<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Helpers\LocalizationHelper;
class LanguagesController extends Controller
{
    protected $languages;
    public function __construct(LocalizationHelper $languages)
    {
        $this->languages = $languages;
    }

    public function getTexts(){
        return file_get_contents(public_path("languages.json"));
        //return $this->languages->getTexts();
    }
}
