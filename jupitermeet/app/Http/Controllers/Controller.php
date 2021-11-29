<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

require_once(base_path() . "/system/apl_core_configuration.php");
require_once(base_path() . "/system/apl_core_functions.php");
require_once(base_path() . "/system/aus_core_configuration.php");
require_once(base_path() . "/system/aus_core_functions.php");

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
