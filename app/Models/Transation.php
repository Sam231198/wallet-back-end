<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['wallet_id', 'wallet_transfer_id', 'amount', 'type', 'message'])]
class Transation extends Model
{
}