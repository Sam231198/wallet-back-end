<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "API Carteira Virtual",
    description: "Documentação da API Carteira Virtual"
)]
abstract class Controller extends BaseController
{
    //
}