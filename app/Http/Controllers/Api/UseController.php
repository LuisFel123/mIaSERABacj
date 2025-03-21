<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Psy\Readline\Hoa\Console;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class UseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::all();
        return $users;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = new User();
        $user->name = $request->name;
        $user->apellidos = $request->apellidos;
        $user->telefono = $request->telefono;
        $user->genero = $request->genero;
        $user->rol = $request->rol;
        $user->nombreUsuario = $request->nombreUsuario;
        $user->contrasena = $request->contrasena;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);

        $user->save();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::find(id: $id);
        return $user;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail(id: $request->id);
        $user->name = $request->name;
        $user->apellidos = $request->apellidos;
        $user->telefono = $request->telefono;
        $user->genero = $request->genero;
        $user->rol = $request->rol;
        $user->nombreUsuario = $request->nombreUsuario;
        $user->contrasena = $request->contrasena;
        $user->email = $request->email;
        $user->password = $request->password;

        $user->save();
        return $user;
    }

    public function updatePassword(Request $request)
    {
        $idsNoEncontrados = [];

        foreach ($request->all() as $usuario) {
            // Verificar si el ID existe antes de actualizar
            $exists = User::where('id', $usuario['id'])->exists();

            if ($exists) {
                User::where('id', $usuario['id'])
                    ->update(['contrasena' => $usuario['contrasena']]);
            } else {
                $idsNoEncontrados[] = $usuario['id'];
            }
        }

        return response()->json([
            'message' => 'Tu contraseña se ha actualizado',
            'ids_no_encontrados' => $idsNoEncontrados
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::destroy(ids: $id);
        return $user;
    }



    public function login(Request $request)
    {

        // Validar los datos de entrada
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6'
        ]);

        //Intentar autenticar al usuario
        //obteniendo al ususario, verificando en la base de dats
        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return response()->json([
                'message' => 'Credenciales incorrectas'
            ]);
        }

        try {
            // Obtener el usuario autenticado
            $user = Auth::user();

            // Verificar si el usuario existe antes de crear el token
            if (!$user) {
                return response()->json(['message' => 'Usuario no encontrado'], 404);
            }

            // Generar el token con Laravel Sanctum
            if (method_exists($user, 'createToken')) {
                $token = $user->createToken('AuthToken')->plainTextToken;
            } else {
                return response()->json([
                    'message' => 'Error: createToken() no está disponible en el modelo User'
                ], 500);
            }

            // Retornar la respuesta con el token
            return response()->json([
                'message' => 'Login exitoso',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'token' => $token
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al generar el token',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            // Obtener el usuario autenticado
            $user = Auth::user();

            // Verificar si el usuario está autenticado
            if (!$user) {
                return response()->json(['message' => 'No autenticado'], 401);
            }

            // Eliminar el token actual para cerrar sesión
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'message' => 'Sesión cerrada exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al cerrar sesión',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
