<?php

namespace UtkarshGayguwal\LogManagement\Http\Controllers;

use UtkarshGayguwal\LogManagement\Models\Log;
use UtkarshGayguwal\LogManagement\Filters\LogFilter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class LogController extends Controller
{
    protected $filter;

    public function __construct(LogFilter $filter)
    {
        $this->filter = $filter;
    }

    /**
     * Display a listing of logs.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $orderBy = $request->order_by ?? 'id';
            $sortBy = $request->sort_by ?? 'desc';
            $perPage = $request->per_page ?? 20;

            $query = Log::select(
                'id', 'module_id', 'client_id', 'action', 'loggable_id', 'loggable_type', 
                'redirect_path', 'is_redirect_enabled', 'log_type', 'title', 'description', 
                'ip_address', 'created_by', 'created_at', 'data', 'program_id', 'asset_id'
            )->filter($this->filter)
                ->with(['user', 'loggable']);

            $logs = $query->orderBy($orderBy, $sortBy)->paginate($perPage);

            if ($logs->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No Records Found',
                    'data' => []
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Logs retrieved successfully',
                'data' => $logs->items(),
                'pagination' => [
                    'current_page' => $logs->currentPage(),
                    'last_page' => $logs->lastPage(),
                    'per_page' => $logs->perPage(),
                    'total' => $logs->total(),
                    'from' => $logs->firstItem(),
                    'to' => $logs->lastItem()
                ]
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display a specific log.
     */
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $log = Log::with(['user', 'loggable'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Log retrieved successfully',
                'data' => $log
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Log not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }
}