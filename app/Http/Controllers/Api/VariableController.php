<?php

namespace App\Http\Controllers\Api;

use App\Actions\DeleteVariable;
use App\Actions\SaveVariable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreVariableRequest;
use App\Http\Requests\Api\UpdateVariableRequest;
use App\Http\Resources\VariableResource;
use App\Models\Environment;
use App\Models\EnvironmentVariable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class VariableController extends Controller
{
    public function index(Request $request, Environment $environment): AnonymousResourceCollection
    {
        Gate::authorize('view', $environment);

        $variables = $environment->variables()
            ->search($request->query('search'))
            ->paginate(min((int) $request->query('per_page', 50), 100));

        return VariableResource::collection($variables);
    }

    public function store(StoreVariableRequest $request, Environment $environment, SaveVariable $saveVariable): JsonResponse
    {
        $variable = $saveVariable($environment, $request->validated());

        return (new VariableResource($variable))->response()->setStatusCode(201);
    }

    public function update(UpdateVariableRequest $request, EnvironmentVariable $variable, SaveVariable $saveVariable): VariableResource
    {
        $saveVariable($variable->environment, $request->validated(), $variable);

        return new VariableResource($variable->refresh());
    }

    public function destroy(EnvironmentVariable $variable, DeleteVariable $deleteVariable): JsonResponse
    {
        Gate::authorize('delete', $variable);

        $deleteVariable($variable);

        return response()->json(null, 204);
    }
}
