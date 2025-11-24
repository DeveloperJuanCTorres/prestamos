<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $perPage = $request->input('perPage', 10);

        $clients = Client::query()
            ->when($search, function($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('numero_doc', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->paginate($perPage);

        return view('clients.index', compact('clients'));
    }

    public function list(Request $request)
    {
        $search = $request->input('search', '');
        $perPage = $request->input('perPage', 10);

        $clients = Client::query()
            ->when($search, function($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('numero_doc', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->paginate($perPage);

        if ($request->ajax()) {
            return response()->json([
                'table' => view('clients.partials.list', compact('clients'))->render(),
                'pagination' => $clients->links()->render()
            ]);
        }

        return view('clients.index', compact('clients'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'tipo_doc' => 'required|string|max:20',
                'numero_doc' => 'required|string|max:25',
                'name' => 'required|string|max:255',
                'address' => 'nullable|string|max:255',
                'email' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:25',
            ]);

            $contact = Client::create($validated);

            return response()->json([
                'status' => true,
                'msg' => 'Cliente registrado'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'msg' => $th->getMessage()
            ]);
        }
        
    }

    public function edit(Request $request)
    {
        try {
            $contact = Client::findOrFail($request->id);

            if ($contact) {

                return response()->json([
                    'status' => true,
                    'msg' => '  Cliente encontrado.',
                    'contact' => $contact
                ]);
            }
            else {
                return response()->json([
                    'status' => false,
                    'msg' => 'No se encontro al cliente'
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'msg' => $th->getMessage()
            ]);
        }   
    }

    public function update(Request $request)
    {
        try {
            $client = Client::findOrFail($request->id);

            // Validar datos
            $request->validate([
                'tipo_doc' => 'required|string|max:20',
                'numero_doc' => 'required|string|max:25',
                'name' => 'required|string|max:255',
                'address' => 'nullable|string|max:255',
                'email' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:25',
            ]);

            // Actualizar campos
            $client->tipo_doc = $request->tipo_doc;
            $client->numero_doc = $request->numero_doc;
            $client->name = $request->name;
            $client->address = $request->address;
            $client->email = $request->email;
            $client->phone = $request->phone;

            $client->save();

            return response()->json([
                'status' => true,
                'msg' => 'Cliente actualizado.'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'msg' => $th->getMessage()
            ]);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $contact = Client::findOrFail($request->id);
            $contact->delete();

            return response()->json([
                'status' => true,
                'msg' => 'Cliente eliminado.'
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'msg' => $th->getMessage()
            ]);
        }
    }
}
