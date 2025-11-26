<?php

namespace App\Http\Controllers;

use App\Models\Type;
use Illuminate\Http\Request;

class TypeController extends Controller
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

        $types = Type::query()
            ->when($search, function($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->paginate($perPage);

        return view('types.index', compact('types'));
    }

    public function list(Request $request)
    {
        $search = $request->input('search', '');
        $perPage = $request->input('perPage', 10);

        $types = Type::query()
            ->when($search, function($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->paginate($perPage);

        if ($request->ajax()) {
            return response()->json([
                'table' => view('types.partials.list_table', compact('types'))->render(),
                'cards' => view('types.partials.list_cards', compact('types'))->render(),
                'pagination' => $types->links()->render()
            ]);
        }

        return view('types.index', compact('types'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'minimo' => 'required',
                'maximo' => 'required',
                'periodicity_days' => 'required',
                'num_payments' => 'required'
            ]);

            $type = Type::create($validated);

            return response()->json([
                'status' => true,
                'msg' => 'Tipo registrado'
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
            $type = Type::findOrFail($request->id);

            if ($type) {

                return response()->json([
                    'status' => true,
                    'msg' => '  Tipo encontrado.',
                    'type' => $type
                ]);
            }
            else {
                return response()->json([
                    'status' => false,
                    'msg' => 'No se encontro el tipo'
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
            $type = Type::findOrFail($request->id);

            // Validar datos
            $request->validate([
                'name' => 'required|string|max:20',
                'minimo' => 'required',
                'maximo' => 'required',
                'periodo' => 'required',
                'num_payments' => 'required'
            ]);

            // Actualizar campos
            $type->name = $request->name;
            $type->minimo = $request->minimo;
            $type->maximo = $request->maximo;
            $type->periodicity_days = $request->periodo;
            $type->num_payments = $request->num_payments;

            $type->save();

            return response()->json([
                'status' => true,
                'msg' => 'Tipo actualizado.'
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
            $type = Type::findOrFail($request->id);
            $type->delete();

            return response()->json([
                'status' => true,
                'msg' => 'Tipo eliminado.'
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'msg' => $th->getMessage()
            ]);
        }
    }
}
