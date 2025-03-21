<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'precio', 'calidad', 'cantidad', 'ancho', 'grosor',
        'largo', 'piesTabla', 'fechaRegistro', 'user_id', 'identificadorP'
    ];
}
