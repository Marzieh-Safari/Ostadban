<?php

namespace App\Http\Controllers;

use App\Models\Professor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class ProfessorController extends Controller
{
    const CACHE_EXPIRE = 60; // 1 hour in minutes
    const SINGLE_PREFIX = 'professor.';
    const LIST_PREFIX = 'professor.all';
    const SEARCH_PREFIX = 'professor.search.';
    const TOP_PREFIX = 'professor.top';

    // نمایش لیست اساتید با قابلیت جستجو و مرتب‌سازی
    public function index(Request $request)
    {
        $cacheKey = $this->buildListCacheKey($request);
        
        $professors = Cache::remember($cacheKey, self::CACHE_EXPIRE, function () use ($request) {
            return $this->buildProfessorQuery($request)->get();
        });

        return response()->json([
            'data' => $professors,
            'meta' => ['cached' => true, 'expires_in' => self::CACHE_EXPIRE . ' minutes']
        ]);
    }

    // ذخیره استاد جدید
    public function store(Request $request)
    {
        $validated = $this->validateProfessorRequest($request);
        $professor = Professor::create($validated);
        
        $this->clearAllListCaches();
        
        return response()->json([
            'data' => $professor,
            'message' => 'Professor created successfully'
        ], 201);
    }

    // نمایش جزئیات استاد با کش پیشرفته
    public function show($id)
    {
        $cacheKey = self::SINGLE_PREFIX . $id;
        
        $professor = Cache::remember($cacheKey, self::CACHE_EXPIRE, function () use ($id) {
            return Professor::with(['courses', 'reviews', 'department'])
                ->withCount('courses')
                ->withAvg('reviews', 'rating')
                ->findOrFail($id);
        });

        return response()->json([
            'data' => $professor,
            'meta' => ['cached' => true]
        ]);
    }

    // به‌روزرسانی اطلاعات استاد
    public function update(Request $request, $id)
    {
        $professor = Professor::findOrFail($id);
        $validated = $this->validateProfessorRequest($request, $id);
        
        $professor->update($validated);
        $this->clearSingleProfessorCache($id);
        $this->clearAllListCaches();

        return response()->json([
            'data' => $professor,
            'message' => 'Professor updated successfully'
        ]);
    }

    // حذف استاد
    public function destroy($id)
    {
        $professor = Professor::findOrFail($id);
        $professor->delete();
        
        $this->clearSingleProfessorCache($id);
        $this->clearAllListCaches();

        return response()->json(null, 204);
    }

    // جستجوی اساتید
    public function search(Request $request)
    {
        $query = $request->query('query');
        $cacheKey = self::SEARCH_PREFIX . md5($query);
        
        $results = Cache::remember($cacheKey, self::CACHE_EXPIRE, function () use ($query) {
            return Professor::where('name', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%")
                ->limit(10)
                ->get();
        });

        return response()->json([
            'data' => $results,
            'meta' => ['cached' => true]
        ]);
    }

    // نمایش اساتید برتر
    public function topProfessors()
    {
        $professors = Cache::remember(self::TOP_PREFIX, self::CACHE_EXPIRE, function () {
            return Professor::withAvg('reviews', 'rating')
                ->orderBy('reviews_avg_rating', 'desc')
                ->limit(5)
                ->get();
        });

        return response()->json([
            'data' => $professors,
            'meta' => ['cached' => true]
        ]);
    }

    // ==================== روش‌های کمکی ====================

    protected function validateProfessorRequest(Request $request, $id = null)
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:professors,email,' . $id,
            'department_id' => 'required|exists:departments,id',
            'faculty_number' => 'required|unique:professors,faculty_number,' . $id
        ]);
    }

    protected function buildProfessorQuery(Request $request)
    {
        $query = Professor::query();

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->has('sort')) {
            $direction = $request->get('direction', 'asc');
            $query->orderBy($request->sort, $direction);
        }

        if ($request->has('department')) {
            $query->where('department_id', $request->department);
        }

        return $query;
    }

    protected function buildListCacheKey(Request $request)
    {
        $key = self::LIST_PREFIX;
        
        if ($request->hasAny(['search', 'sort', 'direction', 'department'])) {
            $key .= '.' . md5(serialize($request->all()));
        }

        return $key;
    }

    protected function clearSingleProfessorCache($id)
    {
        Cache::forget(self::SINGLE_PREFIX . $id);
        Cache::forget(self::SEARCH_PREFIX . $id);
    }

    protected function clearAllListCaches()
    {
        Cache::forget(self::LIST_PREFIX);
        Cache::forget(self::TOP_PREFIX);
        $this->clearFilteredCaches(self::LIST_PREFIX);
        $this->clearFilteredCaches(self::SEARCH_PREFIX);
    }

    protected function clearFilteredCaches($prefix)
    {
        if (config('cache.default') === 'redis') {
            $redis = Redis::connection(config('cache.stores.redis.connection'));
            $keys = $redis->command('keys', ["*{$prefix}*"]);

            foreach ($keys as $key) {
                $cleanKey = str_replace(config('database.redis.options.prefix'), '', $key);
                Cache::forget($cleanKey);
            }
        }
    }
}